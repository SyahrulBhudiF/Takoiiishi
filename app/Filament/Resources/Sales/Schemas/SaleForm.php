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
                    ->default(now())
                    ->required(),
                Select::make('outlet_id')
                    ->relationship('outlet', 'name', fn ($query) => $query->where('type', 'cabang'))
                    ->default(fn () => UserRole::tryFrom(auth()->user()?->role?->value ?? auth()->user()?->role)?->isBranchScoped() ? auth()->user()->outlet_id : null)
                    ->disabled(fn (): bool => UserRole::tryFrom(auth()->user()?->role?->value ?? auth()->user()?->role)?->isBranchScoped() ?? false)
                    ->dehydrated()
                    ->required(),
                TextInput::make('portion_qty')
                    ->required()
                    ->numeric()
                    ->minValue(1),
            ]);
    }
}
