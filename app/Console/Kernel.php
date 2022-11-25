<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    public const NOTIFY_USER_INTERVIEW = 'notify_user:interview';
    public const MAKE_USER_JOB_DESIRED_MATCH = 'command:make_user_job_desired_match';
    public const NOTIFY_WAIT_INTERVIEW_LIMIT_DATE = 'command:wait_interview_limit_date';

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
         $schedule->command(self::NOTIFY_USER_INTERVIEW)
             ->dailyAt(config('schedule.notify_user_interview'));
         $schedule->command(self::NOTIFY_WAIT_INTERVIEW_LIMIT_DATE)
             ->dailyAt(config('schedule.notify_rec_wait_interview_limit_date'));

         $schedule->command(self::MAKE_USER_JOB_DESIRED_MATCH)->everyFifteenMinutes();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
