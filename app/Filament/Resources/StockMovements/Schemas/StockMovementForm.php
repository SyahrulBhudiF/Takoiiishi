<?php

namespace App\Filament\Resources\StockMovements\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class StockMovementForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('outlet_id')
                    ->required(),
                TextInput::make('ingredient_id')
                    ->required(),
                TextInput::make('type')
                    ->required(),
                TextInput::make('qty_in')
                    ->required()
                    ->numeric()
                    ->default(0.0),
                TextInput::make('qty_out')
                    ->required()
                    ->numeric()
                    ->default(0.0),
                TextInput::make('reference'),
            ]);
    }
}
