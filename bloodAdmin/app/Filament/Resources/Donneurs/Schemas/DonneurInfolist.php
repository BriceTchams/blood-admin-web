<?php

namespace App\Filament\Resources\Donneurs\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class DonneurInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('id')
                    ->label('ID'),
                TextEntry::make('uuid')
                    ->label('UUID')
                    ->placeholder('-'),
                TextEntry::make('hopital.id')
                    ->label('Hopital'),
                TextEntry::make('groupeSanguin.id')
                    ->label('Groupe sanguin')
                    ->placeholder('-'),
                TextEntry::make('nom'),
                TextEntry::make('prenom'),
                TextEntry::make('date_naissance')
                    ->date(),
                TextEntry::make('poids')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('telephone'),
                TextEntry::make('email')
                    ->label('Email address')
                    ->placeholder('-'),
                TextEntry::make('statut'),
                IconEntry::make('deleted')
                    ->boolean(),
                TextEntry::make('sync_statut'),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
                ImageEntry::make('image')
                    ->placeholder('-'),
            ]);
    }
}
