<?php

namespace App\Filament\Resources\Distributions\Schemas;

use App\Support\DateFormat;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class DistributionInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Ringkasan Distribusi')
                    ->description('Perpindahan bahan dari pusat ke cabang')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('distribution_date')
                            ->label('Tanggal')
                            ->date(DateFormat::DATE)
                            ->badge()
                            ->color('warning'),
                        TextEntry::make('fromOutlet.name')
                            ->label('Dari')
                            ->badge()
                            ->color('gray'),
                        TextEntry::make('toOutlet.name')
                            ->label('Ke')
                            ->badge()
                            ->color('success'),
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
