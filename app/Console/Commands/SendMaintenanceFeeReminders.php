<?php

namespace App\Console\Commands;

use App\Models\MaintenanceFee;
use App\Notifications\MaintenanceFeeDueReminderNotification;
use Illuminate\Console\Command;
use App\Notifications\MaintenanceFeeOverdueNotification;

class SendMaintenanceFeeReminders extends Command
{
    protected $signature = 'maintenance:send-reminders';

    protected $description = 'Envía recordatorios de cuotas de mantenimiento próximas a vencer';

    public function handle(): int
    {
        $reminderDate = today()->addDays(3);

        $fees = MaintenanceFee::query()
            ->with([
                'apartment.residents.user',
            ])
            ->where('status', 'pending')
            ->whereDate('due_date', $reminderDate)
            ->get();

        $notificationsSent = 0;

        foreach ($fees as $fee) {
            $residents = $fee->apartment
                ->residents
                ->where('is_active', true);

            foreach ($residents as $resident) {
                if (!$resident->user) {
                    continue;
                }

                $resident->user->notify(
                    new MaintenanceFeeDueReminderNotification($fee)
                );

                $notificationsSent++;
            }
        }

        $overdueDate = today()->subDay();

        $overdueFees = MaintenanceFee::query()
            ->with([
                'apartment.residents.user',
            ])
            ->where('status', 'pending')
            ->whereDate('due_date', $overdueDate)
            ->get();

        $overdueNotificationsSent = 0;

        foreach ($overdueFees as $fee) {
            $residents = $fee->apartment
                ->residents
                ->where('is_active', true);

            foreach ($residents as $resident) {
                if (!$resident->user) {
                    continue;
                }

                $resident->user->notify(
                    new MaintenanceFeeOverdueNotification($fee)
                );

                $overdueNotificationsSent++;
            }
        }

        $this->info(
            "Recordatorios enviados: {$notificationsSent}"
        );

        $this->info(
            "Avisos de cuotas vencidas enviados: {$overdueNotificationsSent}"
        );

        return self::SUCCESS;
    }
}