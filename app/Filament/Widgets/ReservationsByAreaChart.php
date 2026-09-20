<?php

namespace App\Filament\Widgets;

use App\Models\CommonArea;
use Filament\Widgets\ChartWidget;

class ReservationsByAreaChart extends ChartWidget
{
    protected static ?string $heading = 'Reservas por área común';

    protected static ?int $sort = 3;
    protected int|string|array $columnSpan = [
        'md' => 1,
        'xl' => 1,
    ];

    protected function getData(): array
    {
        $areas = CommonArea::query()
            ->withCount([
                'reservations' => fn($query) => $query
                    ->whereIn('status', ['confirmed', 'completed']),
            ])
            ->orderBy('name')
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'Reservas',
                    'data' => $areas
                        ->pluck('reservations_count')
                        ->toArray(),
                ],
            ],
            'labels' => $areas
                ->pluck('name')
                ->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    public static function canView(): bool
    {
        return auth()->user()?->hasAnyRole([
            'Administrador',
            'Administración',
        ]) ?? false;
    }
}