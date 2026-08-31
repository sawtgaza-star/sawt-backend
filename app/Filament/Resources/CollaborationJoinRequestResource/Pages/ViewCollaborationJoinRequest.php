<?php

namespace App\Filament\Resources\CollaborationJoinRequestResource\Pages;

use App\Filament\Resources\CollaborationJoinRequestResource;
use App\Services\CollaborationJoinRequestService;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewCollaborationJoinRequest extends ViewRecord
{
    protected static string $resource = CollaborationJoinRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\Action::make('approve')
                ->label('قبول')
                ->color('success')
                ->icon('heroicon-o-check')
                ->visible(fn () => $this->record->status === 'pending')
                ->requiresConfirmation()
                ->action(function () {
                    $service = app(CollaborationJoinRequestService::class);
                    $service->approve($this->record, auth()->id());

                    if ($service->lastEmailError) {
                        Notification::make()
                            ->title('تم قبول الطلب، لكن تعذر إرسال البريد')
                            ->body($service->lastEmailError)
                            ->warning()
                            ->send();
                        $this->redirect(CollaborationJoinRequestResource::getUrl('index'));

                        return;
                    }

                    Notification::make()->title('تم قبول الطلب وإرسال البريد للجهة')->success()->send();
                    $this->redirect(CollaborationJoinRequestResource::getUrl('index'));
                }),
            Actions\Action::make('reject')
                ->label('رفض')
                ->color('danger')
                ->icon('heroicon-o-x-mark')
                ->visible(fn () => $this->record->status === 'pending')
                ->form([
                    Forms\Components\Textarea::make('admin_note')->label('سبب الرفض')->required(),
                ])
                ->action(function (array $data) {
                    app(CollaborationJoinRequestService::class)->reject(
                        $this->record,
                        auth()->id(),
                        $data['admin_note'] ?? null,
                    );
                    Notification::make()->title('تم رفض الطلب')->success()->send();
                    $this->redirect(CollaborationJoinRequestResource::getUrl('index'));
                }),
            Actions\DeleteAction::make(),
        ];
    }
}
