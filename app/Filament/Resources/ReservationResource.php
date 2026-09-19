<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ReservationResource\Pages;
use App\Filament\Resources\ReservationResource\RelationManagers;
use App\Models\Reservation;
use App\Models\User;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Models\CommonArea;
use Filament\Forms\Get;
use Filament\Forms\Set;
class ReservationResource extends Resource
{
    protected static ?string $model = Reservation::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Datos de la reserva')
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->label('Usuario')
                            ->relationship(
                                name: 'user',
                                titleAttribute: 'name',
                                modifyQueryUsing: fn($query) =>
                                    $query->whereHas('resident', function ($query) {
                                        $query
                                            ->where('is_active', true)
                                            ->whereNotNull('apartment_id');
                                    })
                            )
                            ->searchable()
                            ->preload()
                            ->live()
                            ->default(fn() => auth()->id())
                            ->disabled(fn() => !auth()->user()->hasRole('Administrador'))
                            ->dehydrated()
                            ->afterStateUpdated(function ($state, Set $set) {
                                if (!$state) {
                                    $set('apartment_id', null);
                                    return;
                                }

                                $user = User::with('resident.apartment')->find($state);

                                $set(
                                    'apartment_id',
                                    $user?->resident?->apartment_id
                                );
                            })
                            ->required(),

                        Forms\Components\Select::make('apartment_id')
                            ->label('Apartamento')
                            ->options(
                                fn() => \App\Models\Apartment::query()
                                    ->orderBy('tower')
                                    ->orderBy('number')
                                    ->get()
                                    ->pluck('display_name', 'id')
                                    ->toArray()
                            )
                            ->default(fn() => auth()->user()->apartment()?->id)
                            ->disabled()
                            ->dehydrated()
                            ->required(),

                        Forms\Components\Select::make('common_area_id')
                            ->label('Área común')
                            ->relationship(
                                name: 'commonArea',
                                titleAttribute: 'name',
                                modifyQueryUsing: fn($query) => $query->where('is_active', true),
                            )
                            ->searchable()
                            ->preload()
                            ->live()
                            ->afterStateUpdated(function (Set $set) {
                                $set('start_time', null);
                                $set('end_time', null);
                            })
                            ->required(),

                        Forms\Components\DatePicker::make('reservation_date')
                            ->label('Fecha')
                            ->required()
                            ->native(false)
                            ->minDate(today())
                            ->live()
                            ->afterStateUpdated(function (Set $set) {
                                $set('start_time', null);
                                $set('end_time', null);
                            }),

                        Forms\Components\Select::make('start_time')
                            ->label('Horario')
                            ->options(function (Get $get): array {
                                $commonAreaId = $get('common_area_id');
                                $date = $get('reservation_date');

                                if (!$commonAreaId || !$date) {
                                    return [];
                                }

                                $commonArea = CommonArea::find($commonAreaId);

                                if (!$commonArea) {
                                    return [];
                                }

                                return collect(
                                    $commonArea->getAvailableTimeSlots(
                                        Carbon::parse($date)
                                    )
                                )
                                    ->reject(function (array $slot) use ($commonArea, $date) {
                                        return Reservation::hasOverlap(
                                            commonAreaId: $commonArea->id,
                                            date: $date,
                                            startTime: $slot['start'],
                                            endTime: $slot['end'],
                                        );
                                    })
                                    ->mapWithKeys(function (array $slot) {
                                        return [
                                            $slot['start'] => "{$slot['start']} - {$slot['end']}",
                                        ];
                                    })
                                    ->toArray();
                            })
                            ->live()
                            ->afterStateUpdated(function ($state, Get $get, Set $set) {
                                if (!$state) {
                                    $set('end_time', null);
                                    return;
                                }

                                $commonArea = CommonArea::find(
                                    $get('common_area_id')
                                );

                                if (!$commonArea) {
                                    return;
                                }

                                $endTime = Carbon::parse($state)
                                    ->addMinutes($commonArea->reservation_duration)
                                    ->format('H:i');

                                $set('end_time', $endTime);
                            })
                            ->required()
                            ->disabled(
                                fn(Get $get) =>
                                    !$get('common_area_id') ||
                                    !$get('reservation_date')
                            ),

                        Forms\Components\TimePicker::make('end_time')
                            ->label('Hora de finalización')
                            ->seconds(false)
                            ->disabled()
                            ->dehydrated()
                            ->required(),

                        Forms\Components\Select::make('status')
                            ->label('Estado')
                            ->options([
                                'confirmed' => 'Confirmada',
                                'cancelled' => 'Cancelada',
                                'completed' => 'Finalizada',
                            ])
                            ->default('confirmed')
                            ->disabled(fn() => !auth()->user()->hasRole('Administrador'))
                            ->dehydrated()
                            ->required(),

                        Forms\Components\Textarea::make('notes')
                            ->label('Observaciones')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('reservation_date')
                    ->label('Fecha')
                    ->date('d/m/Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('commonArea.name')
                    ->label('Área común')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('apartment.display_name')
                    ->label('Apartamento'),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Usuario')
                    ->searchable(),

                Tables\Columns\TextColumn::make('schedule')
                    ->label('Horario')
                    ->state(
                        fn(Reservation $record): string =>
                            Carbon::parse($record->start_time)->format('H:i')
                            . ' - ' .
                            Carbon::parse($record->end_time)->format('H:i')
                    ),

                Tables\Columns\TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'confirmed' => 'Confirmada',
                        'cancelled' => 'Cancelada',
                        'completed' => 'Finalizada',
                        default => $state,
                    })
                    ->color(fn(string $state): string => match ($state) {
                        'confirmed' => 'success',
                        'cancelled' => 'danger',
                        'completed' => 'gray',
                        default => 'gray',
                    }),
            ])
            ->defaultSort('reservation_date', 'asc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Estado')
                    ->options([
                        'confirmed' => 'Confirmada',
                        'cancelled' => 'Cancelada',
                        'completed' => 'Finalizada',
                    ]),

                Tables\Filters\SelectFilter::make('common_area_id')
                    ->label('Área común')
                    ->relationship('commonArea', 'name'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->visible(
                        fn(Reservation $record): bool =>
                            static::canEdit($record)
                    ),

                Tables\Actions\Action::make('cancel')
                    ->label('Cancelar')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Cancelar reserva')
                    ->modalDescription(
                        '¿Está seguro de que desea cancelar esta reserva?'
                    )
                    ->visible(function (Reservation $record): bool {
                        $user = auth()->user();

                        if ($record->status !== 'confirmed') {
                            return false;
                        }

                        return $user->hasRole('Administrador')
                            || $record->user_id === $user->id;
                    })
                    ->action(function (Reservation $record): void {
                        $record->update([
                            'status' => 'cancelled',
                        ]);
                    }),
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
            'index' => Pages\ListReservations::route('/'),
            'create' => Pages\CreateReservation::route('/create'),
            'edit' => Pages\EditReservation::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        $user = auth()->user();

        if (!$user->hasRole('Administrador')) {
            $query->where('user_id', $user->id);
        }

        return $query;
    }

    public static function canEdit($record): bool
    {
        $user = auth()->user();

        if ($user->hasRole('Administrador')) {
            return true;
        }

        return $record->user_id === $user->id
            && $record->status === 'confirmed';
    }

    public static function canDelete($record): bool
    {
        return false;
    }

    public static function canDeleteAny(): bool
    {
        return false;
    }
}
