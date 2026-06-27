<?php

namespace App\Filament\Resources\ExamenPreDons\Pages;

use App\Filament\Resources\ExamenPreDons\ExamenPreDonResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListExamenPreDons extends ListRecords
{
    protected static string $resource = ExamenPreDonResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
