<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MaintenanceFeeResource\Pages;
use App\Filament\Resources\MaintenanceFeeResource\RelationManagers;
use App\Models\Apartment;
use App\Models\MaintenanceFee;
use App\Notifications\PaymentRegisteredNotification;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class MaintenanceFeeResource extends Resource
{
    protected static ?string $model = MaintenanceFee::class;

    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';

    protected static ?string $navigationLabel = 'Cuotas de mantenimiento';

    protected static ?string $modelLabel = 'Cuota de mantenimiento';

    protected static ?string $pluralModelLabel = 'Cuotas de mantenimiento';

    protected static ?string $navigationGroup = 'Finanzas';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('apartment_id')
                    ->label('Apartamento')
                    ->relationship('apartment', 'number')
                    ->getOptionLabelFromRecordUsing(
                        fn(Apartment $record): string => $record->display_name
                    )
                    ->searchable(['number', 'tower'])
                    ->preload()
                    ->required(),

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
                    ->minValue(0)
                    ->required(),

                Forms\Components\DatePicker::make('due_date')
                    ->label('Fecha de vencimiento')
                    ->required(),

                Forms\Components\Select::make('status')
                    ->label('Estado')
                    ->options([
                        'pending' => 'Pendiente',
                        'paid' => 'Pagada',
                        'overdue' => 'Vencida',
                    ])
                    ->default('pending')
                    ->disabled()
                    ->dehydrated(false),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('apartment.number')
                    ->label('Apartamento')
                    ->formatStateUsing(
                        fn($state, $record): string => $record->apartment->display_name
                    )
                    ->searchable()
                    ->sortable(),

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
                    ->label('Monto')
                    ->money('GTQ')
                    ->sortable(),

                Tables\Columns\TextColumn::make('due_date')
                    ->label('Vencimiento')
                    ->date('d/m/Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('payment.paid_at')
                    ->label('Fecha de pago')
                    ->date('d/m/Y')
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('payment.reference')
                    ->label('Referencia')
                    ->placeholder('—')
                    ->toggleable(),

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
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('current_status')
                    ->label('Estado')
                    ->options([
                        'pending' => 'Pendiente',
                        'overdue' => 'Vencida',
                        'paid' => 'Pagada',
                    ])
                    ->query(function ($query, array $data) {
                        return match ($data['value'] ?? null) {
                            'paid' => $query->where('status', 'paid'),

                            'overdue' => $query
                                ->where('status', '!=', 'paid')
                                ->whereDate('due_date', '<', today()),

                            'pending' => $query
                                ->where('status', '!=', 'paid')
                                ->whereDate('due_date', '>=', today()),

                            default => $query,
                        };
                    }),

                Tables\Filters\SelectFilter::make('month')
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
                    ]),
            ])
            ->actions([
                Tables\Actions\Action::make('registerPayment')
                    ->label('Registrar pago')
                    ->icon('heroicon-o-banknotes')
                    ->color('success')
                    ->visible(
                        fn(MaintenanceFee $record): bool =>
                            auth()->user()?->can('pagos.registrar')
                            && $record->status !== 'paid'
                    )
                    ->form([
                        Forms\Components\TextInput::make('amount')
                            ->label('Monto pagado')
                            ->numeric()
                            ->prefix('Q')
                            ->default(fn(MaintenanceFee $record) => $record->amount)
                            ->required()
                            ->rule(function (MaintenanceFee $record) {
                                return function (string $attribute, $value, \Closure $fail) use ($record) {
                                    if ((float) $value !== (float) $record->amount) {
                                        $fail(
                                            'El monto pagado debe ser igual al monto de la cuota: Q ' .
                                            number_format($record->amount, 2)
                                        );
                                    }
                                };
                            }),

                        Forms\Components\DatePicker::make('paid_at')
                            ->label('Fecha de pago')
                            ->default(now())
                            ->required(),

                        Forms\Components\TextInput::make('reference')
                            ->label('Referencia')
                            ->maxLength(255),

                        Forms\Components\Textarea::make('notes')
                            ->label('Observaciones')
                            ->rows(3),

                    ])
                    ->action(function (MaintenanceFee $record, array $data): void {
                        if (!auth()->user()?->can('pagos.registrar')) {
                            abort(403);
                        }
                        $payment = $record->payment()->create([
                            'amount' => $data['amount'],
                            'paid_at' => $data['paid_at'],
                            'reference' => $data['reference'] ?? null,
                            'notes' => $data['notes'] ?? null,
                        ]);

                        $record->update([
                            'status' => 'paid',
                        ]);

                        $record->load([
                            'apartment.residents.user',
                        ]);

                        $users = $record->apartment
                            ->residents
                            ->where('is_active', true)
                            ->pluck('user')
                            ->filter()
                            ->unique('id');

                        foreach ($users as $user) {
                            if ($user->email) {
                                $user->notify(
                                    new PaymentRegisteredNotification($record, $payment)
                                );
                            }
                        }

                        Notification::make()
                            ->title('Pago registrado')
                            ->body("Se registró el pago de {$record->apartment->display_name}.")
                            ->success()
                            ->send();
                    }),
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

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()
            ->with(['apartment', 'payment']);

        $user = auth()->user();

        if (!$user) {
            return $query->whereRaw('1 = 0');
        }

        if ($user->hasRole('Residente')) {
            $apartmentId = $user->apartment()?->id;

            if (!$apartmentId) {
                return $query->whereRaw('1 = 0');
            }

            $query->where('apartment_id', $apartmentId);
        }

        return $query;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMaintenanceFees::route('/'),
            'create' => Pages\CreateMaintenanceFee::route('/create'),
            'edit' => Pages\EditMaintenanceFee::route('/{record}/edit'),
        ];
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->can('cuotas.ver') ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->can('cuotas.crear') ?? false;
    }

    public static function canEdit($record): bool
    {
        return auth()->user()?->can('cuotas.editar') ?? false;
    }

    public static function canDelete($record): bool
    {
        return auth()->user()?->can('cuotas.eliminar') ?? false;
    }

    public static function canDeleteAny(): bool
    {
        return auth()->user()?->can('cuotas.eliminar') ?? false;
    }
}
