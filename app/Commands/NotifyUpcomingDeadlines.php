<?php

namespace App\Console\Commands;

use App\Models\Etapa;                // ajuste: tu modelo de etapas de Juicio
use App\Models\RevisionEtapa;        // si manejas etapas de Revisión
use App\Support\BusinessDays;
use App\Notifications\UpcomingDeadlineNotification;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;

class NotifyUpcomingDeadlines extends Command
{
    protected $signature = 'deadlines:notify {--window=15}';
    protected $description = 'Envía emails por vencimientos próximos (días hábiles, excluyendo festivos por autoridad).';

    public function handle(): int
    {
        $window = (int) $this->option('window');
        $today  = Carbon::today();

        $this->info("Buscando vencimientos a {$window} días hábiles...");

        // === JUICIOS ===
        $etapas = Etapa::query()
            ->with([
                'juicio.autoridad:id,nombre',
                'juicio.abogado.usuario',        // preferible email desde usuario
                'juicio.abogado',                // o email directo desde abogado si lo tienes
                'etiqueta:id,nombre'
            ])
            ->whereNotNull('fecha_vencimiento')
            ->whereIn('estatus', ['en_tramite','en_proceso','pendiente','juicio']) // ajusta a tus estados "no cerrados"
            ->get();

        $count = 0;

        foreach ($etapas as $e) {
            if (!$e->juicio) continue;

            $autoridadId = (int) ($e->juicio->autoridad_id ?? 0);
            $holidays = BusinessDays::holidaysForAuthority($autoridadId, $e->fecha_vencimiento->year)
                ->merge(BusinessDays::holidaysForAuthority($autoridadId, $today->year))
                ->unique();

            $dias = BusinessDays::diffInBusinessDays($today, Carbon::parse($e->fecha_vencimiento), $holidays);
            if ($dias <= $window) {
                // Determinamos email destino del abogado
                $email = $e->juicio->abogado?->usuario?->email
                       ?? $e->juicio->abogado?->email
                       ?? null;

                if (!$email) continue;

                $titulo = sprintf('Juicio #%s - %s', $e->juicio->numero_juicio ?? $e->juicio->id, $e->etapa ?? 'Etapa');
                $cuerpo = sprintf(
                    'La etapa "%s" del juicio "%s" vence el %s (Autoridad: %s).',
                    $e->etapa ?? 'Etapa',
                    $e->juicio->nombre ?? ('Juicio #' . $e->juicio->id),
                    Carbon::parse($e->fecha_vencimiento)->translatedFormat('d/M/Y'),
                    $e->juicio->autoridad->nombre ?? '—'
                );

                $url = route('juicios.show', $e->juicio->id); // crea esta ruta (ver más abajo)

                Notification::route('mail', $email)->notify(
                    new UpcomingDeadlineNotification($titulo, $cuerpo, $url, $dias)
                );

                $count++;
            }
        }

        // === REVISIONES (opcional) ===
        if (class_exists(RevisionEtapa::class)) {
            $revs = RevisionEtapa::query()
                ->with([
                    'revision.autoridad:id,nombre',
                    'revision.abogado.usuario',
                    'revision.abogado',
                ])
                ->whereNotNull('fecha_vencimiento')
                ->whereIn('estatus', ['en_tramite','en_proceso','pendiente']) // ajusta
                ->get();

            foreach ($revs as $r) {
                if (!$r->revision) continue;

                $autoridadId = (int) ($r->revision->autoridad_id ?? 0);
                $holidays = BusinessDays::holidaysForAuthority($autoridadId, $r->fecha_vencimiento->year)
                    ->merge(BusinessDays::holidaysForAuthority($autoridadId, $today->year))
                    ->unique();

                $dias = BusinessDays::diffInBusinessDays($today, Carbon::parse($r->fecha_vencimiento), $holidays);
                if ($dias <= $window) {
                    $email = $r->revision->abogado?->usuario?->email
                           ?? $r->revision->abogado?->email
                           ?? null;
                    if (!$email) continue;

                    $titulo = sprintf('Revisión #%s - %s', $r->revision->id, $r->etapa ?? 'Etapa');
                    $cuerpo = sprintf(
                        'La etapa "%s" de la revisión del cliente "%s" vence el %s (Autoridad: %s).',
                        $r->etapa ?? 'Etapa',
                        $r->revision->cliente->nombre ?? '—',
                        Carbon::parse($r->fecha_vencimiento)->translatedFormat('d/M/Y'),
                        $r->revision->autoridad->nombre ?? '—'
                    );

                    $url = route('revisiones.show', $r->revision->id); // crea si no existe

                    Notification::route('mail', $email)->notify(
                        new UpcomingDeadlineNotification($titulo, $cuerpo, $url, $dias)
                    );
                    $count++;
                }
            }
        }

        $this->info("Notificaciones enviadas: {$count}");
        return self::SUCCESS;
    }
}
