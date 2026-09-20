<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CommonAreaResource\Pages;
use App\Filament\Resources\CommonAreaResource\RelationManagers;
use App\Models\CommonArea;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CommonAreaResource extends Resource
{
    protected static ?string $model = CommonArea::class;
    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';
    protected static ?string $navigationLabel = 'Áreas comunes';
    protected static ?string $modelLabel = 'Área común';

    protected static ?string $pluralModelLabel = 'Áreas comunes';

    protected static ?string $navigationGroup = 'Operaciones';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información del área')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nombre')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\Textarea::make('description')
                            ->label('Descripción')
                            ->rows(3)
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('capacity')
                            ->label('Capacidad máxima')
                            ->numeric()
                            ->integer()
                            ->minValue(1)
                            ->suffix('personas')
                            ->helperText('Cantidad máxima de personas permitidas en el área.'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Disponibilidad')
                    ->schema([
                        Forms\Components\CheckboxList::make('available_days')
                            ->label('Días disponibles')
                            ->options([
                                1 => 'Lunes',
                                2 => 'Martes',
                                3 => 'Miércoles',
                                4 => 'Jueves',
                                5 => 'Viernes',
                                6 => 'Sábado',
                                0 => 'Domingo',
                            ])
                            ->columns(4)
                            ->required()
                            ->columnSpanFull(),
                        Forms\Components\TimePicker::make('opening_time')
                            ->label('Hora de apertura')
                            ->seconds(false)
                            ->required(),

                        Forms\Components\TimePicker::make('closing_time')
                            ->label('Hora de cierre')
                            ->seconds(false)
                            ->required()
                            ->after('opening_time'),

                        Forms\Components\TextInput::make('reservation_duration')
                            ->label('Duración de reserva')
                            ->numeric()
                            ->required()
                            ->integer()
                            ->minValue(15)
                            ->step(15)
                            ->default(60)
                            ->suffix('minutos')
                            ->helperText('Duración de cada bloque de reserva.'),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Área activa')
                            ->default(true),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Área')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('capacity')
                    ->label('Capacidad')
                    ->suffix(' personas'),

                Tables\Columns\TextColumn::make('opening_time')
                    ->label('Apertura'),

                Tables\Columns\TextColumn::make('closing_time')
                    ->label('Cierre'),

                Tables\Columns\TextColumn::make('reservation_duration')
                    ->label('Duración')
                    ->suffix(' min'),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Activa')
                    ->boolean(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Estado')
                    ->trueLabel('Activas')
                    ->falseLabel('Inactivas')
                    ->placeholder('Todas'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([

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
            'index' => Pages\ListCommonAreas::route('/'),
            'create' => Pages\CreateCommonArea::route('/create'),
            'edit' => Pages\EditCommonArea::route('/{record}/edit'),
        ];
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->can('areas.ver') ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->can('areas.crear') ?? false;
    }

    public static function canEdit($record): bool
    {
        return auth()->user()?->can('areas.editar') ?? false;
    }

    public static function canDelete($record): bool
    {
        $user = auth()->user();

        if (!$user?->can('areas.eliminar')) {
            return false;
        }

        return !$record->reservations()->exists();
    }

    public static function canDeleteAny(): bool
    {
        return auth()->user()?->can('areas.eliminar') ?? false;
    }
}
