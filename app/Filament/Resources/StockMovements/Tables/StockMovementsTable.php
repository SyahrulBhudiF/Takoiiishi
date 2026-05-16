<?php

namespace App\Filament\Resources\StockMovements\Tables;

use App\Support\DateFormat;
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
                    ->label('Jenis Mutasi')
                    ->searchable(),
                TextColumn::make('qty_in')
                    ->label('Masuk')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('qty_out')
                    ->label('Keluar')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Tanggal')
                    ->dateTime(DateFormat::DATE_TIME)
                    ->sortable(),
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
                ViewAction::make(),

            ])
            ->toolbarActions([]);
    }
}
