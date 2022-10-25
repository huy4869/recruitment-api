<?php

namespace App\Console\Commands;

use App\Console\Kernel;
use App\Models\Notification;
use App\Models\User;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class NotifyUserInterview extends Command
{
    const QUANTITY_CHUNK = 1000;
    const DAY_AFTER_TOMORROW = 2;

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
        $users = User::query()->roleUser()->with(['applications'])->get();

        if (!$users) {
            $this->info('No user exists.');
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

            $userNotification = [];
            $now = now();
            $dayAfterTomorrow = now()->addDays(self::DAY_AFTER_TOMORROW);

            foreach ($users as $user) {
                foreach ($user->applications as $application) {
                    $interviewData = date('Y-m-d', strtotime($application->date));

                    if ($now->format('Y-m-d') < $interviewData && $interviewData < $dayAfterTomorrow->format('Y-m-d')) {
                        $userNotification[] = [
                            'user_id' => $user->id,
                            'notice_type_id' => Notification::TYPE_INTERVIEW_COMING,
                            'noti_object_ids' => json_encode([
                                'store_id' => $application->store_id,
                                'application_id' => $application->id,
                            ]),
                            'title' => trans('notification.interview.title'),
                            'content' => trans('notification.interview.content'),
                            'created_at' => $now->toDateTimeString(),
                        ];
                    }
                }
            }

            if (!count($userNotification)) {
                $this->info('No user interviews yet.');
                $this->line('_________END__________');

                return;
            }

            collect($userNotification)->chunk(self::QUANTITY_CHUNK)->each(function ($data) {
                Notification::insert($data->toArray());
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
