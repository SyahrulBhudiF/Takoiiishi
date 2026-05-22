<?php

namespace App\Filament\Resources\Sales\Schemas;

use App\Enums\UserRole;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class SaleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                DatePicker::make('sale_date')
                    ->label('Tanggal Penjualan')
                    ->default(now())
                    ->required(),
                Select::make('outlet_id')
                    ->label('Outlet')
                    ->relationship('outlet', 'name', fn ($query) => $query->whereIn('type', ['pusat', 'cabang']))
                    ->default(fn () => UserRole::parse(auth()->user()?->role)?->isOutletScoped() ? auth()->user()->outlet_id : null)
                    ->disabled(fn (): bool => UserRole::parse(auth()->user()?->role)?->isOutletScoped() ?? false)
                    ->dehydrated()
                    ->required(),
                TextInput::make('portion_qty')
                    ->label('Jumlah Porsi')
                    ->required()
                    ->numeric()
                    ->minValue(1),
            ]);
    }
}
