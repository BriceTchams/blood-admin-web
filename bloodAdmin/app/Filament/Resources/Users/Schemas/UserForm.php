<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name'),
                TextInput::make('type')
                    ->required()
                    ->default('admin'),
                TextInput::make('login'),
                TextInput::make('password_hash')
                    ->password(),
                TextInput::make('role')
                    ->required()
                    ->default('user'),
                TextInput::make('telephone')
                    ->tel(),
                TextInput::make('uuid')
                    ->label('UUID'),
                Toggle::make('deleted')
                    ->required(),
                Select::make('sync_statut')
                    ->options(['pending' => 'Pending', 'synced' => 'Synced', 'failed' => 'Failed', 'conflict' => 'Conflict'])
                    ->default('pending')
                    ->required(),
                TextInput::make('email')
                    ->label('Email address')
                    ->email(),
                TextInput::make('password')
                    ->password(),
            ]);
    }
}
