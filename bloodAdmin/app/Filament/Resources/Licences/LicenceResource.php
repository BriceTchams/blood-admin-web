<?php

namespace App\Filament\Resources\Licences;

use App\Filament\Resources\Licences\Pages\CreateLicence;
use App\Filament\Resources\Licences\Pages\EditLicence;
use App\Filament\Resources\Licences\Pages\ListLicences;
use App\Filament\Resources\Licences\Pages\ViewLicence;
use App\Filament\Resources\Licences\Schemas\LicenceForm;
use App\Filament\Resources\Licences\Schemas\LicenceInfolist;
use App\Filament\Resources\Licences\Tables\LicencesTable;
use App\Models\License;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class LicenceResource extends Resource
{
    protected static ?string $model = License::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return LicenceForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return LicenceInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LicencesTable::configure($table);
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
            'index' => ListLicences::route('/'),
            'create' => CreateLicence::route('/create'),
            'view' => ViewLicence::route('/{record}'),
            'edit' => EditLicence::route('/{record}/edit'),
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
