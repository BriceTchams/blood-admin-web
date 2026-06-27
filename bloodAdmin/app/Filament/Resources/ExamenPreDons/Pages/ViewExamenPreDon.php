<?php

namespace App\Filament\Resources\ExamenPreDons\Pages;

use App\Filament\Resources\ExamenPreDons\ExamenPreDonResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewExamenPreDon extends ViewRecord
{
    protected static string $resource = ExamenPreDonResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
