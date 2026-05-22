<?php

namespace App\Filament\Resources\Purchases\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PurchaseForm
{
    private static function updateItemSubtotal(callable $set, callable $get): void
    {
        $set('subtotal', ((float) $get('quantity')) * ((float) $get('price')));
    }

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->schema([
                        DatePicker::make('purchase_date')
                            ->label('Tanggal Pembelian')
                            ->default(now())
                            ->required(),

                        TextInput::make('total')
                            ->label('Total Pembelian')
                            ->prefix('Rp')
                            ->numeric()
                            ->disabled()
                            ->dehydrated()
                            ->default(0)
                            ->helperText('Otomatis dihitung dari seluruh subtotal item.'),
                    ])
                    ->columns([
                        'default' => 1,
                        'lg' => 2,
                    ])
                    ->columnSpanFull(),

                Repeater::make('items')
                    ->label('Item Pembelian')
                    ->relationship()
                    ->schema([
                        Select::make('ingredient_id')
                            ->label('Bahan')
                            ->relationship('ingredient', 'name')
                            ->required(),
                        TextInput::make('quantity')
                            ->label('Jumlah')
                            ->numeric()
                            ->required()
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn ($state, callable $set, callable $get) => self::updateItemSubtotal($set, $get)),
                        TextInput::make('price')
                            ->label('Harga')
                            ->numeric()
                            ->required()
                            ->live(onBlur: true)
                            ->prefix('Rp')
                            ->afterStateUpdated(fn ($state, callable $set, callable $get) => self::updateItemSubtotal($set, $get)),
                        TextInput::make('subtotal')
                            ->label('Subtotal')
                            ->prefix('Rp')
                            ->numeric()
                            ->disabled()
                            ->dehydrated()
                            ->default(0),
                    ])
                    ->columns([
                        'default' => 1,
                        'md' => 2,
                        'xl' => 4,
                    ])
                    ->columnSpanFull()
                    ->minItems(1)
                    ->required(),
            ]);
    }
}
