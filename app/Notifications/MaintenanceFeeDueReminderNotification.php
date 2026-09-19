<?php

namespace App\Notifications;

use App\Models\MaintenanceFee;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;

class MaintenanceFeeDueReminderNotification extends Notification implements ShouldQueue
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
            ->subject('Recordatorio de cuota de mantenimiento')
            ->greeting('Hola ' . $notifiable->name)
            ->line('Le recordamos que tiene una cuota de mantenimiento próxima a vencer.')
            ->line('Apartamento: ' . $apartment->display_name)
            ->line(
                'Período: ' .
                str_pad($this->fee->month, 2, '0', STR_PAD_LEFT) .
                '/' .
                $this->fee->year
            )
            ->line(
                'Monto: Q ' .
                number_format((float) $this->fee->amount, 2)
            )
            ->line(
                'Fecha de vencimiento: ' .
                $this->fee->due_date->format('d/m/Y')
            )
            ->line('Por favor, realice su pago antes de la fecha de vencimiento.')
            ->line('Si ya realizó el pago, puede ignorar este mensaje.');
    }
}