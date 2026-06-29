<?php

namespace App\Filament\Resources\GroupeSanguins\Pages;

use App\Filament\Resources\GroupeSanguins\GroupeSanguinResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditGroupeSanguin extends EditRecord
{
    protected static string $resource = GroupeSanguinResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
