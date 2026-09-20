<?php

namespace App\Filament\Resources\ReservationResource\Pages;

use App\Filament\Resources\ReservationResource;
use App\Models\User;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use App\Models\Reservation;
use Illuminate\Validation\ValidationException;

class EditReservation extends EditRecord
{
    protected static string $resource = ReservationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->visible(
                    fn(): bool =>
                        auth()->user()?->can('reservas.eliminar') ?? false
                ),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $user = auth()->user();

        if ($user->hasRole('Residente')) {
            $data['user_id'] = $user->id;
        }

        $reservationUser = User::with('resident')
            ->findOrFail($data['user_id']);

        $data['apartment_id'] = $reservationUser->resident?->apartment_id;

        return $data;
    }

    protected function beforeSave(): void
    {
        $data = $this->form->getState();

        if (
            Reservation::hasOverlap(
                commonAreaId: (int) $data['common_area_id'],
                date: $data['reservation_date'],
                startTime: $data['start_time'],
                endTime: $data['end_time'],
                ignoreReservationId: $this->record->id,
            )
        ) {
            throw ValidationException::withMessages([
                'data.start_time' =>
                    'Este horario ya está reservado. Seleccione otro horario.',
            ]);
        }
    }
}
