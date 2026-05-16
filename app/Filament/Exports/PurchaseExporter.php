<?php

namespace App\Filament\Exports;

use App\Models\PurchaseItem;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Support\Number;

class PurchaseExporter extends Exporter
{
    protected static ?string $model = PurchaseItem::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('purchase.purchase_date')->label('Tanggal'),
            ExportColumn::make('purchase.creator.name')->label('Dibuat Oleh'),
            ExportColumn::make('ingredient.name')->label('Bahan'),
            ExportColumn::make('quantity')->label('Jumlah'),
            ExportColumn::make('ingredient.unit')->label('Satuan'),
            ExportColumn::make('price')->label('Harga'),
            ExportColumn::make('subtotal')->label('Subtotal'),
            ExportColumn::make('purchase.total')->label('Total Pembelian'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Export pembelian selesai. ' . Number::format($export->successful_rows) . ' baris item berhasil diexport.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . Number::format($failedRowsCount) . ' baris gagal.';
        }

        return $body;
    }
}
