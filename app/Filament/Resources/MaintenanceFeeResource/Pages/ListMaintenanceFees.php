<?php

namespace App\Filament\Resources\MaintenanceFeeResource\Pages;

use App\Filament\Resources\MaintenanceFeeResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use App\Models\Apartment;
use App\Models\MaintenanceFee;
use Filament\Forms;
use Filament\Notifications\Notification;


class ListMaintenanceFees extends ListRecords
{
    protected static string $resource = MaintenanceFeeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('generateFees')
                ->label('Generar cuotas')
                ->icon('heroicon-o-banknotes')
                ->form([
                    Forms\Components\Select::make('month')
                        ->label('Mes')
                        ->options([
                            1 => 'Enero',
                            2 => 'Febrero',
                            3 => 'Marzo',
                            4 => 'Abril',
                            5 => 'Mayo',
                            6 => 'Junio',
                            7 => 'Julio',
                            8 => 'Agosto',
                            9 => 'Septiembre',
                            10 => 'Octubre',
                            11 => 'Noviembre',
                            12 => 'Diciembre',
                        ])
                        ->default(now()->month)
                        ->required(),

                    Forms\Components\TextInput::make('year')
                        ->label('Año')
                        ->numeric()
                        ->default(now()->year)
                        ->minValue(2020)
                        ->maxValue(2100)
                        ->required(),

                    Forms\Components\TextInput::make('amount')
                        ->label('Monto')
                        ->numeric()
                        ->prefix('Q')
                        ->minValue(0.01)
                        ->required(),

                    Forms\Components\DatePicker::make('due_date')
                        ->label('Fecha de vencimiento')
                        ->required(),
                ])
                ->action(function (array $data): void {
                    $created = 0;
                    $existing = 0;

                    Apartment::query()
                        ->orderBy('id')
                        ->chunkById(100, function ($apartments) use ($data, &$created, &$existing) {
                            foreach ($apartments as $apartment) {
                                $fee = MaintenanceFee::firstOrCreate(
                                    [
                                        'apartment_id' => $apartment->id,
                                        'month' => $data['month'],
                                        'year' => $data['year'],
                                    ],
                                    [
                                        'amount' => $data['amount'],
                                        'due_date' => $data['due_date'],
                                        'status' => 'pending',
                                    ]
                                );

                                $fee->wasRecentlyCreated
                                    ? $created++
                                    : $existing++;
                            }
                        });

                    Notification::make()
                        ->title('Cuotas generadas')
                        ->body(
                            "{$created} cuotas creadas. {$existing} ya existían."
                        )
                        ->success()
                        ->send();
                }),

            Actions\CreateAction::make(),
        ];
    }
}
