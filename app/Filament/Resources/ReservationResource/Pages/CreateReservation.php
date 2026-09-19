<?php

namespace App\Filament\Resources\ReservationResource\Pages;

use App\Filament\Resources\ReservationResource;
use App\Models\User;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use App\Notifications\ReservationConfirmedNotification;

class CreateReservation extends CreateRecord
{
    protected static string $resource = ReservationResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $user = auth()->user();

        if (!$user->hasRole('Administrador')) {
            $data['user_id'] = $user->id;
            $data['apartment_id'] = $user->apartment()?->id;
            $data['status'] = 'confirmed';
        }

        $reservationUser = User::with('resident')
            ->findOrFail($data['user_id']);

        $data['apartment_id'] =
            $reservationUser->resident?->apartment_id;

        return $data;
    }
    public static function canCreateAnother(): bool
    {
        $user = auth()->user();

        if ($user->hasRole('Administrador')) {
            return true;
        }

        return $user->hasApartment();
    }

    protected function afterCreate(): void
    {
        $reservation = $this->record;

        $reservation->load([
            'user',
            'apartment',
            'commonArea',
        ]);

        if (
            $reservation->status === 'confirmed'
            && $reservation->user
        ) {
            $reservation->user->notify(
                new ReservationConfirmedNotification($reservation)
            );
        }
    }
}
