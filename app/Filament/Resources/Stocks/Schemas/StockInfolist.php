<?php

namespace App\Filament\Resources\Stocks\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class StockInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('outlet.name')
                    ->label('Outlet'),
                TextEntry::make('ingredient.name')
                    ->label('Bahan'),
                TextEntry::make('quantity')
                    ->label('Stok')
                    ->numeric(),
                TextEntry::make('created_at')
                    ->label('Dibuat')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->label('Diperbarui')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
