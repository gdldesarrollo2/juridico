<?php

namespace App\Notifications;

use App\Models\Etapa;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class VencimientoProximo extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Etapa $etapa)
    {
        // Si serializas en cola y no quieres relaciones:
        // $this->etapa = $etapa->withoutRelations();
    }

    public function via($notifiable): array
    {
        return ['mail']; // y/o 'database'
    }

    public function toMail($notifiable): MailMessage
    {
        $juicio = $this->etapa->juicio; // relación Etapa->juicio
        $fecha  = optional($this->etapa->fecha_vencimiento)->format('d/m/Y');

        return (new MailMessage)
            ->subject('Vencimiento próximo de etapa')
            ->greeting('Hola '.$notifiable->name)
            ->line('Tienes un vencimiento próximo en tu juicio.')
            ->line('Etapa: '.$this->etapa->etapa)
            ->line('Fecha: '.$fecha)
            ->action('Ver Juicio', route('juicios.show', $juicio->id))
            ->line('Por favor revisa este vencimiento para tomar las acciones necesarias.');
    }
}
