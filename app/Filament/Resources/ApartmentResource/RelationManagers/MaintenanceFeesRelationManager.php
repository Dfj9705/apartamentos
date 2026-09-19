<?php

namespace App\Filament\Resources\ApartmentResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class MaintenanceFeesRelationManager extends RelationManager
{
    protected static string $relationship = 'maintenanceFees';

    protected static ?string $title = 'Historial de cuotas';

    public function form(Form $form): Form
    {
        return $form
            ->schema([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('month')
            ->columns([
                Tables\Columns\TextColumn::make('month')
                    ->label('Mes')
                    ->formatStateUsing(fn($state): string => match ((int) $state) {
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
                    }),

                Tables\Columns\TextColumn::make('year')
                    ->label('Año')
                    ->sortable(),

                Tables\Columns\TextColumn::make('amount')
                    ->label('Cuota')
                    ->money('GTQ'),

                Tables\Columns\TextColumn::make('due_date')
                    ->label('Vencimiento')
                    ->date('d/m/Y'),

                Tables\Columns\TextColumn::make('current_status')
                    ->label('Estado')
                    ->badge()
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'pending' => 'Pendiente',
                        'paid' => 'Pagada',
                        'overdue' => 'Vencida',
                        default => $state,
                    })
                    ->color(fn(string $state): string => match ($state) {
                        'pending' => 'warning',
                        'paid' => 'success',
                        'overdue' => 'danger',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('payment.paid_at')
                    ->label('Fecha de pago')
                    ->date('d/m/Y')
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('payment.amount')
                    ->label('Pagado')
                    ->money('GTQ')
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('payment.reference')
                    ->label('Referencia')
                    ->placeholder('—'),
            ])
            ->defaultSort('due_date', 'desc')
            ->headerActions([])
            ->actions([])
            ->bulkActions([]);
    }
}