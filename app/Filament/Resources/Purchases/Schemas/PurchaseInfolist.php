<?php

namespace App\Filament\Resources\Purchases\Schemas;

use App\Support\DateFormat;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PurchaseInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Ringkasan Pembelian')
                    ->columnSpanFull()
                    ->columns(4)
                    ->schema([
                        TextEntry::make('purchase_date')
                            ->label('Tanggal')
                            ->date(DateFormat::DATE)
                            ->badge()
                            ->color('warning'),
                        TextEntry::make('creator.name')
                            ->label('Dibuat oleh'),
                        TextEntry::make('total')
                            ->label('Total')
                            ->money('IDR')
                            ->weight('bold'),
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
