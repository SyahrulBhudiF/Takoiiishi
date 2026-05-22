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
                    ->label('Nama Outlet')
                    ->required(),
                Textarea::make('address')
                    ->label('Alamat')
                    ->columnSpanFull(),
                Select::make('type')
                    ->label('Tipe')
                    ->options([
                        'gudang' => 'Gudang',
                        'pusat' => 'Pusat',
                        'cabang' => 'Cabang',
                    ])
                    ->required(),
            ]);
    }
}
