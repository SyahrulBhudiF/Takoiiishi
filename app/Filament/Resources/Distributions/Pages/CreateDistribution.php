<?php

namespace App\Filament\Resources\Distributions\Pages;

use App\Filament\Resources\Distributions\DistributionResource;
use App\Models\Outlet;
use App\Services\StockService;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateDistribution extends CreateRecord
{
    protected static string $resource = DistributionResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['from_outlet_id'] = Outlet::pusat()?->id;
        $data['created_by'] = auth()->id();

        return $data;
    }

    protected function handleRecordCreation(array $data): Model
    {
        $items = $data['items'] ?? [];
        unset($data['items']);

        $record = static::getModel()::query()->create($data);
        $stock = app(StockService::class);

        foreach ($items as $item) {
            $record->items()->create($item);

            $stock->subtract(
                $record->from_outlet_id,
                $item['ingredient_id'],
                (float) $item['quantity'],
                'distribution_out',
                $record->id,
            );

            $stock->add(
                $record->to_outlet_id,
                $item['ingredient_id'],
                (float) $item['quantity'],
                'distribution_in',
                $record->id,
            );
        }

        return $record;
    }
}
