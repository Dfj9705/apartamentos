<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationLabel = 'Usuarios';

    protected static ?string $modelLabel = 'Usuario';

    protected static ?string $pluralModelLabel = 'Usuarios';

    protected static ?string $navigationGroup = 'Gestión';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información del usuario')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nombre')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('email')
                            ->label('Correo electrónico')
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),

                        Forms\Components\TextInput::make('password')
                            ->label('Contraseña')
                            ->password()
                            ->revealable()
                            ->required(fn(string $operation): bool => $operation === 'create')
                            ->dehydrated(fn($state): bool => filled($state))
                            ->minLength(8)
                            ->maxLength(255)
                            ->helperText(
                                fn(string $operation): ?string =>
                                    $operation === 'edit'
                                    ? 'Déjala vacía para conservar la contraseña actual.'
                                    : null
                            ),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Usuario activo')
                            ->default(true)
                            ->disabled(
                                fn(?User $record): bool =>
                                    $record !== null && auth()->id() === $record->id
                            )
                            ->dehydrated(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Acceso')
                    ->schema([
                        Forms\Components\Select::make('roles')
                            ->label('Rol')
                            ->relationship('roles', 'name')
                            ->preload()
                            ->searchable()
                            ->required(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('email')
                    ->label('Correo')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('roles.name')
                    ->label('Rol')
                    ->badge(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Activo')
                    ->boolean(),

                Tables\Columns\TextColumn::make('resident.apartment.number')
                    ->label('Apartamento')
                    ->formatStateUsing(function ($state, $record) {
                        if (!$record->resident?->apartment) {
                            return '—';
                        }

                        $apartment = $record->resident->apartment;

                        return ($apartment->tower
                            ? "{$apartment->tower}-"
                            : '')
                            . $apartment->number;
                    }),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Estado')
                    ->trueLabel('Activos')
                    ->falseLabel('Inactivos')
                    ->placeholder('Todos'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([]);
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->can('usuarios.ver') ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->can('usuarios.crear') ?? false;
    }

    public static function canEdit($record): bool
    {
        return auth()->user()?->can('usuarios.editar') ?? false;
    }

    public static function canDelete($record): bool
    {
        if (auth()->id() === $record->id) {
            return false;
        }

        return auth()->user()?->can('usuarios.eliminar') ?? false;
    }
    public static function canDeleteAny(): bool
    {
        return auth()->user()?->can('usuarios.eliminar') ?? false;
    }
}
