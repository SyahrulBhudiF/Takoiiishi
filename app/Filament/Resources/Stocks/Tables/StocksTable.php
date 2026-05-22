<?php

namespace App\Filament\Resources\Stocks\Tables;

use App\Filament\Exports\StockExporter;
use App\Support\DateFormat;
use Filament\Actions\ExportAction;
use Filament\Actions\Exports\Enums\ExportFormat;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
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
                IconColumn::make('is_enough')
                    ->label('Status Minimum')
                    ->state(fn ($record): bool => ! $record->isLow())
                    ->boolean()
                    ->trueColor('success')
                    ->falseColor('danger'),
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
                Filter::make('outlet')
                    ->label('Outlet')
                    ->schema([
                        Select::make('outlet_id')
                            ->label('Outlet')
                            ->relationship('outlet', 'name')
                            ->native(false)
                            ->searchable()
                            ->preload(),
                    ])
                    ->query(fn (Builder $query, array $data): Builder => $query->when($data['outlet_id'] ?? null, fn (Builder $query, string $outletId): Builder => $query->where('outlet_id', $outletId))),
                Filter::make('low_stock')
                    ->label('Stok minimum')
                    ->query(fn (Builder $query): Builder => $query->whereColumn('quantity', '<=', 'ingredients.minimum_stock')),
            ])
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->join('ingredients', 'stocks.ingredient_id', '=', 'ingredients.id')->select('stocks.*'))
            ->recordActions([
                ViewAction::make(),

            ])
            ->toolbarActions([
                ExportAction::make()
                    ->exporter(StockExporter::class)
                    ->formats([ExportFormat::Csv, ExportFormat::Xlsx])
                    ->fileName(fn () => 'laporan-stok-' . now()->format('Y-m-d-His')),
            ]);
    }
}
