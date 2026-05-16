<?php

namespace App\Filament\Resources\StockMovements\Schemas;

use App\Support\DateFormat;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class StockMovementInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Ringkasan Mutasi')
                    ->columnSpanFull()
                    ->columns(4)
                    ->schema([
                        TextEntry::make('outlet.name')
                            ->label('Outlet')
                            ->badge()
                            ->color('success'),
                        TextEntry::make('ingredient.name')
                            ->label('Bahan'),
                        TextEntry::make('type')
                            ->label('Jenis Mutasi')
                            ->badge()
                            ->color('warning'),
                        TextEntry::make('qty_in')
                            ->label('Masuk')
                            ->numeric(),
                        TextEntry::make('qty_out')
                            ->label('Keluar')
                            ->numeric(),
                        TextEntry::make('created_at')
                            ->label('Tanggal')
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
