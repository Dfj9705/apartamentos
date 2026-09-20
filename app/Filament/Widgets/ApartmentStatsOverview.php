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
        $user = auth()->user();

        if ($user?->hasRole('Residente')) {
            return $this->getResidentStats();
        }

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

    protected function getResidentStats(): array
    {
        $user = auth()->user();
        $apartment = $user->apartment();

        if (!$apartment) {
            return [
                Stat::make('Apartamento', 'Sin apartamento')
                    ->description('No tienes un apartamento asociado')
                    ->icon('heroicon-o-home'),
            ];
        }

        $pendingFees = MaintenanceFee::query()
            ->where('apartment_id', $apartment->id)
            ->where('status', '!=', 'paid')
            ->whereDate('due_date', '>=', today())
            ->count();

        $overdueFees = MaintenanceFee::query()
            ->where('apartment_id', $apartment->id)
            ->where('status', '!=', 'paid')
            ->whereDate('due_date', '<', today())
            ->count();

        $pendingBalance = MaintenanceFee::query()
            ->where('apartment_id', $apartment->id)
            ->where('status', '!=', 'paid')
            ->sum('amount');

        return [
            Stat::make(
                'Mi apartamento',
                $apartment->display_name
            )
                ->description('Apartamento asociado')
                ->icon('heroicon-o-home'),

            Stat::make(
                'Cuotas pendientes',
                $pendingFees
            )
                ->description('Dentro del plazo')
                ->icon('heroicon-o-clock'),

            Stat::make(
                'Cuotas vencidas',
                $overdueFees
            )
                ->description('Fuera del plazo de pago')
                ->icon('heroicon-o-exclamation-triangle')
                ->color($overdueFees > 0 ? 'danger' : 'success'),

            Stat::make(
                'Saldo pendiente',
                'Q ' . number_format($pendingBalance, 2)
            )
                ->description('Total pendiente de pago')
                ->icon('heroicon-o-banknotes')
                ->color($pendingBalance > 0 ? 'warning' : 'success'),
        ];
    }
}