<?php

namespace App\Filament\Resources\Souscriptions\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class SouscriptionInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('id')
                    ->label('ID'),
                TextEntry::make('hopital.id')
                    ->label('Hopital'),
                TextEntry::make('plan')
                    ->badge(),
                TextEntry::make('billing_period')
                    ->badge(),
                TextEntry::make('date_souscription')
                    ->dateTime(),
                TextEntry::make('date_expiration')
                    ->dateTime(),
                TextEntry::make('date_renouvellement')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('statut')
                    ->badge(),
                TextEntry::make('notes')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('sync_statut'),
                TextEntry::make('synced_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
