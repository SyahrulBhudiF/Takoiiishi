<?php

namespace App\Filament\Resources\Ingredients\Tables;

use App\Support\DateFormat;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class IngredientsTable
{
    private static function formatQuantity(float $quantity, string $unit): string
    {
        $formatted = rtrim(rtrim(number_format($quantity, 2, ',', '.'), '0'), ',');

        return "{$formatted} {$unit}";
    }

    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nama Bahan')
                    ->searchable(),
                TextColumn::make('minimum_stock')
                    ->label('Stok Minimum')
                    ->formatStateUsing(fn ($state, $record): string => self::formatQuantity((float) $state, $record->unit))
                    ->sortable(),
                TextColumn::make('usage_per_portion')
                    ->label('Pemakaian per Porsi')
                    ->formatStateUsing(fn ($state, $record): string => self::formatQuantity((float) $state, $record->unit))
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime(DateFormat::DATE_TIME)
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label('Diperbarui')
                    ->dateTime(DateFormat::DATE_TIME)
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
