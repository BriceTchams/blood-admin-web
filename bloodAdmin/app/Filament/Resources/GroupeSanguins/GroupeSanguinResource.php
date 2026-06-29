<?php

namespace App\Filament\Resources\GroupeSanguins;

use App\Filament\Resources\GroupeSanguins\Pages\CreateGroupeSanguin;
use App\Filament\Resources\GroupeSanguins\Pages\EditGroupeSanguin;
use App\Filament\Resources\GroupeSanguins\Pages\ListGroupeSanguins;
use App\Filament\Resources\GroupeSanguins\Pages\ViewGroupeSanguin;
use App\Filament\Resources\GroupeSanguins\Schemas\GroupeSanguinForm;
use App\Filament\Resources\GroupeSanguins\Schemas\GroupeSanguinInfolist;
use App\Filament\Resources\GroupeSanguins\Tables\GroupeSanguinsTable;
use App\Models\GroupeSanguin;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class GroupeSanguinResource extends Resource
{
    protected static ?string $model = GroupeSanguin::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return GroupeSanguinForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return GroupeSanguinInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return GroupeSanguinsTable::configure($table);
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
            'index' => ListGroupeSanguins::route('/'),
            'create' => CreateGroupeSanguin::route('/create'),
            'view' => ViewGroupeSanguin::route('/{record}'),
            'edit' => EditGroupeSanguin::route('/{record}/edit'),
        ];
    }
}
