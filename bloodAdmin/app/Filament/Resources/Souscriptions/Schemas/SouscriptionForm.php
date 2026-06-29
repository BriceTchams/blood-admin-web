<?php

namespace App\Filament\Resources\Souscriptions\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class SouscriptionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('hopital_id')
                    ->relationship('hopital', 'id')
                    ->required(),
                Select::make('plan')
                    ->options(['trial' => 'Trial', 'basic' => 'Basic', 'premium' => 'Premium', 'enterprise' => 'Enterprise'])
                    ->default('trial')
                    ->required(),
                Select::make('billing_period')
                    ->options(['monthly' => 'Monthly', 'yearly' => 'Yearly'])
                    ->default('yearly')
                    ->required(),
                DateTimePicker::make('date_souscription')
                    ->required(),
                DateTimePicker::make('date_expiration')
                    ->required(),
                DateTimePicker::make('date_renouvellement'),
                Select::make('statut')
                    ->options([
            'active' => 'Active',
            'inactive' => 'Inactive',
            'suspended' => 'Suspended',
            'cancelled' => 'Cancelled',
        ])
                    ->default('active')
                    ->required(),
                Textarea::make('notes')
                    ->columnSpanFull(),
                TextInput::make('sync_statut')
                    ->required()
                    ->default('pending'),
                DateTimePicker::make('synced_at'),
            ]);
    }
}
