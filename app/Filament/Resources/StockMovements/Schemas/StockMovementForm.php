<?php

namespace App\Filament\Resources\StockMovements\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class StockMovementForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('outlet_id')
                    ->label('Outlet')
                    ->relationship('outlet', 'name')
                    ->required(),
                Select::make('ingredient_id')
                    ->label('Bahan')
                    ->relationship('ingredient', 'name')
                    ->required(),
                TextInput::make('type')
                    ->label('Jenis Mutasi')
                    ->required(),
                TextInput::make('qty_in')
                    ->label('Masuk')
                    ->required()
                    ->numeric()
                    ->default(0.0),
                TextInput::make('qty_out')
                    ->label('Keluar')
                    ->required()
                    ->numeric()
                    ->default(0.0),
            ]);
    }
}
