<?php

namespace App\Console\Commands;

use App\Console\Kernel;
use App\Models\Application;
use App\Models\MInterviewStatus;
use App\Models\Notification;
use Carbon\Carbon;
use Exception;
use Illuminate\Console\Command;

class NotifyRecInterview extends Command
{
    const QUANTITY_CHUNK = 1000;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = Kernel::NOTIFY_WAIT_INTERVIEW_LIMIT_DATE;

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Notify when "waiting for results" status lasts 1 month(run this cmd at the beginning of the day)';

    /**
     * Execute the console command.
     *
     * @return void
     * @throws Exception
     */
    public function handle()
    {
        $this->line('_________START__________');
        $applications = Application::query()
            ->with(['user', 'store.owner'])
            ->where('interview_status_id', '=', MInterviewStatus::STATUS_WAITING_RESULT)
            ->get();

        if (!$applications) {
            $this->info('No application exists.');
            $this->line('_________END__________');

            return;
        }

        $this->info('Refreshing data...');
        $this->info('Looking for interviews pending results today...');


        $recNotifications = [];
        $now = now()->format('Y-m-d');

        foreach ($applications as $application) {
            $time = Carbon::parse($application->update_times)->addDays(config('date.max_day'))->format('Y-m-d');

            if ($now == $time) {
                $recNotifications[] = [
                    'user_id' => $application->store->owner->id,
                    'notice_type_id' => Notification::TYPE_WAIT_INTERVIEW_LIMIT_DATE,
                    'noti_object_ids' => json_encode([
                        'store_id' => $application->store_id,
                        'user_id' => $application->user_id,
                        'application_id' => $application->id,
                    ]),
                    'title' => trans('notification.N003.title', ['user_name' => $application->user->first_name.$application->user->last_name]),
                    'content' => trans('notification.N003.content', ['user_name' => $application->user->first_name.$application->user->last_name]),
                    'created_at' => now()->toDateTimeString(),
                ];
            }
        }

        if (!count($recNotifications)) {
            $this->info('No Notify when "waiting for results" status lasts 1 month.');
            $this->line('_________END__________');

            return;
        }

        collect($recNotifications)->chunk(self::QUANTITY_CHUNK)->each(function ($data) {
            Notification::query()->insert($data->toArray());
            $this->info(sprintf('Inserted %s record !', count($data->toArray())));
        });

        $this->info('The command was successful!');
        $this->line('_________END__________');
    }
}
