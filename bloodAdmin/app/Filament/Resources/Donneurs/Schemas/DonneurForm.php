<?php

namespace App\Filament\Resources\Donneurs\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class DonneurForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('uuid')
                    ->label('UUID'),
                Select::make('hopital_id')
                    ->relationship('hopital', 'id')
                    ->required(),
                Select::make('groupe_sanguin_id')
                    ->relationship('groupeSanguin', 'id'),
                TextInput::make('nom')
                    ->required(),
                TextInput::make('prenom')
                    ->required(),
                DatePicker::make('date_naissance')
                    ->required(),
                TextInput::make('poids')
                    ->numeric(),
                TextInput::make('telephone')
                    ->tel()
                    ->required(),
                TextInput::make('email')
                    ->label('Email address')
                    ->email(),
                TextInput::make('statut')
                    ->required()
                    ->default('actif'),
                Toggle::make('deleted')
                    ->required(),
                TextInput::make('sync_statut')
                    ->required()
                    ->default('pending'),
                FileUpload::make('image')
                    ->image(),
            ]);
    }
}
