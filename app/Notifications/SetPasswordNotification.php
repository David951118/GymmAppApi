<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Lang;

class SetPasswordNotification extends Notification
{
    use Queueable;

    public $token;

    /**
     * Create a new notification instance.
     */
    public function __construct($token)
    {
        $this->token = $token;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        // En una app real, esto apuntaría a tu URL de frontend, ej: https://tu-app.com/activate?token=...
        // Para la API, simularemos la URL o apuntaremos a una ruta de frontend inexistente
        $url = url('/activate-account?token=' . $this->token . '&email=' . $notifiable->getEmailForPasswordReset());

        return (new MailMessage)
            ->subject(Lang::get('Bienvenido a GymApp - Activa tu cuenta'))
            ->greeting('¡Hola ' . $notifiable->nombre . '!')
            ->line('Se ha creado una cuenta para ti en GymApp.')
            ->line('Para comenzar, por favor haz clic en el botón de abajo para establecer tu contraseña y activar tu cuenta.')
            ->action('Establecer Contraseña', $url)
            ->line('Este enlace de activación expirará en 60 minutos.')
            ->line('Si no esperabas esta invitación, puedes ignorar este correo.');
    }
}
