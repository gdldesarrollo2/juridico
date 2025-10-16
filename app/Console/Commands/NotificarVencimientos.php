<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Etapa;
use Carbon\Carbon;
use App\Notifications\VencimientoProximo;

class NotificarVencimientos extends Command
{
    /**
     * The name and signature of the console command.
     *
     * Lo vas a ejecutar con: php artisan notificar:vencimientos
     */
    protected $signature = 'notificar:vencimientos';

    /**
     * The console command description.
     */
    protected $description = 'Notifica a los abogados sobre vencimientos próximos (solo días hábiles, excluyendo festivos).';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $hoy = Carbon::today();

        // Trae etapas con juicios y abogado->usuario asignado
        $etapas = Etapa::with(['juicio.abogado.usuario'])
            ->whereBetween('fecha_vencimiento', [$hoy, $hoy->copy()->addDays(15)])
            ->get();

        $total = 0;

        foreach ($etapas as $etapa) {
            // Validar si tiene abogado y usuario asignado
            if ($etapa->juicio && $etapa->juicio->abogado && $etapa->juicio->abogado->usuario) {

                // 🔹 Calcula días hábiles entre hoy y vencimiento
                $diasHabiles = $this->diasHabiles($hoy, Carbon::parse($etapa->fecha_vencimiento));

                if ($diasHabiles <= 15) {
                    $etapa->juicio->abogado->usuario->notify(new VencimientoProximo($etapa));
                    $total++;
                }
            }
        }

        $this->info("✅ Se enviaron {$total} notificaciones de vencimientos próximos.");
    }

    /**
     * Calcula los días hábiles entre dos fechas (excluye sábados, domingos y festivos).
     */
    private function diasHabiles(Carbon $desde, Carbon $hasta): int
    {
        $festivos = \DB::table('dias_festivos')->pluck('fecha')->toArray();

        $dias = 0;
        $fecha = $desde->copy();

        while ($fecha->lte($hasta)) {
            if (!$fecha->isWeekend() && !in_array($fecha->toDateString(), $festivos)) {
                $dias++;
            }
            $fecha->addDay();
        }

        return $dias;
    }
}
