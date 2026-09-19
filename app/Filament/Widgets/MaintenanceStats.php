<?php

namespace App\Filament\Widgets;

use App\Models\MaintenanceFee;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class MaintenanceStats extends BaseWidget
{
    protected static ?int $sort = 1;
    protected int|string|array $columnSpan = 'full';
    protected function getStats(): array
    {
        $total = MaintenanceFee::sum('amount');

        $collected = MaintenanceFee::query()
            ->where('status', 'paid')
            ->sum('amount');

        $pending = MaintenanceFee::query()
            ->where('status', '!=', 'paid')
            ->whereDate('due_date', '>=', today())
            ->sum('amount');

        $overdue = MaintenanceFee::query()
            ->where('status', '!=', 'paid')
            ->whereDate('due_date', '<', today())
            ->sum('amount');

        return [
            Stat::make(
                'Total de cuotas',
                'Q ' . number_format($total, 2)
            )
                ->description('Monto total generado')
                ->icon('heroicon-o-document-currency-dollar'),

            Stat::make(
                'Cobrado',
                'Q ' . number_format($collected, 2)
            )
                ->description('Cuotas pagadas')
                ->icon('heroicon-o-check-circle')
                ->color('success'),

            Stat::make(
                'Pendiente',
                'Q ' . number_format($pending, 2)
            )
                ->description('Pendiente de cobro')
                ->icon('heroicon-o-clock')
                ->color('warning'),

            Stat::make(
                'Vencido',
                'Q ' . number_format($overdue, 2)
            )
                ->description('Cuotas fuera de plazo')
                ->icon('heroicon-o-exclamation-triangle')
                ->color('danger'),
        ];
    }
}