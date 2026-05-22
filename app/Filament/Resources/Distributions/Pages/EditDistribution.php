<?php

namespace App\Filament\Resources\Distributions\Pages;

use App\Filament\Resources\Distributions\DistributionResource;
use App\Models\Distribution;
use App\Services\DistributionStockService;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditDistribution extends EditRecord
{
    protected static string $resource = DistributionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            Action::make('cancel')
                ->label('Batalkan')
                ->color('danger')
                ->requiresConfirmation()
                ->schema([
                    Textarea::make('cancel_reason')
                        ->label('Alasan pembatalan')
                        ->required(),
                ])
                ->visible(fn (): bool => auth()->user()?->can('Delete:Distribution') && app(DistributionStockService::class)->canModify($this->record))
                ->action(function (array $data): void {
                    app(DistributionStockService::class)->cancel($this->record, $data['cancel_reason'] ?? null);
                    $this->redirect($this->getResource()::getUrl('view', ['record' => $this->record]));
                }),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->record]);
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $items = $data['items'] ?? [];
        unset($data['items']);

        return app(DistributionStockService::class)->update($record, $data, $items);
    }

    public function mount(int | string $record): void
    {
        parent::mount($record);

        abort_unless(app(DistributionStockService::class)->canModify($this->record), 404);
    }
}
