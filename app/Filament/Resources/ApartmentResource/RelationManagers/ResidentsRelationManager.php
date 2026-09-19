<?php

namespace App\Filament\Resources\ApartmentResource\RelationManagers;

use App\Models\Resident;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ResidentsRelationManager extends RelationManager
{
    protected static string $relationship = 'residents';

    public function form(Form $form): Form
    {
        return $form
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
                    ->label('Tipo')
                    ->options([
                        'owner' => 'Propietario',
                        'tenant' => 'Inquilino',
                    ])
                    ->default('tenant')
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
                    ->nullable(),

                Forms\Components\Toggle::make('is_active')
                    ->label('Activo')
                    ->default(true),
            ])
            ->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Residente')
                    ->searchable(),

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
                    ->label('Teléfono'),

                Tables\Columns\IconColumn::make('user_id')
                    ->label('Acceso')
                    ->boolean()
                    ->getStateUsing(
                        fn($record): bool => $record->user_id !== null
                    ),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Activo')
                    ->boolean(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Agregar residente'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }
}
