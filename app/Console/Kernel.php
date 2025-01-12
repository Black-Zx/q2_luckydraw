<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        // 
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        /*
        $schedule->command('match:create')
            ->dailyAt('07:00');
        $schedule->command('match:live')
            ->everyFiveMinutes();
        $schedule->command('match:score')
            ->everyFiveMinutes();
        $schedule->command('total_score:update')
            ->dailyAt('23:50');
        $schedule->command('leaderboard:update')
            ->dailyAt('00:00');
        */
        $schedule->command('reallocate:spin')
            ->dailyAt('00:00');

        // email notification
        $schedule->command('notify:email')
            ->everyMinute();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}