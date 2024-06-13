<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Equipo;

class SolicitudRechazada extends Notification
{
    use Queueable;

    private $user;
    private $equipo_id;

    /**
     * Create a new notification instance.
     */
    public function __construct($user, $equipo_id)
    {
        $this->user = $user;
        $this->equipo_id = $equipo_id;
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
        $equipo = Equipo::find($this->equipo_id);
        return (new MailMessage)
                    ->line('Su solicitud de unión ha sido rechazada en el equipo ' . $equipo->nombre)
                    ->action('Lista de equipos', url('equipos'))
                    ->line('¡Gracias por usar nuestra aplicación!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
