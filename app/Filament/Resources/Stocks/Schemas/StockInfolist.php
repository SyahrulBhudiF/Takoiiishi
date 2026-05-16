<?php

namespace App\Filament\Resources\Stocks\Schemas;

use App\Support\DateFormat;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class StockInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Ringkasan Stok')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('outlet.name')
                            ->label('Outlet')
                            ->badge()
                            ->color('success'),
                        TextEntry::make('ingredient.name')
                            ->label('Bahan'),
                        TextEntry::make('quantity')
                            ->label('Stok')
                            ->numeric()
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
