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
                    ->required(),
                TextInput::make('unit')
                    ->required(),
                TextInput::make('minimum_stock')
                    ->required()
                    ->numeric()
                    ->default(0.0),
                TextInput::make('usage_per_portion')
                    ->required()
                    ->numeric()
                    ->default(0.0),
            ]);
    }
}
