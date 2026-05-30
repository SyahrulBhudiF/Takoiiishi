<?php

namespace App\Filament\Resources\StockMovements\Tables;

use App\Support\DateFormat;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class StockMovementsTable
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

    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('outlet.name')
                    ->label('Outlet')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('ingredient.name')
                    ->label('Bahan')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('type')
                    ->label('Jenis Pergerakan')
                    ->formatStateUsing(fn (string $state): string => self::TYPE_LABELS[$state] ?? ucfirst(str_replace('_', ' ', $state)))
                    ->badge()
                    ->color(fn (string $state): string => self::TYPE_COLORS[$state] ?? 'gray')
                    ->searchable(),
                TextColumn::make('qty_in')
                    ->label('Masuk')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('qty_out')
                    ->label('Keluar')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Tanggal')
                    ->dateTime(DateFormat::DATE_TIME)
                    ->sortable(),
                TextColumn::make('updated_at')
                    ->label('Diperbarui')
                    ->dateTime(DateFormat::DATE_TIME)
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),

            ])
            ->toolbarActions([]);
    }
}
