<?php

namespace App\Filament\Resources\CommonAreaResource\Pages;

use App\Filament\Resources\CommonAreaResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCommonAreas extends ListRecords
{
    protected static string $resource = CommonAreaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
