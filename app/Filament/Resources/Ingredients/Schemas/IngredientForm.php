<?php

namespace App\Filament\Resources\Ingredients\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class IngredientForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nama Bahan')
                    ->required(),
                TextInput::make('unit')
                    ->label('Satuan')
                    ->required(),
                TextInput::make('minimum_stock')
                    ->label('Stok Minimum')
                    ->required()
                    ->numeric()
                    ->default(0.0),
                TextInput::make('usage_per_portion')
                    ->label('Pemakaian per Porsi')
                    ->required()
                    ->numeric()
                    ->default(0.0),
            ]);
    }
}
