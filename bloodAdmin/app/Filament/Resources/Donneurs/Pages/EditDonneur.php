<?php

namespace App\Filament\Resources\Donneurs\Pages;

use App\Filament\Resources\Donneurs\DonneurResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditDonneur extends EditRecord
{
    protected static string $resource = DonneurResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
