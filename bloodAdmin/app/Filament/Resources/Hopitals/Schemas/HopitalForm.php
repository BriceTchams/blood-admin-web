<?php

namespace App\Filament\Resources\Hopitals\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class HopitalForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')
                    ->relationship('user', 'id')
                    ->required(),
                TextInput::make('nom')
                    ->required(),
                TextInput::make('ville')
                    ->required(),
                Textarea::make('adresse')
                    ->columnSpanFull(),
                TextInput::make('logo'),
                TextInput::make('email')
                    ->label('Email address')
                    ->email(),
                TextInput::make('telephone_principal')
                    ->tel(),
                Select::make('statut')
                    ->options(['active' => 'Active', 'suspended' => 'Suspended', 'inactive' => 'Inactive'])
                    ->default('active')
                    ->required(),
                DateTimePicker::make('license_expires_at'),
                TextInput::make('code_hopital')
                    ->required(),
                Toggle::make('deleted')
                    ->required(),
                Select::make('sync_statut')
                    ->options(['pending' => 'Pending', 'synced' => 'Synced', 'failed' => 'Failed'])
                    ->default('pending')
                    ->required(),
                DateTimePicker::make('last_synced_at'),
            ]);
    }
}
