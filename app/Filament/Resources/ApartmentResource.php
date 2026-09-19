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

                        Forms\Components\Select::make('status')
                            ->label('Estado')
                            ->options([
                                'available' => 'Disponible',
                                'occupied' => 'Ocupado',
                                'maintenance' => 'Mantenimiento',
                            ])
                            ->default('available')
                            ->required(),
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
            //
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
