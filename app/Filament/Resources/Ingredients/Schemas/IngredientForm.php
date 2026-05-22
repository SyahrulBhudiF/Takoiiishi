<?php

namespace App\Filament\Resources\Ingredients\Schemas;

use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class IngredientForm
{
    private static function convertUsagePerPortion(mixed $quantity, ?string $unit): float
    {
        $quantity = (float) $quantity;

        return match ($unit) {
            'g', 'ml' => $quantity / 1000,
            default => $quantity,
        };
    }

    private static function defaultUsageUnit(?string $stockUnit): ?string
    {
        return match ($stockUnit) {
            'kg' => 'g',
            'liter' => 'ml',
            default => $stockUnit,
        };
    }

    private static function usagePreview(mixed $quantity, ?string $usageUnit, ?string $stockUnit): ?string
    {
        if (blank($quantity) || blank($usageUnit) || blank($stockUnit)) {
            return 'Pilih satuan stok, lalu isi pemakaian per porsi.';
        }

        $converted = self::convertUsagePerPortion($quantity, $usageUnit);
        $formatted = rtrim(rtrim(number_format($converted, 4, '.', ''), '0'), '.');

        return "{$formatted} {$stockUnit} per porsi";
    }

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nama Bahan')
                    ->required(),
                Select::make('unit')
                    ->label('Satuan')
                    ->options([
                        'kg' => 'Kilogram (kg)',
                        'g' => 'Gram (g)',
                        'liter' => 'Liter',
                        'ml' => 'Mililiter (ml)',
                        'pcs' => 'Pieces (pcs)',
                        'pack' => 'Pack',
                        'box' => 'Box',
                    ])
                    ->required()
                    ->native(false)
                    ->searchable()
                    ->live()
                    ->afterStateUpdated(fn ($state, callable $set, callable $get) => $set('usage_per_portion', self::convertUsagePerPortion($get('usage_per_portion_input'), self::defaultUsageUnit($state)))),
                TextInput::make('minimum_stock')
                    ->label('Stok Minimum')
                    ->required()
                    ->numeric()
                    ->default(0.0),
                TextInput::make('usage_per_portion_input')
                    ->label('Pemakaian per Porsi')
                    ->numeric()
                    ->type('number')
                    ->inputMode('decimal')
                    ->step('any')
                    ->minValue(0)
                    ->required()
                    ->live(onBlur: true)
                    ->dehydrated(false)
                    ->afterStateHydrated(function (TextInput $component, mixed $state, callable $get, ?\App\Models\Ingredient $record): void {
                        $component->state($record?->usage_per_portion ?? $get('usage_per_portion'));
                    })
                    ->afterStateUpdated(fn ($state, callable $set, callable $get) => $set('usage_per_portion', self::convertUsagePerPortion($state, self::defaultUsageUnit($get('unit')))))
                    ->suffix(fn (callable $get): ?string => self::defaultUsageUnit($get('unit')), true)
                    ->helperText(fn (callable $get): string => self::usagePreview($get('usage_per_portion_input'), self::defaultUsageUnit($get('unit')), $get('unit'))),
                Hidden::make('usage_per_portion')
                    ->required()
                    ->default(0.0),
            ]);
    }
}
