<?php

namespace App\Notifications;

use App\Models\Reservation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ReservationConfirmedNotification extends Notification implements ShouldQueue
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
            ->subject('Confirmación de reserva')
            ->greeting('Hola ' . $notifiable->name)
            ->line('Su reserva de área común ha sido confirmada.')
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
            ->line('Gracias por utilizar el sistema de reservas.');
    }
}