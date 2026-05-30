<?php

namespace App\Filament\Exports;

use App\Models\Stock;
use Filament\Actions\Exports\ExportColumn;
use Filament\Forms\Components\DatePicker;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Support\Number;

class StockExporter extends Exporter
{
    protected static ?string $model = Stock::class;

    public static function getOptionsFormComponents(): array
    {
        return [
            DatePicker::make('from')->label('Diperbarui dari tanggal'),
            DatePicker::make('until')->label('Diperbarui sampai tanggal'),
        ];
    }

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('outlet.name')->label('Outlet'),
            ExportColumn::make('outlet.type')->label('Tipe Outlet'),
            ExportColumn::make('ingredient.name')->label('Bahan'),
            ExportColumn::make('ingredient.unit')->label('Satuan'),
            ExportColumn::make('quantity')->label('Stok'),
            ExportColumn::make('ingredient.minimum_stock')->label('Stok Minimum'),
            ExportColumn::make('stock_gap')
                ->label('Selisih dari Minimum')
                ->state(fn (Stock $record): float => (float) $record->quantity - (float) $record->ingredient?->minimum_stock),
            ExportColumn::make('status')
                ->label('Status')
                ->state(fn (Stock $record): string => $record->isLow() ? 'Stok menipis' : 'Aman'),
            ExportColumn::make('updated_at')->label('Terakhir Diperbarui'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Export stok selesai. ' . Number::format($export->successful_rows) . ' baris berhasil diexport.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . Number::format($failedRowsCount) . ' baris gagal.';
        }

        return $body;
    }
}
