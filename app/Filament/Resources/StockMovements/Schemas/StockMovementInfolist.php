<?php

namespace App\Filament\Resources\StockMovements\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class StockMovementInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('outlet.name')
                    ->label('Outlet'),
                TextEntry::make('ingredient.name')
                    ->label('Bahan'),
                TextEntry::make('type')
                    ->label('Jenis Mutasi'),
                TextEntry::make('qty_in')
                    ->label('Masuk')
                    ->numeric(),
                TextEntry::make('qty_out')
                    ->label('Keluar')
                    ->numeric(),
                TextEntry::make('created_at')
                    ->label('Tanggal')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->label('Diperbarui')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
