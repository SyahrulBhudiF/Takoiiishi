<?php

namespace App\Filament\Resources\StockMutations\Pages;

use App\Filament\Resources\StockMutations\StockMutationResource;
use App\Services\StockMutationService;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use InvalidArgumentException;

class EditStockMutation extends EditRecord
{
    protected static string $resource = StockMutationResource::class;

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
                ->visible(fn (): bool => auth()->user()?->can('Delete:StockMutation') && app(StockMutationService::class)->canModify($this->record))
                ->action(function (array $data): void {
                    app(StockMutationService::class)->cancel($this->record, $data['cancel_reason'] ?? null);
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

        try {
            return app(StockMutationService::class)->update($record, $data, $items);
        } catch (InvalidArgumentException | QueryException $exception) {
            Notification::make()
                ->title('Mutasi stok gagal')
                ->body($exception instanceof InvalidArgumentException ? $exception->getMessage() : 'Stok outlet asal tidak cukup.')
                ->danger()
                ->send();

            $this->halt();
        }
    }

    public function mount(int | string $record): void
    {
        parent::mount($record);

        abort_unless(app(StockMutationService::class)->canModify($this->record), 404);
    }
}
