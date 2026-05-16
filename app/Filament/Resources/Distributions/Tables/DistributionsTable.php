<?php

namespace App\Filament\Resources\Distributions\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class DistributionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('distribution_date')
                    ->date()
                    ->sortable(),
                TextColumn::make('fromOutlet.name')
                    ->label('Dari')
                    ->searchable(),
                TextColumn::make('toOutlet.name')
                    ->label('Ke')
                    ->searchable(),
                TextColumn::make('creator.name')
                    ->label('Dibuat oleh')
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
