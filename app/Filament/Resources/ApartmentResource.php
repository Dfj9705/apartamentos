<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ApartmentResource\Pages;
use App\Filament\Resources\ApartmentResource\RelationManagers;
use App\Models\Apartment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ApartmentResource extends Resource
{
    protected static ?string $model = Apartment::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';

    protected static ?string $navigationLabel = 'Apartamentos';

    protected static ?string $modelLabel = 'Apartamento';

    protected static ?string $pluralModelLabel = 'Apartamentos';

    protected static ?string $navigationGroup = 'Administración';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información del apartamento')
                    ->schema([
                        Forms\Components\TextInput::make('number')
                            ->label('Número')
                            ->required()
                            ->maxLength(50),

                        Forms\Components\TextInput::make('tower')
                            ->label('Torre / Edificio')
                            ->maxLength(100),

                        Forms\Components\TextInput::make('floor')
                            ->label('Nivel')
                            ->numeric()
                            ->minValue(0),

                        Forms\Components\Placeholder::make('status_display')
                            ->label('Estado')
                            ->content(fn(?Apartment $record): string => match ($record?->status) {
                                'available' => 'Disponible',
                                'occupied' => 'Ocupado',
                                'maintenance' => 'Mantenimiento',
                                default => 'Disponible',
                            }),
                    ])
                    ->columns(2),
            ]);
    }
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('number')
                    ->label('Apartamento')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('tower')
                    ->label('Torre')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('floor')
                    ->label('Nivel')
                    ->sortable(),

                Tables\Columns\TextColumn::make('residents_count')
                    ->label('Residentes')
                    ->counts('residents')
                    ->badge()
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'available' => 'Disponible',
                        'occupied' => 'Ocupado',
                        'maintenance' => 'Mantenimiento',
                        default => $state,
                    })
                    ->color(fn(string $state): string => match ($state) {
                        'available' => 'success',
                        'occupied' => 'warning',
                        'maintenance' => 'danger',
                        default => 'gray',
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Estado')
                    ->options([
                        'available' => 'Disponible',
                        'occupied' => 'Ocupado',
                        'maintenance' => 'Mantenimiento',
                    ]),

                Tables\Filters\SelectFilter::make('tower')
                    ->label('Torre')
                    ->options(
                        fn() => Apartment::query()
                            ->whereNotNull('tower')
                            ->distinct()
                            ->orderBy('tower')
                            ->pluck('tower', 'tower')
                            ->toArray()
                    ),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('maintenance')
                    ->label('Mantenimiento')
                    ->icon('heroicon-o-wrench-screwdriver')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->visible(
                        fn(Apartment $record): bool =>
                            $record->status !== 'maintenance'
                    )
                    ->action(function (Apartment $record) {
                        $record->update([
                            'status' => 'maintenance',
                        ]);
                    }),

                Tables\Actions\Action::make('finishMaintenance')
                    ->label('Finalizar mantenimiento')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(
                        fn(Apartment $record): bool =>
                            $record->status === 'maintenance'
                    )
                    ->action(function (Apartment $record) {
                        $record->update([
                            'status' => $record->residents()
                                ->where('is_active', true)
                                ->exists()
                                ? 'occupied'
                                : 'available',
                        ]);
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\ResidentsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListApartments::route('/'),
            'create' => Pages\CreateApartment::route('/create'),
            'edit' => Pages\EditApartment::route('/{record}/edit'),
        ];
    }
}
