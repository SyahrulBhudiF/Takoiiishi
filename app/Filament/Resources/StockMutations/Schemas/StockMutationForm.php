<?php

namespace App\Filament\Resources\StockMutations\Schemas;

use App\Enums\UserRole;
use App\Models\Ingredient;
use App\Models\Stock;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class StockMutationForm
{
    private static function stockHelperText(?string $outletId, ?string $ingredientId): string
    {
        if (blank($outletId) || blank($ingredientId)) {
            return 'Pilih outlet asal dan bahan untuk melihat stok.';
        }

        $ingredient = Ingredient::query()->find($ingredientId);

        if (! $ingredient) {
            return 'Stok asal: 0';
        }

        $quantity = Stock::query()
            ->where('outlet_id', $outletId)
            ->where('ingredient_id', $ingredientId)
            ->value('quantity') ?? 0;

        return 'Stok asal: '.rtrim(rtrim(number_format((float) $quantity, 2, '.', ''), '0'), '.').' '.$ingredient->unit;
    }

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                DatePicker::make('mutation_date')
                    ->label('Tanggal Mutasi')
                    ->default(now())
                    ->required(),
                Select::make('from_outlet_id')
                    ->label('Outlet Asal')
                    ->relationship('fromOutlet', 'name', fn ($query) => $query->whereIn('type', ['pusat', 'cabang']))
                    ->default(fn () => UserRole::parse(auth()->user()?->role)?->isOutletScoped() ? auth()->user()->outlet_id : null)
                    ->disabled(fn (): bool => UserRole::parse(auth()->user()?->role)?->isOutletScoped() ?? false)
                    ->dehydrated()
                    ->required()
                    ->live(),
                Select::make('to_outlet_id')
                    ->label('Outlet Tujuan')
                    ->relationship('toOutlet', 'name', fn ($query) => $query->whereIn('type', ['pusat', 'cabang']))
                    ->required()
                    ->different('from_outlet_id')
                    ->live(),
                Repeater::make('items')
                    ->label('Item Mutasi')
                    ->relationship()
                    ->dehydrated()
                    ->saveRelationshipsUsing(null)
                    ->schema([
                        Select::make('ingredient_id')
                            ->label('Bahan')
                            ->options(fn (): array => Ingredient::query()->orderBy('name')->pluck('name', 'id')->all())
                            ->searchable()
                            ->required()
                            ->live(),
                        TextInput::make('quantity')
                            ->label('Jumlah')
                            ->helperText(fn (callable $get): string => self::stockHelperText($get('../../from_outlet_id'), $get('ingredient_id')))
                            ->numeric()
                            ->required()
                            ->minValue(0.01),
                    ])
                    ->columns(2)
                    ->columnSpanFull()
                    ->minItems(1)
                    ->required(),
            ]);
    }
}
