<?php

namespace App\Filament\Resources\Distributions\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class DistributionInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('distribution_date')
                    ->label('Tanggal')
                    ->date(),
                TextEntry::make('fromOutlet.name')
                    ->label('Dari'),
                TextEntry::make('toOutlet.name')
                    ->label('Ke'),
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
