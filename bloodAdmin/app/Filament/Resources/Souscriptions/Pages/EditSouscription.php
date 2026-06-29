<?php

namespace App\Filament\Resources\Souscriptions\Pages;

use App\Filament\Resources\Souscriptions\SouscriptionResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditSouscription extends EditRecord
{
    protected static string $resource = SouscriptionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
