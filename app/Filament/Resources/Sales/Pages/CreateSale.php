<?php

namespace App\Filament\Resources\Sales\Pages;

use App\Enums\UserRole;
use App\Filament\Resources\Sales\SaleResource;
use App\Models\Ingredient;
use App\Services\StockService;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateSale extends CreateRecord
{
    protected static string $resource = SaleResource::class;

    protected static bool $canCreateAnother = false;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $user = auth()->user();

        if (UserRole::parse($user->role)?->isOutletScoped()) {
            $data['outlet_id'] = $user->outlet_id;
        }

        $data['created_by'] = $user->id;

        return $data;
    }

    protected function handleRecordCreation(array $data): Model
    {
        $record = static::getModel()::query()->create($data);
        $stock = app(StockService::class);

        Ingredient::query()
            ->where('usage_per_portion', '>', 0)
            ->each(function (Ingredient $ingredient) use ($record, $stock): void {
                $stock->subtract(
                    $record->outlet_id,
                    $ingredient->id,
                    ((float) $record->portion_qty) * ((float) $ingredient->usage_per_portion),
                    'sale_out',
                    $record->id,
                );
            });

        return $record;
    }
}
