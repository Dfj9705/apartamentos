<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ActivityResource\Pages;
use App\Filament\Resources\ActivityResource\RelationManagers;
use Spatie\Activitylog\Models\Activity;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ActivityResource extends Resource
{
    protected static ?string $model = Activity::class;

    protected static ?string $navigationIcon = 'heroicon-o-clock';
    protected static ?string $navigationGroup = 'Gestión';

    protected static ?string $navigationLabel = 'Historial de actividad';

    protected static ?string $modelLabel = 'Actividad';

    protected static ?string $pluralModelLabel = 'Historial de actividad';

    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Fecha')
                    ->dateTime('d/m/Y H:i:s')
                    ->sortable(),

                Tables\Columns\TextColumn::make('causer.name')
                    ->label('Usuario')
                    ->placeholder('Sistema')
                    ->searchable(),

                Tables\Columns\TextColumn::make('event')
                    ->label('Evento')
                    ->badge()
                    ->formatStateUsing(fn(?string $state): string => match ($state) {
                        'created' => 'Creado',
                        'updated' => 'Actualizado',
                        'deleted' => 'Eliminado',
                        default => ucfirst($state ?? 'Evento'),
                    }),

                Tables\Columns\TextColumn::make('subject_type')
                    ->label('Entidad')
                    ->formatStateUsing(
                        fn(?string $state): string => class_basename($state ?? '')
                    ),

                Tables\Columns\TextColumn::make('subject_id')
                    ->label('ID'),

                Tables\Columns\TextColumn::make('description')
                    ->label('Descripción')
                    ->limit(40),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([])
            ->actions([
                Tables\Actions\Action::make('viewChanges')
                    ->label('Ver cambios')
                    ->icon('heroicon-o-eye')
                    ->modalHeading('Detalle de actividad')
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Cerrar')
                    ->modalContent(fn(Activity $record) => view(
                        'filament.resources.activity-resource.view-changes',
                        ['activity' => $record]
                    )),
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
            'index' => Pages\ListActivities::route('/'),
            'create' => Pages\CreateActivity::route('/create'),
            'edit' => Pages\EditActivity::route('/{record}/edit'),
        ];
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->hasRole('Administrador') ?? false;
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
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
