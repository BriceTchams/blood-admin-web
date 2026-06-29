<?php

namespace App\Filament\Resources\GroupeSanguins\Pages;

use App\Filament\Resources\GroupeSanguins\GroupeSanguinResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListGroupeSanguins extends ListRecords
{
    protected static string $resource = GroupeSanguinResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
