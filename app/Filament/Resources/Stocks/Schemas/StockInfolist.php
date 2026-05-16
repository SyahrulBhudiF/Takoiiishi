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
                TextEntry::make('id')
                    ->label('ID'),
                TextEntry::make('outlet_id'),
                TextEntry::make('ingredient_id'),
                TextEntry::make('quantity')
                    ->numeric(),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
