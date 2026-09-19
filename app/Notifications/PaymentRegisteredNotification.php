<?php

namespace App\Notifications;

use App\Models\MaintenanceFee;
use App\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PaymentRegisteredNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public MaintenanceFee $fee,
        public Payment $payment
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
            ->subject('Confirmación de pago de mantenimiento')
            ->greeting('Hola ' . $notifiable->name)
            ->line('Se ha registrado correctamente su pago de mantenimiento.')
            ->line('Apartamento: ' . $apartment->display_name)
            ->line(
                'Período: ' .
                str_pad($this->fee->month, 2, '0', STR_PAD_LEFT) .
                '/' .
                $this->fee->year
            )
            ->line(
                'Monto: Q ' .
                number_format((float) $this->payment->amount, 2)
            )
            ->line(
                'Fecha de pago: ' .
                $this->payment->paid_at->format('d/m/Y')
            )
            ->when(
                $this->payment->reference,
                fn(MailMessage $mail) =>
                    $mail->line(
                        'Referencia: ' . $this->payment->reference
                    )
            )
            ->line('Gracias por mantener sus cuotas al día.');
    }
}