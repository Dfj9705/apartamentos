<?php

namespace App\Filament\Resources\ReservationResource\Pages;

use App\Filament\Resources\ReservationResource;
use App\Models\Reservation;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Carbon\Carbon;

class ListReservations extends ListRecords
{
    protected static string $resource = ReservationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function mount(): void
    {
        parent::mount();

        Reservation::query()
            ->where('status', 'confirmed')
            ->get()
            ->each(function (Reservation $reservation) {
                if ($reservation->hasEnded()) {
                    $reservation->update([
                        'status' => 'completed',
                    ]);
                }
            });
    }
}
