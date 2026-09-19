<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ResidentResource\Pages;
use App\Filament\Resources\ResidentResource\RelationManagers;
use App\Models\Resident;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ResidentResource extends Resource
{
    protected static ?string $model = Resident::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationLabel = 'Residentes';

    protected static ?string $modelLabel = 'Residente';

    protected static ?string $pluralModelLabel = 'Residentes';

    protected static ?string $navigationGroup = 'Administración';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información del residente')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nombre completo')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('email')
                            ->label('Correo electrónico')
                            ->email()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('phone')
                            ->label('Teléfono')
                            ->tel()
                            ->maxLength(30),

                        Forms\Components\Select::make('type')
                            ->label('Tipo de residente')
                            ->options([
                                'owner' => 'Propietario',
                                'tenant' => 'Inquilino',
                            ])
                            ->default('tenant')
                            ->required(),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Residente activo')
                            ->default(true),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Apartamento y acceso')
                    ->schema([
                        Forms\Components\Select::make('apartment_id')
                            ->label('Apartamento')
                            ->relationship(
                                name: 'apartment',
                                titleAttribute: 'number',
                            )
                            ->getOptionLabelFromRecordUsing(
                                fn($record) =>
                                    ($record->tower ? "Torre {$record->tower} - " : '')
                                    . "Apartamento {$record->number}"
                            )
                            ->searchable()
                            ->preload()
                            ->required(),

                        Forms\Components\Select::make('user_id')
                            ->label('Usuario del sistema')
                            ->relationship(
                                name: 'user',
                                titleAttribute: 'name',
                                modifyQueryUsing: function ($query, ?Resident $record) {
                                    $query->whereDoesntHave('resident');

                                    if ($record?->user_id) {
                                        $query->orWhere('id', $record->user_id);
                                    }
                                },
                            )
                            ->searchable()
                            ->preload()
                            ->nullable()
                            ->helperText(
                                'Opcional. Vincula al residente con una cuenta que pueda ingresar al sistema.'
                            ),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Residente')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('apartment.number')
                    ->label('Apartamento')
                    ->formatStateUsing(function ($state, $record) {
                        return ($record->apartment->tower
                            ? "Torre {$record->apartment->tower} - "
                            : '')
                            . $record->apartment->number;
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('type')
                    ->label('Tipo')
                    ->badge()
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'owner' => 'Propietario',
                        'tenant' => 'Inquilino',
                        default => $state,
                    })
                    ->color(fn(string $state): string => match ($state) {
                        'owner' => 'success',
                        'tenant' => 'info',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('phone')
                    ->label('Teléfono')
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('email')
                    ->label('Correo')
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Usuario')
                    ->placeholder('Sin acceso')
                    ->searchable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Activo')
                    ->boolean()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label('Tipo')
                    ->options([
                        'owner' => 'Propietario',
                        'tenant' => 'Inquilino',
                    ]),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Estado')
                    ->trueLabel('Activos')
                    ->falseLabel('Inactivos')
                    ->placeholder('Todos'),
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
            'index' => Pages\ListResidents::route('/'),
            'create' => Pages\CreateResident::route('/create'),
            'edit' => Pages\EditResident::route('/{record}/edit'),
        ];
    }
}
