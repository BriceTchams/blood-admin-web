<?php

namespace App\Filament\Resources\GroupeSanguins\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class GroupeSanguinInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('id')
                    ->label('ID'),
                TextEntry::make('uuid')
                    ->label('UUID'),
                TextEntry::make('libelle')
                    ->badge(),
                TextEntry::make('rhesus')
                    ->badge(),
                IconEntry::make('deleted')
                    ->boolean(),
                TextEntry::make('sync_statut'),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
