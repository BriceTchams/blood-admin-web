<?php

namespace App\Filament\Resources\GroupeSanguins\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class GroupeSanguinForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('uuid')
                    ->label('UUID')
                    ->required(),
                Select::make('libelle')
                    ->options(['A' => 'A', 'B' => 'B', 'AB' => 'A b', 'O' => 'O'])
                    ->required(),
                Select::make('rhesus')
                    ->options(['+' => '+', '-' => ' '])
                    ->required(),
                Toggle::make('deleted')
                    ->required(),
                TextInput::make('sync_statut')
                    ->required()
                    ->default('pending'),
            ]);
    }
}
