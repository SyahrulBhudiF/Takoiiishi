<?php

namespace App\Filament\Resources\Stocks\Tables;

use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class StocksTable
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
                TextColumn::make('quantity')
                    ->label('Stok')
                    ->numeric()
                    ->sortable()
                    ->color(fn ($record): string => $record->isLow() ? 'danger' : 'success')
                    ->weight(fn ($record): string => $record->isLow() ? 'bold' : 'normal'),
                TextColumn::make('ingredient.minimum_stock')
                    ->label('Minimum')
                    ->numeric()
                    ->sortable(),
                IconColumn::make('is_low')
                    ->label('Minimum')
                    ->state(fn ($record): bool => $record->isLow())
                    ->boolean()
                    ->trueColor('danger')
                    ->falseColor('success'),
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
                Filter::make('low_stock')
                    ->label('Stok minimum')
                    ->query(fn (Builder $query): Builder => $query->whereColumn('quantity', '<=', 'ingredients.minimum_stock')),
            ])
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->join('ingredients', 'stocks.ingredient_id', '=', 'ingredients.id')->select('stocks.*'))
            ->recordActions([
                ViewAction::make(),

            ])
            ->toolbarActions([]);
    }
}
