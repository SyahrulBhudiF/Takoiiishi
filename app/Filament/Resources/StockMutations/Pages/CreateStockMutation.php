<?php

namespace App\Filament\Resources\StockMutations\Pages;

use App\Enums\UserRole;
use App\Filament\Resources\StockMutations\StockMutationResource;
use App\Services\StockMutationService;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use InvalidArgumentException;

class CreateStockMutation extends CreateRecord
{
    protected static string $resource = StockMutationResource::class;

    protected static bool $canCreateAnother = false;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $user = auth()->user();

        if (UserRole::parse($user?->role)?->isOutletScoped()) {
            $data['from_outlet_id'] = $user->outlet_id;
        }

        $data['created_by'] = $user?->id;

        return $data;
    }

    protected function handleRecordCreation(array $data): Model
    {
        $items = $data['items'] ?? [];
        unset($data['items']);

        try {
            return app(StockMutationService::class)->create($data, $items);
        } catch (InvalidArgumentException | QueryException $exception) {
            Notification::make()
                ->title('Mutasi stok gagal')
                ->body($exception instanceof InvalidArgumentException ? $exception->getMessage() : 'Stok outlet asal tidak cukup.')
                ->danger()
                ->send();

            $this->halt();
        }
    }
}
