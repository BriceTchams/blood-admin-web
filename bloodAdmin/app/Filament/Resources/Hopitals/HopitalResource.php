<?php

namespace App\Filament\Resources\Hopitals;

use App\Filament\Resources\Hopitals\Pages\CreateHopital;
use App\Filament\Resources\Hopitals\Pages\EditHopital;
use App\Filament\Resources\Hopitals\Pages\ListHopitals;
use App\Filament\Resources\Hopitals\Pages\ViewHopital;
use App\Filament\Resources\Hopitals\Schemas\HopitalForm;
use App\Filament\Resources\Hopitals\Schemas\HopitalInfolist;
use App\Filament\Resources\Hopitals\Tables\HopitalsTable;
use App\Models\Hopital;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class HopitalResource extends Resource
{
    protected static ?string $model = Hopital::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return HopitalForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return HopitalInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return HopitalsTable::configure($table);
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
            'index' => ListHopitals::route('/'),
            'create' => CreateHopital::route('/create'),
            'view' => ViewHopital::route('/{record}'),
            'edit' => EditHopital::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
