<?php

namespace App\Filament\Resources\Hopitals\Schemas;

use App\Models\Hopital;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class HopitalInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('id')
                    ->label('ID'),
                TextEntry::make('user.id')
                    ->label('User'),
                TextEntry::make('nom'),
                TextEntry::make('ville'),
                TextEntry::make('adresse')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('logo')
                    ->placeholder('-'),
                TextEntry::make('email')
                    ->label('Email address')
                    ->placeholder('-'),
                TextEntry::make('telephone_principal')
                    ->placeholder('-'),
                TextEntry::make('statut')
                    ->badge(),
                TextEntry::make('license_expires_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('code_hopital'),
                IconEntry::make('deleted')
                    ->boolean(),
                TextEntry::make('sync_statut')
                    ->badge(),
                TextEntry::make('last_synced_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('deleted_at')
                    ->dateTime()
                    ->visible(fn (Hopital $record): bool => $record->trashed()),
            ]);
    }
}
