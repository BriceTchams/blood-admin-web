<?php

namespace App\Filament\Resources\GroupeSanguins\Pages;

use App\Filament\Resources\GroupeSanguins\GroupeSanguinResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewGroupeSanguin extends ViewRecord
{
    protected static string $resource = GroupeSanguinResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
