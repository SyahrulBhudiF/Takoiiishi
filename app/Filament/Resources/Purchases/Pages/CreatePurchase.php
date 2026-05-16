<?php

namespace App\Filament\Resources\Purchases\Pages;

use App\Filament\Resources\Purchases\PurchaseResource;
use App\Models\Outlet;
use App\Services\StockService;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreatePurchase extends CreateRecord
{
    protected static string $resource = PurchaseResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = auth()->id();
        $data['total'] = collect($data['items'] ?? [])->sum(fn (array $item): float => ((float) $item['quantity']) * ((float) $item['price']));

        return $data;
    }

    protected function handleRecordCreation(array $data): Model
    {
        $items = $data['items'] ?? [];
        unset($data['items']);

        $record = static::getModel()::query()->create($data);
        $pusat = Outlet::pusat();

        foreach ($items as $item) {
            $item['subtotal'] = ((float) $item['quantity']) * ((float) $item['price']);
            $record->items()->create($item);

            app(StockService::class)->add(
                $pusat->id,
                $item['ingredient_id'],
                (float) $item['quantity'],
                'purchase_in',
                $record->id,
            );
        }

        return $record;
    }
}
