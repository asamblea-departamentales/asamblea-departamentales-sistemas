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
        // Ejecuta cada minuto el comando de recordatorios de actividades
        // Asegúrate de haber creado el comando con:
        // php artisan make:command SendActividadReminders
        $schedule->command('actividades:reminders')->everyMinute();
        //Ejecutar el comando cada 28 de cada mes a las 8:00 AM
        $schedule->command('departamentales:check')->monthlyOn(28, '08:00');
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
