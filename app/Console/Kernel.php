<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        //van con una hora de retraso mirar el comando php artisan schedule:list
        if (env('APP_ENV') === 'local') {
            $schedule->command('recordatorios:enviar')->dailyAt('11:20');
        } else { 
            $schedule->command('recordatorios:enviar')->everyMinute();
        }// Se ejecutará todos los días a las 8:00 AM
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
