<?php

namespace App\Filament\Resources\Hopitals\Pages;

use App\Filament\Resources\Hopitals\HopitalResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListHopitals extends ListRecords
{
    protected static string $resource = HopitalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
