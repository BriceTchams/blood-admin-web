<?php

namespace App\Filament\Resources\Souscriptions\Pages;

use App\Filament\Resources\Souscriptions\SouscriptionResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewSouscription extends ViewRecord
{
    protected static string $resource = SouscriptionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
