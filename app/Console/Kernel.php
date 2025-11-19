<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('inspire')->hourly();
          $schedule->command('alertas:generar-stock')
            ->hourly()
            ->withoutOverlapping();

        // Limpiar alertas antiguas cada día a las 2 AM
        $schedule->call(function () {
            \App\Models\AlertaStock::where('leida', true)
                ->where('fecha_lectura', '<', now()->subDays(30))
                ->delete();
        })->dailyAt('02:00');
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
