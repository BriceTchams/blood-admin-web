<?php

namespace App\Filament\Resources\Donneurs;

use App\Filament\Resources\Donneurs\Pages\CreateDonneur;
use App\Filament\Resources\Donneurs\Pages\EditDonneur;
use App\Filament\Resources\Donneurs\Pages\ListDonneurs;
use App\Filament\Resources\Donneurs\Pages\ViewDonneur;
use App\Filament\Resources\Donneurs\Schemas\DonneurForm;
use App\Filament\Resources\Donneurs\Schemas\DonneurInfolist;
use App\Filament\Resources\Donneurs\Tables\DonneursTable;
use App\Models\Donneur;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class DonneurResource extends Resource
{
    protected static ?string $model = Donneur::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return DonneurForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return DonneurInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DonneursTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDonneurs::route('/'),
            'create' => CreateDonneur::route('/create'),
            'view' => ViewDonneur::route('/{record}'),
            'edit' => EditDonneur::route('/{record}/edit'),
        ];
    }
}
