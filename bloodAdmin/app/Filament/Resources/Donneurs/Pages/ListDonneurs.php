<?php

namespace App\Filament\Resources\Donneurs\Pages;

use App\Filament\Resources\Donneurs\DonneurResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListDonneurs extends ListRecords
{
    protected static string $resource = DonneurResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
