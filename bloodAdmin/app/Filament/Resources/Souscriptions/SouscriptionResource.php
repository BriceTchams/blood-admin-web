<?php

namespace App\Filament\Resources\Souscriptions;

use App\Filament\Resources\Souscriptions\Pages\CreateSouscription;
use App\Filament\Resources\Souscriptions\Pages\EditSouscription;
use App\Filament\Resources\Souscriptions\Pages\ListSouscriptions;
use App\Filament\Resources\Souscriptions\Pages\ViewSouscription;
use App\Filament\Resources\Souscriptions\Schemas\SouscriptionForm;
use App\Filament\Resources\Souscriptions\Schemas\SouscriptionInfolist;
use App\Filament\Resources\Souscriptions\Tables\SouscriptionsTable;
use App\Models\Souscription;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class SouscriptionResource extends Resource
{
    protected static ?string $model = Souscription::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return SouscriptionForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return SouscriptionInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SouscriptionsTable::configure($table);
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
            'index' => ListSouscriptions::route('/'),
            'create' => CreateSouscription::route('/create'),
            'view' => ViewSouscription::route('/{record}'),
            'edit' => EditSouscription::route('/{record}/edit'),
        ];
    }
}
