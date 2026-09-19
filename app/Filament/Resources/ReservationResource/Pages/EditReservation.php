<?php

namespace App\Filament\Resources\ReservationResource\Pages;

use App\Filament\Resources\ReservationResource;
use App\Models\User;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditReservation extends EditRecord
{
    protected static string $resource = ReservationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $user = auth()->user();

        if (!$user->hasRole('Administrador')) {
            $data['user_id'] = $user->id;
            $data['apartment_id'] = $user->apartment()?->id;
        }

        $reservationUser = User::with('resident')
            ->findOrFail($data['user_id']);

        $data['apartment_id'] = $reservationUser->resident?->apartment_id;

        return $data;
    }
}
