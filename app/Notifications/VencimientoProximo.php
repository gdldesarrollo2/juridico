<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class VencimientoProximo extends Notification implements ShouldQueue
{
    use Queueable;

    public $etapa;

    public function __construct($etapa)
    {
        $this->etapa = $etapa;
    }

    /**
     * Indica los canales de entrega (correo en este caso).
     */
    public function via($notifiable)
    {
        return ['mail'];   // 👈 Aquí definimos que se envíe por correo
    }

    /**
     * Contenido del correo.
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('📌 Vencimiento próximo')
            ->greeting('Hola ' . $notifiable->name)
            ->line('Tienes un vencimiento próximo en tu juicio.')
            ->line('Etapa: ' . $this->etapa->nombre)
            ->line('Fecha: ' . $this->etapa->fecha_vencimiento)
            ->action('Ver Juicio', url('/juicios/' . $this->etapa->juicio_id))
            ->line('Por favor revisa este vencimiento.');
    }
}
