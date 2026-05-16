<?php

namespace App\Filament\Resources\Distributions\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class DistributionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                DatePicker::make('distribution_date')
                    ->default(now())
                    ->required(),
                Select::make('to_outlet_id')
                    ->relationship('toOutlet', 'name', fn ($query) => $query->where('type', 'cabang'))
                    ->label('Cabang Tujuan')
                    ->required(),
                Repeater::make('items')
                    ->relationship()
                    ->schema([
                        Select::make('ingredient_id')
                            ->relationship('ingredient', 'name')
                            ->required(),
                        TextInput::make('quantity')
                            ->numeric()
                            ->required()
                            ->minValue(0.01),
                    ])
                    ->columns(2)
                    ->minItems(1)
                    ->required(),
            ]);
    }
}
