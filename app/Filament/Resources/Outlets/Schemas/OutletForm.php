<?php

namespace App\Filament\Resources\Outlets\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class OutletForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                Textarea::make('address')
                    ->columnSpanFull(),
                Select::make('type')
                    ->options([
                        'pusat' => 'Pusat',
                        'cabang' => 'Cabang',
                    ])
                    ->required(),
            ]);
    }
}
