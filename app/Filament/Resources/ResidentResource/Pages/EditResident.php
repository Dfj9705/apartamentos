<?php

namespace App\Filament\Resources\ResidentResource\Pages;

use App\Filament\Resources\ResidentResource;
use App\Models\Apartment;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditResident extends EditRecord
{
    protected static string $resource = ResidentResource::class;

    protected ?int $previousApartmentId = null;

    protected function beforeSave(): void
    {
        $this->previousApartmentId = $this->record->apartment_id;
    }

    protected function afterSave(): void
    {
        // Actualizar el apartamento anterior.
        if ($this->previousApartmentId) {
            Apartment::find($this->previousApartmentId)
                    ?->updateOccupancyStatus();
        }

        // Actualizar el apartamento actual.
        $this->record->refresh();
        $this->record->apartment
                ?->updateOccupancyStatus();
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->before(function () {
                    $this->previousApartmentId = $this->record->apartment_id;
                })
                ->after(function () {
                    if ($this->previousApartmentId) {
                        Apartment::find($this->previousApartmentId)
                                ?->updateOccupancyStatus();
                    }
                }),
        ];
    }
}