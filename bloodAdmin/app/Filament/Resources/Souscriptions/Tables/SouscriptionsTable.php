<?php

namespace App\Filament\Resources\Souscriptions\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SouscriptionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->searchable(),
                TextColumn::make('hopital.id')
                    ->searchable(),
                TextColumn::make('plan')
                    ->badge(),
                TextColumn::make('billing_period')
                    ->badge(),
                TextColumn::make('date_souscription')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('date_expiration')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('date_renouvellement')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('statut')
                    ->badge(),
                TextColumn::make('sync_statut')
                    ->searchable(),
                TextColumn::make('synced_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
