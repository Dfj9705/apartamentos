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
            ->where(function ($query) {
                $query
                    ->whereDate('reservation_date', '<', today())
                    ->orWhere(function ($query) {
                        $query
                            ->whereDate('reservation_date', today())
                            ->whereTime('end_time', '<', now()->format('H:i:s'));
                    });
            })
            ->update([
                'status' => 'completed',
            ]);
    }
}
