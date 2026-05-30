<?php

namespace App\Filament\Resources\StockMovements\Schemas;

use App\Support\DateFormat;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class StockMovementInfolist
{
    private const TYPE_LABELS = [
        'purchase_in' => 'Pembelian',
        'distribution_out' => 'Distribusi Keluar',
        'distribution_in' => 'Distribusi Masuk',
        'distribution_reverse_in' => 'Batal Masuk',
        'distribution_reverse_out' => 'Batal Keluar',
        'mutation_out' => 'Mutasi Keluar',
        'mutation_in' => 'Mutasi Masuk',
        'mutation_reverse_in' => 'Batal Mutasi Masuk',
        'mutation_reverse_out' => 'Batal Mutasi Keluar',
        'sale_out' => 'Penjualan',
    ];

    private const TYPE_COLORS = [
        'purchase_in' => 'success',
        'distribution_out' => 'warning',
        'distribution_in' => 'info',
        'distribution_reverse_in' => 'gray',
        'distribution_reverse_out' => 'gray',
        'mutation_out' => 'warning',
        'mutation_in' => 'info',
        'mutation_reverse_in' => 'gray',
        'mutation_reverse_out' => 'gray',
        'sale_out' => 'danger',
    ];

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
                            ->label('Jenis Pergerakan')
                            ->formatStateUsing(fn (string $state): string => self::TYPE_LABELS[$state] ?? ucfirst(str_replace('_', ' ', $state)))
                            ->badge()
                            ->color(fn (string $state): string => self::TYPE_COLORS[$state] ?? 'gray'),
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
