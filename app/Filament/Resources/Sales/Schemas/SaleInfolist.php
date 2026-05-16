<?php

namespace App\Filament\Resources\Sales\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class SaleInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('sale_date')
                    ->label('Tanggal')
                    ->date(),
                TextEntry::make('outlet.name')
                    ->label('Outlet'),
                TextEntry::make('portion_qty')
                    ->label('Jumlah Porsi')
                    ->numeric(),
                TextEntry::make('creator.name')
                    ->label('Dibuat oleh'),
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
