<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Console\Command;

class TaskScheduler
{
    /**
     * Run the task scheduler.
     *
     * @param \Illuminate\Console\Scheduling\Schedule $schedule
     * @return void
     */
    public function schedule(Schedule $schedule)
    {
        $schedule->command('fetch:articles')->daily(); // Schedule the custom command to run daily
    }
}
