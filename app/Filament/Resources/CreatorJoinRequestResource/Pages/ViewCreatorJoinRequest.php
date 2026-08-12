<?php

namespace App\Filament\Resources\CreatorJoinRequestResource\Pages;

use App\Filament\Resources\CreatorJoinRequestResource;
use App\Services\CreatorJoinRequestService;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Validation\ValidationException;

class ViewCreatorJoinRequest extends ViewRecord
{
    protected static string $resource = CreatorJoinRequestResource::class;

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
                    try {
                        $service = app(CreatorJoinRequestService::class);
                        $service->approve($this->record, auth()->id());
                    } catch (ValidationException $e) {
                        Notification::make()
                            ->title(collect($e->errors())->flatten()->first() ?: 'تعذر قبول الطلب')
                            ->danger()
                            ->send();

                        return;
                    }

                    if ($service->lastEmailError) {
                        Notification::make()
                            ->title('تم قبول الطلب وإنشاء الحساب، لكن تعذر إرسال البريد')
                            ->body($service->lastEmailError)
                            ->warning()
                            ->send();
                        $this->redirect(CreatorJoinRequestResource::getUrl('index'));

                        return;
                    }

                    Notification::make()->title('تم قبول الطلب ونقل البيانات إلى حساب صانع المحتوى')->success()->send();
                    $this->redirect(CreatorJoinRequestResource::getUrl('index'));
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
                    $this->record->update([
                        'status' => 'rejected',
                        'admin_note' => $data['admin_note'],
                        'reviewed_by' => auth()->id(),
                        'reviewed_at' => now(),
                    ]);
                    Notification::make()->title('تم رفض الطلب')->success()->send();
                    $this->redirect(CreatorJoinRequestResource::getUrl('index'));
                }),
            Actions\DeleteAction::make()
                ->modalHeading('حذف طلب الانضمام')
                ->modalDescription('سيُحذف الطلب وحساب المستخدم وملف صانع المحتوى المرتبطين به.'),
        ];
    }
}
