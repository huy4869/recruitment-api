<?php

namespace App\Console\Commands;

use App\Console\Kernel;
use App\Models\Application;
use App\Models\MInterviewStatus;
use App\Models\Notification;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class NotifyUserInterview extends Command
{
    const QUANTITY_CHUNK = 1000;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = Kernel::NOTIFY_USER_INTERVIEW;

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'notify user for interview (run this cmd at the end of the day)';

    /**
     * Execute the console command.
     *
     * @return void
     * @throws Exception
     */
    public function handle()
    {
        $this->line('_________START__________');
        $dayAfterTomorrow = now()->addDay();
        $applications = Application::query()->whereHas('jobPosting')
            ->with(['store','store.owner', 'user'])
            ->whereDate('date', $dayAfterTomorrow)
            ->where('interview_status_id', '=', MInterviewStatus::STATUS_WAITING_INTERVIEW)
            ->get();

        if (!$applications) {
            $this->info('No application exists.');
            $this->line('_________END__________');

            return;
        }

        $dayNotifyUserInterviewDelay = now()->subDays(config('validate.notify_user_interview_delay'))
            ->format('Y-m-d');
        $this->info('Refreshing data...');
        Notification::query()->where('notice_type_id', Notification::TYPE_INTERVIEW_COMING)
            ->whereDate('created_at', '>', $dayNotifyUserInterviewDelay)
            ->delete();

        $this->info('Data is refreshed.');
        $this->info('Looking for user interviews tomorrow...');

        try {
            DB::beginTransaction();

            $notifications = [];
            $now = now()->toDateTimeString();

            foreach ($applications as $application) {
                $notifications[] = [
                    'user_id' => $application->user_id,
                    'notice_type_id' => Notification::TYPE_INTERVIEW_COMING,
                    'noti_object_ids' => json_encode([
                        'store_id' => $application->store_id,
                        'job_id' => $application->job_posting_id,
                        'application_id' => $application->id,
                    ]),
                    'title' => trans('notification.interview.title'),
                    'content' => trans('notification.interview.content', ['store_name' => $application->store->name]),
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
                $notifications[] = [
                    'user_id' => $application->store->owner->id,
                    'notice_type_id' => Notification::TYPE_INTERVIEW_COMING,
                    'noti_object_ids' => json_encode([
                        'store_id' => $application->store_id,
                        'job_id' => $application->job_posting_id,
                        'application_id' => $application->id,
                    ]),
                    'title' => __('notification.NOO5.title'),
                    'content' => __('notification.NOO5.content', ['user_name' => $application->user->first_name . $application->user->last_name]),
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }//end foreach

            if (!count($notifications)) {
                $this->info('No user interviews yet.');
                $this->line('_________END__________');

                return;
            }

            collect($notifications)->chunk(self::QUANTITY_CHUNK)->each(function ($data) {
                Notification::query()->insert($data->toArray());
                $this->info(sprintf('Inserted %s record !', count($data->toArray())));
            });

            DB::commit();
            $this->info('The command was successful!');
            $this->line('_________END__________');
            return;
        } catch (Exception $exception) {
            DB::rollBack();
            Log::error($exception->getMessage(), [$exception]);
            $this->error('Something went wrong!');
            throw new Exception($exception->getMessage());
        }//end try
    }
}
