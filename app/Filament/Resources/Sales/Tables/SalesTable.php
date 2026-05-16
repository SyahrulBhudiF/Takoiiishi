<?php

namespace App\Filament\Resources\Sales\Tables;

use App\Filament\Exports\SaleExporter;
use Filament\Actions\ExportAction;
use Filament\Actions\Exports\Enums\ExportFormat;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class SalesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('sale_date')
                    ->date()
                    ->sortable(),
                TextColumn::make('outlet.name')
                    ->label('Outlet')
                    ->searchable(),
                TextColumn::make('portion_qty')
                    ->numeric()
                    ->sortable(),
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
                Filter::make('date_range')
                    ->schema([
                        DatePicker::make('from')->label('Dari tanggal'),
                        DatePicker::make('until')->label('Sampai tanggal'),
                    ])
                    ->query(fn (Builder $query, array $data): Builder => $query
                        ->when($data['from'] ?? null, fn (Builder $query, string $date): Builder => $query->whereDate('sale_date', '>=', $date))
                        ->when($data['until'] ?? null, fn (Builder $query, string $date): Builder => $query->whereDate('sale_date', '<=', $date))),
                Filter::make('outlet')
                    ->schema([
                        Select::make('outlet_id')->relationship('outlet', 'name'),
                    ])
                    ->query(fn (Builder $query, array $data): Builder => $query->when($data['outlet_id'] ?? null, fn (Builder $query, string $outletId): Builder => $query->where('outlet_id', $outletId))),
            ])
            ->recordActions([
                ViewAction::make(),

            ])
            ->toolbarActions([
                ExportAction::make()
                    ->exporter(SaleExporter::class)
                    ->formats([ExportFormat::Csv, ExportFormat::Xlsx])
                    ->fileName(fn () => 'laporan-penjualan-' . now()->format('Y-m-d-His')),
            ]);
    }
}
