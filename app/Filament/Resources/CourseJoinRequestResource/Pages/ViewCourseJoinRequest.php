<?php

namespace App\Filament\Resources\CourseJoinRequestResource\Pages;

use App\Filament\Resources\CourseJoinRequestResource;
use App\Services\CourseJoinService;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use RuntimeException;

/** View a single course join / waitlist request with accept & reject actions. */
class ViewCourseJoinRequest extends ViewRecord
{
    protected static string $resource = CourseJoinRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\Action::make('accept')
                ->label(__('قبول'))
                ->color('success')
                ->icon('heroicon-o-check')
                ->visible(fn () => $this->record->isPending())
                ->requiresConfirmation()
                ->action(function () {
                    try {
                        app(CourseJoinService::class)->accept($this->record, auth()->user());
                    } catch (RuntimeException $e) {
                        Notification::make()->title($e->getMessage())->danger()->send();

                        return;
                    }

                    Notification::make()->title(__('تم قبول الطلب وإرسال بريد للمستخدم'))->success()->send();
                    $this->redirect(CourseJoinRequestResource::getUrl('index'));
                }),
            Actions\Action::make('reject')
                ->label(__('رفض'))
                ->color('danger')
                ->icon('heroicon-o-x-mark')
                ->visible(fn () => $this->record->isPending())
                ->form([
                    Forms\Components\Textarea::make('admin_notes')->label(__('سبب الرفض')),
                ])
                ->action(function (array $data) {
                    try {
                        app(CourseJoinService::class)->reject(
                            $this->record,
                            auth()->user(),
                            $data['admin_notes'] ?? null,
                        );
                    } catch (RuntimeException $e) {
                        Notification::make()->title($e->getMessage())->danger()->send();

                        return;
                    }

                    Notification::make()->title(__('تم رفض الطلب وإرسال بريد للمستخدم'))->success()->send();
                    $this->redirect(CourseJoinRequestResource::getUrl('index'));
                }),
            Actions\DeleteAction::make(),
        ];
    }
}
