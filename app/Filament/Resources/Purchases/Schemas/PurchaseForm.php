<?php

namespace App\Filament\Resources\Purchases\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class PurchaseForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                DatePicker::make('purchase_date')
                    ->label('Tanggal Pembelian')
                    ->default(now())
                    ->required(),
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
                            ->afterStateUpdated(fn ($state, callable $set, callable $get) => $set('subtotal', ((float) $state) * ((float) $get('price')))),
                        TextInput::make('price')
                            ->label('Harga')
                            ->numeric()
                            ->required()
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn ($state, callable $set, callable $get) => $set('subtotal', ((float) $state) * ((float) $get('quantity')))),
                        TextInput::make('subtotal')
                            ->label('Subtotal')
                            ->numeric()
                            ->readOnly()
                            ->default(0),
                    ])
                    ->columns(4)
                    ->minItems(1)
                    ->required(),
                TextInput::make('total')
                    ->label('Total')
                    ->numeric()
                    ->readOnly()
                    ->default(0),
            ]);
    }
}
