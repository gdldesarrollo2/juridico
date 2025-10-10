<?php
// app/Support/JuicioAbogadoService.php
namespace App\Support;

use App\Models\Juicio;
use App\Models\JuicioAbogadoHistorial;
use Illuminate\Support\Facades\DB;

class JuicioAbogadoService
{
    /**
     * Cambia el abogado del juicio, cierra historial vigente y abre uno nuevo.
     */
    public static function setAbogado(Juicio $juicio, ?int $nuevoAbogadoId, ?int $userId = null, ?string $motivo = null): void
    {
        DB::transaction(function () use ($juicio, $nuevoAbogadoId, $userId, $motivo) {
            // Cierra historial vigente (si hay)
            JuicioAbogadoHistorial::where('juicio_id', $juicio->id)
                ->whereNull('asignado_hasta')
                ->latest('asignado_desde')
                ->limit(1)
                ->update(['asignado_hasta' => now(), 'changed_by' => $userId]);

            // Actualiza campo en juicios
            $juicio->update(['abogado_id' => $nuevoAbogadoId]);

            // Abre registro nuevo si hay nuevo abogado
            if ($nuevoAbogadoId) {
                JuicioAbogadoHistorial::create([
                    'juicio_id'       => $juicio->id,
                    'abogado_id'      => $nuevoAbogadoId,
                    'asignado_desde'  => now(),
                    'changed_by'      => $userId,
                    'motivo'          => $motivo,
                ]);
            }
        });
    }
}
