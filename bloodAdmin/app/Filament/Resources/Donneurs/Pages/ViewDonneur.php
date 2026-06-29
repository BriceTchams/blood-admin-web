<?php

namespace App\Filament\Resources\Donneurs\Pages;

use App\Filament\Resources\Donneurs\DonneurResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewDonneur extends ViewRecord
{
    protected static string $resource = DonneurResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
