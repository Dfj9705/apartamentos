<?php

namespace App\Filament\Resources\ReservationResource\Pages;

use App\Filament\Resources\ReservationResource;
use App\Models\User;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use App\Notifications\ReservationConfirmedNotification;
use App\Models\Reservation;
use Illuminate\Validation\ValidationException;
class CreateReservation extends CreateRecord
{
    protected static string $resource = ReservationResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $user = auth()->user();

        if ($user->hasRole('Residente')) {
            $data['user_id'] = $user->id;
            $data['status'] = 'confirmed';
        }

        $reservationUser = User::with('resident')
            ->findOrFail($data['user_id']);

        $data['apartment_id'] = $reservationUser->resident?->apartment_id;

        return $data;
    }
    public static function canCreateAnother(): bool
    {
        $user = auth()->user();

        if (
            $user->hasRole('Administrador') ||
            $user->hasRole('Administración')
        ) {
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

    protected function beforeCreate(): void
    {
        $data = $this->form->getState();

        if (
            Reservation::hasOverlap(
                commonAreaId: (int) $data['common_area_id'],
                date: $data['reservation_date'],
                startTime: $data['start_time'],
                endTime: $data['end_time'],
            )
        ) {
            throw ValidationException::withMessages([
                'data.start_time' =>
                    'Este horario acaba de ser reservado. Seleccione otro horario.',
            ]);
        }
    }
}
