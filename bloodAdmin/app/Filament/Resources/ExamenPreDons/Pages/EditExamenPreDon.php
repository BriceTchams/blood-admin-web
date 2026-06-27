<?php

namespace App\Filament\Resources\ExamenPreDons\Pages;

use App\Filament\Resources\ExamenPreDons\ExamenPreDonResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditExamenPreDon extends EditRecord
{
    protected static string $resource = ExamenPreDonResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
