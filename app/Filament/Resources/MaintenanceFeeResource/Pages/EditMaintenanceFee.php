<?php

namespace App\Filament\Resources\MaintenanceFeeResource\Pages;

use App\Filament\Resources\MaintenanceFeeResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMaintenanceFee extends EditRecord
{
    protected static string $resource = MaintenanceFeeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
