<?php

namespace App\Filament\Resources\SupportSubscriptionResource\Pages;

use App\Filament\Resources\SupportSubscriptionResource;
use App\Models\SupportSubscription;
use App\Services\SupportSubscriptionService;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Throwable;

class ViewSupportSubscription extends ViewRecord
{
    protected static string $resource = SupportSubscriptionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('sync')
                ->label(__('مزامنة مع PayPal'))
                ->icon('heroicon-o-arrow-path')
                ->color('info')
                ->visible(fn () => filled($this->getRecord()->gateway_subscription_id))
                ->action(function (): void {
                    /** @var SupportSubscription $record */
                    $record = $this->getRecord();

                    try {
                        app(SupportSubscriptionService::class)->activate($record);
                    } catch (Throwable $e) {
                        Notification::make()->title(__('تعذّرت المزامنة'))->body($e->getMessage())->danger()->send();

                        return;
                    }

                    Notification::make()->title(__('تمت مزامنة حالة الاشتراك'))->success()->send();
                    $this->refreshFormData([]);
                }),

            Actions\Action::make('cancel')
                ->label(__('إلغاء الاشتراك'))
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->requiresConfirmation()
                ->visible(fn () => $this->getRecord()->isCancellable())
                ->action(function (): void {
                    /** @var SupportSubscription $record */
                    $record = $this->getRecord();

                    try {
                        app(SupportSubscriptionService::class)->cancel($record, 'Cancelled by admin');
                    } catch (Throwable $e) {
                        Notification::make()->title(__('تعذّر الإلغاء'))->body($e->getMessage())->danger()->send();

                        return;
                    }

                    Notification::make()->title(__('تم إلغاء الاشتراك'))->warning()->send();
                    $this->refreshFormData([]);
                }),
        ];
    }
}
