<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Console\Commands\NotificarVencimientos;

class Kernel extends ConsoleKernel
{
    /**
     * Registrar los comandos de Artisan.
     */
    protected $commands = [
        NotificarVencimientos::class, // 👈 aquí registras tu comando
    ];

    /**
     * Definir la programación de comandos.
     */
    protected function schedule(Schedule $schedule)
    {
        // Ejecutar todos los días a las 8am
       // $schedule->command('notificar:vencimientos')->dailyAt('08:00');
           $schedule->command('notificar:vencimientos')->everyMinute();
    }

    /**
     * Registrar los comandos para la aplicación.
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
