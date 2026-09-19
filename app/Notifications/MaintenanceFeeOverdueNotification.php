<?php

namespace App\Notifications;

use App\Models\MaintenanceFee;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MaintenanceFeeOverdueNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public MaintenanceFee $fee
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $apartment = $this->fee->apartment;

        return (new MailMessage)
            ->subject('Cuota de mantenimiento vencida')
            ->greeting('Hola ' . $notifiable->name)
            ->line('Le informamos que tiene una cuota de mantenimiento vencida.')
            ->line('Apartamento: ' . $apartment->display_name)
            ->line(
                'Período: ' .
                str_pad($this->fee->month, 2, '0', STR_PAD_LEFT) .
                '/' .
                $this->fee->year
            )
            ->line(
                'Monto pendiente: Q ' .
                number_format((float) $this->fee->amount, 2)
            )
            ->line(
                'Fecha de vencimiento: ' .
                $this->fee->due_date->format('d/m/Y')
            )
            ->line('Por favor, realice el pago de la cuota pendiente.')
            ->line('Si ya realizó el pago recientemente, puede ignorar este mensaje.');
    }
}