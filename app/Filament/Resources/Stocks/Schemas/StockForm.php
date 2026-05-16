<?php

namespace App\Filament\Resources\Stocks\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class StockForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('outlet_id')
                    ->relationship('outlet', 'name')
                    ->required(),
                Select::make('ingredient_id')
                    ->relationship('ingredient', 'name')
                    ->required(),
                TextInput::make('quantity')
                    ->required()
                    ->numeric()
                    ->default(0.0),
            ]);
    }
}
