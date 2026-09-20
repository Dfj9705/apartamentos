<?php

namespace App\Filament\Widgets;

use App\Models\Apartment;
use App\Models\MaintenanceFee;
use App\Models\Payment;
use App\Models\Resident;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ApartmentStatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;
    protected function getStats(): array
    {
        $apartments = Apartment::count();

        $activeResidents = Resident::query()
            ->where('is_active', true)
            ->count();

        $pendingFees = MaintenanceFee::query()
            ->where('status', 'pending')
            ->whereDate('due_date', '>=', today())
            ->count();

        $overdueFees = MaintenanceFee::query()
            ->where('status', '!=', 'paid')
            ->whereDate('due_date', '<', today())
            ->count();

        $monthlyIncome = Payment::query()
            ->whereMonth('paid_at', now()->month)
            ->whereYear('paid_at', now()->year)
            ->sum('amount');

        return [
            Stat::make(
                'Apartamentos registrados',
                $apartments
            )
                ->description('Total de apartamentos')
                ->icon('heroicon-o-building-office-2'),

            Stat::make(
                'Residentes activos',
                $activeResidents
            )
                ->description('Residentes actualmente activos')
                ->icon('heroicon-o-users'),

            Stat::make(
                'Cuotas pendientes',
                $pendingFees
            )
                ->description('Pendientes dentro del plazo')
                ->icon('heroicon-o-clock'),

            Stat::make(
                'Cuotas vencidas',
                $overdueFees
            )
                ->description('Requieren atención')
                ->icon('heroicon-o-exclamation-triangle'),

            Stat::make(
                'Ingresos del mes',
                'Q ' . number_format($monthlyIncome, 2)
            )
                ->description(
                    ucfirst(now()->translatedFormat('F Y'))
                )
                ->icon('heroicon-o-banknotes'),
        ];
    }
}