<?php

namespace App\Filament\Resources\Purchases\Schemas;

use App\Models\Ingredient;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PurchaseForm
{
    private static function updateItemSubtotal(callable $set, callable $get): void
    {
        $set('subtotal', ((float) $get('quantity')) * ((float) $get('price')));
        self::updatePurchaseTotal($set, $get);
    }

    private static function updatePurchaseTotal(callable $set, callable $get): void
    {
        $items = $get('../../items') ?? [];
        $total = collect($items)->sum(fn (array $item): float => ((float) ($item['quantity'] ?? 0)) * ((float) ($item['price'] ?? 0)));

        $set('../../total', $total);
    }

    private static function ingredientUnit(?string $ingredientId): ?string
    {
        return filled($ingredientId) ? Ingredient::query()->find($ingredientId)?->unit : null;
    }

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->schema([
                        DatePicker::make('purchase_date')
                            ->label('Tanggal Pembelian')
                            ->default(now())
                            ->required(),

                        TextInput::make('total')
                            ->label('Total Pembelian')
                            ->prefix('Rp')
                            ->numeric()
                            ->disabled()
                            ->dehydrated()
                            ->default(0)
                            ->helperText('Otomatis dihitung dari seluruh subtotal item.'),
                    ])
                    ->columns([
                        'default' => 1,
                        'lg' => 2,
                    ])
                    ->columnSpanFull(),

                Repeater::make('items')
                    ->label('Item Pembelian')
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
                            ->suffix(fn (callable $get): ?string => self::ingredientUnit($get('ingredient_id')), true)
                            ->helperText(fn (callable $get): string => filled(self::ingredientUnit($get('ingredient_id'))) ? 'Satuan: '.self::ingredientUnit($get('ingredient_id')) : 'Pilih bahan baku untuk melihat satuan.')
                            ->numeric()
                            ->required()
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn ($state, callable $set, callable $get) => self::updateItemSubtotal($set, $get)),
                        TextInput::make('price')
                            ->label('Harga')
                            ->numeric()
                            ->required()
                            ->live(onBlur: true)
                            ->prefix('Rp')
                            ->afterStateUpdated(fn ($state, callable $set, callable $get) => self::updateItemSubtotal($set, $get)),
                        TextInput::make('subtotal')
                            ->label('Subtotal')
                            ->prefix('Rp')
                            ->numeric()
                            ->disabled()
                            ->dehydrated()
                            ->default(0),
                    ])
                    ->columns([
                        'default' => 1,
                        'md' => 2,
                        'xl' => 4,
                    ])
                    ->columnSpanFull()
                    ->minItems(1)
                    ->required(),
            ]);
    }
}
