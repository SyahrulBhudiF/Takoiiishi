<?php

namespace App\Filament\Resources\Sales\Schemas;

use App\Support\DateFormat;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SaleInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Ringkasan Penjualan')
                    ->columnSpanFull()
                    ->columns(4)
                    ->schema([
                        TextEntry::make('sale_date')
                            ->label('Tanggal')
                            ->date(DateFormat::DATE)
                            ->badge()
                            ->color('warning'),
                        TextEntry::make('outlet.name')
                            ->label('Outlet')
                            ->badge()
                            ->color('success'),
                        TextEntry::make('portion_qty')
                            ->label('Jumlah Porsi')
                            ->numeric()
                            ->weight('bold'),
                        TextEntry::make('creator.name')
                            ->label('Dibuat oleh'),
                        TextEntry::make('created_at')
                            ->label('Dibuat')
                            ->dateTime(DateFormat::DATE_TIME)
                            ->placeholder('-'),
                        TextEntry::make('updated_at')
                            ->label('Diperbarui')
                            ->dateTime(DateFormat::DATE_TIME)
                            ->placeholder('-'),
                    ]),
            ]);
    }
}
