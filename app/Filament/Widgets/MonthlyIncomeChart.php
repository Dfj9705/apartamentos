<?php

namespace App\Filament\Widgets;

use App\Models\Payment;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;

class MonthlyIncomeChart extends ChartWidget
{
    protected static ?string $heading = 'Ingresos de los últimos 6 meses';
    protected static ?int $sort = 2;
    protected int|string|array $columnSpan = [
        'md' => 1,
        'xl' => 1,
    ];

    protected function getData(): array
    {
        $labels = [];
        $data = [];

        for ($i = 5; $i >= 0; $i--) {
            $date = now()->copy()->subMonths($i);

            $labels[] = ucfirst(
                Carbon::create($date->year, $date->month)
                    ->translatedFormat('M Y')
            );

            $data[] = (float) Payment::query()
                ->whereYear('paid_at', $date->year)
                ->whereMonth('paid_at', $date->month)
                ->sum('amount');
        }

        return [
            'datasets' => [
                [
                    'label' => 'Ingresos (Q)',
                    'data' => $data,
                    'fill' => true,
                    'tension' => 0.3,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}