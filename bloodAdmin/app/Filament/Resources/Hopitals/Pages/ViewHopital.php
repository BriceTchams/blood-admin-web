<?php

namespace App\Filament\Resources\Hopitals\Pages;

use App\Filament\Resources\Hopitals\HopitalResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewHopital extends ViewRecord
{
    protected static string $resource = HopitalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
