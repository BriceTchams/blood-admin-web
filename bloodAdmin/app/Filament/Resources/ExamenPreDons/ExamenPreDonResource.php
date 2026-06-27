<?php

namespace App\Filament\Resources\ExamenPreDons;

use App\Filament\Resources\ExamenPreDons\Pages\CreateExamenPreDon;
use App\Filament\Resources\ExamenPreDons\Pages\EditExamenPreDon;
use App\Filament\Resources\ExamenPreDons\Pages\ListExamenPreDons;
use App\Filament\Resources\ExamenPreDons\Pages\ViewExamenPreDon;
use App\Filament\Resources\ExamenPreDons\Schemas\ExamenPreDonForm;
use App\Filament\Resources\ExamenPreDons\Schemas\ExamenPreDonInfolist;
use App\Filament\Resources\ExamenPreDons\Tables\ExamenPreDonsTable;
use App\Models\ExamenPreDon;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ExamenPreDonResource extends Resource
{
    protected static ?string $model = ExamenPreDon::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'ExamenPreDon';

    public static function form(Schema $schema): Schema
    {
        return ExamenPreDonForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ExamenPreDonInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ExamenPreDonsTable::configure($table);
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
            'index' => ListExamenPreDons::route('/'),
            'create' => CreateExamenPreDon::route('/create'),
            'view' => ViewExamenPreDon::route('/{record}'),
            'edit' => EditExamenPreDon::route('/{record}/edit'),
        ];
    }
}
