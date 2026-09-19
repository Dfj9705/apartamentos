<?php

namespace App\Notifications;

use App\Models\Reservation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ReservationCancelledNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Reservation $reservation
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $reservation = $this->reservation;

        return (new MailMessage)
            ->subject('Cancelación de reserva')
            ->greeting('Hola ' . $notifiable->name)
            ->line('Su reserva de área común ha sido cancelada.')
            ->line('Área común: ' . $reservation->commonArea->name)
            ->line('Apartamento: ' . $reservation->apartment->display_name)
            ->line(
                'Fecha: ' .
                $reservation->reservation_date->format('d/m/Y')
            )
            ->line(
                'Horario: ' .
                substr($reservation->start_time, 0, 5) .
                ' - ' .
                substr($reservation->end_time, 0, 5)
            )
            ->line('El horario ha quedado nuevamente disponible para otras reservas.');
    }
}