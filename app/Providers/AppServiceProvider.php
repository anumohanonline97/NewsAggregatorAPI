<?php

namespace App\Providers;

use App\Console\TaskScheduler;
use Illuminate\Support\ServiceProvider;


class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $taskScheduler = new TaskScheduler();
        $taskScheduler->schedule(app('Illuminate\Console\Scheduling\Schedule'));
    }
}
