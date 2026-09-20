<?php

namespace App\Filament\Widgets;

use App\Models\Reservation;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class UpcomingReservations extends BaseWidget
{
    protected static ?string $heading = 'Próximas reservas';

    protected static ?int $sort = 4;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Reservation::query()
                    ->with([
                        'commonArea',
                        'apartment',
                        'user',
                    ])
                    ->when(
                        auth()->user()?->hasRole('Residente'),
                        fn($query) => $query->where('user_id', auth()->id())
                    )
                    ->where('status', 'confirmed')
                    ->whereDate('reservation_date', '>=', today())
                    ->orderBy('reservation_date')
                    ->orderBy('start_time')
            )
            ->columns([
                Tables\Columns\TextColumn::make('reservation_date')
                    ->label('Fecha')
                    ->date('d/m/Y'),

                Tables\Columns\TextColumn::make('start_time')
                    ->label('Inicio')
                    ->time('H:i'),

                Tables\Columns\TextColumn::make('end_time')
                    ->label('Fin')
                    ->time('H:i'),

                Tables\Columns\TextColumn::make('commonArea.name')
                    ->label('Área común'),

                Tables\Columns\TextColumn::make('apartment.display_name')
                    ->label('Apartamento'),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Usuario')
                    ->visible(fn(): bool => !auth()->user()?->hasRole('Residente')),
            ])
            ->paginated([5, 10, 25])
            ->defaultPaginationPageOption(5);
    }
}