<?php

namespace App\Filament\Resources\StockMovements\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class StockMovementsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('outlet.name')
                    ->label('Outlet')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('ingredient.name')
                    ->label('Bahan')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('type')
                    ->searchable(),
                TextColumn::make('qty_in')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('qty_out')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('reference')
                    ->searchable(),
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

            ])
            ->toolbarActions([]);
    }
}
