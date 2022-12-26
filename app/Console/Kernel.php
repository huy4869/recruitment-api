<?php

namespace App\Console;

use App\Console\Commands\MakeUserJobDesiredMatch;
use App\Console\Commands\NotifyRecInterview;
use App\Console\Commands\NotifyUserInterview;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    public const NOTIFY_USER_INTERVIEW = 'notify_user:interview';
    public const MAKE_USER_JOB_DESIRED_MATCH = 'command:make_user_job_desired_match';
    public const NOTIFY_WAIT_INTERVIEW_LIMIT_DATE = 'command:wait_interview_limit_date';

    protected $commands = [
        NotifyUserInterview::class,
        NotifyRecInterview::class,
        MakeUserJobDesiredMatch::class,
    ];
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
         $schedule->command(self::NOTIFY_USER_INTERVIEW)
             ->dailyAt(config('schedule.notify_user_interview'))
             ->runInBackground()
             ->withoutOverlapping();
         $schedule->command(self::NOTIFY_WAIT_INTERVIEW_LIMIT_DATE)
             ->dailyAt(config('schedule.notify_rec_wait_interview_limit_date'))
             ->runInBackground()
             ->withoutOverlapping();

         $schedule->command(self::MAKE_USER_JOB_DESIRED_MATCH)
             ->everyFifteenMinutes()
             ->runInBackground()
             ->withoutOverlapping();
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
