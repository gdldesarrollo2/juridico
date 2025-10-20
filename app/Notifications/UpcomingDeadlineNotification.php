<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class UpcomingDeadlineNotification extends Notification
{
    use Queueable;

    public function __construct(
        public string $titulo,          // Ej: "Juicio #123 - Presentación de demanda"
        public string $cuerpo,          // Texto descriptivo
        public string $ctaUrl,          // URL a ver/editar
        public ?int $diasRestantes = null // 0 = hoy vence
    ) {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject('⚠️ Vencimiento próximo')
            ->greeting('Hola,')
            ->line($this->cuerpo);

        if (!is_null($this->diasRestantes)) {
            $mail->line("Días hábiles restantes: {$this->diasRestantes}");
        }

        return $mail->action('Ver detalle', $this->ctaUrl)
            ->line('Este es un recordatorio automático.');
    }
}
