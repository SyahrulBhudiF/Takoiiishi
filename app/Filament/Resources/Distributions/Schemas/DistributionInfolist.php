<?php

namespace App\Filament\Resources\Distributions\Schemas;

use App\Support\DateFormat;
use Filament\Infolists\Components\RepeatableEntry;
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
                    ->description('Perpindahan bahan dari gudang ke outlet')
                    ->columnSpanFull()
                    ->columns(4)
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
                        TextEntry::make('status')
                            ->label('Status')
                            ->badge()
                            ->formatStateUsing(fn (string $state): string => match ($state) {
                                'completed' => 'Completed',
                                'cancelled' => 'Dibatalkan',
                                default => ucfirst($state),
                            })
                            ->color(fn (string $state): string => match ($state) {
                                'completed' => 'success',
                                'cancelled' => 'danger',
                                default => 'gray',
                            }),
                        TextEntry::make('created_at')
                            ->label('Dibuat')
                            ->dateTime(DateFormat::DATE_TIME)
                            ->placeholder('-'),
                        TextEntry::make('updated_at')
                            ->label('Diperbarui')
                            ->dateTime(DateFormat::DATE_TIME)
                            ->placeholder('-'),
                    ]),
                Section::make('Daftar Bahan Distribusi')
                    ->columnSpanFull()
                    ->schema([
                        RepeatableEntry::make('items')
                            ->label('Bahan')
                            ->schema([
                                TextEntry::make('ingredient.name')
                                    ->label('Bahan'),
                                TextEntry::make('quantity')
                                    ->label('Jumlah')
                                    ->numeric(),
                                TextEntry::make('ingredient.unit')
                                    ->label('Satuan'),
                            ])
                            ->columns(3),
                    ]),
                Section::make('Pembatalan')
                    ->columnSpanFull()
                    ->columns(3)
                    ->visible(fn ($record): bool => $record?->status === 'cancelled')
                    ->schema([
                        TextEntry::make('cancelled_at')
                            ->label('Dibatalkan pada')
                            ->dateTime(DateFormat::DATE_TIME),
                        TextEntry::make('canceller.name')
                            ->label('Dibatalkan oleh'),
                        TextEntry::make('cancel_reason')
                            ->label('Alasan')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
