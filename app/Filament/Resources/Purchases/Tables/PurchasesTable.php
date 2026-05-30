<?php

namespace App\Filament\Resources\Purchases\Tables;

use App\Filament\Exports\PurchaseExporter;
use App\Support\DateFormat;
use Filament\Actions\ExportAction;
use Filament\Actions\Exports\Enums\ExportFormat;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use Illuminate\Database\Eloquent\Builder;

class PurchasesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('purchase_date')
                    ->label('Tanggal')
                    ->date(DateFormat::DATE)
                    ->sortable(),
                TextColumn::make('creator.name')
                    ->label('Dibuat oleh')
                    ->searchable(),
                TextColumn::make('total')
                    ->label('Total')
                    ->getStateUsing(fn (Purchase $record): float => (float) $record->total ?: (float) $record->items()->sum('subtotal'))
                    ->money('IDR')
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
                Filter::make('date_range')
                    ->schema([
                        DatePicker::make('from')->label('Dari tanggal'),
                        DatePicker::make('until')->label('Sampai tanggal'),
                    ])
                    ->query(fn (Builder $query, array $data): Builder => $query
                        ->when($data['from'] ?? null, fn (Builder $query, string $date): Builder => $query->whereDate('purchase_date', '>=', $date))
                        ->when($data['until'] ?? null, fn (Builder $query, string $date): Builder => $query->whereDate('purchase_date', '<=', $date))),
            ])
            ->recordActions([
                ViewAction::make(),

            ])
            ->toolbarActions([
                ExportAction::make()
                    ->exporter(PurchaseExporter::class)
                    ->modifyQueryUsing(fn (array $options): Builder => PurchaseItem::query()
                        ->with(['purchase.creator', 'ingredient'])
                        ->when($options['from'] ?? null, fn (Builder $query, string $date): Builder => $query->whereHas('purchase', fn (Builder $query): Builder => $query->whereDate('purchase_date', '>=', $date)))
                        ->when($options['until'] ?? null, fn (Builder $query, string $date): Builder => $query->whereHas('purchase', fn (Builder $query): Builder => $query->whereDate('purchase_date', '<=', $date))))
                    ->formats([ExportFormat::Csv, ExportFormat::Xlsx])
                    ->fileName(fn () => 'laporan-pembelian-detail-' . now()->format('Y-m-d-His')),
            ]);
    }
}
