<?php

namespace App\Filament\Resources\Souscriptions\Pages;

use App\Filament\Resources\Souscriptions\SouscriptionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSouscriptions extends ListRecords
{
    protected static string $resource = SouscriptionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
