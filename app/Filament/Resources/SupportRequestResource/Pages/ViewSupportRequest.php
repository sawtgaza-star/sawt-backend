<?php

namespace App\Filament\Resources\SupportRequestResource\Pages;

use App\Filament\Resources\SupportRequestResource;
use App\Models\SupportRequest;
use App\Services\SupportRequestService;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Throwable;

class ViewSupportRequest extends ViewRecord
{
    protected static string $resource = SupportRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('approve')
                ->label('اعتماد')
                ->icon('heroicon-o-check-badge')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('اعتماد طلب الدعم')
                ->modalDescription('سيتم إنشاء تبرع موثّق بهذا المبلغ وإضافته لرصيد الحملة إن وُجدت.')
                ->form([
                    Forms\Components\Textarea::make('admin_note')->label('ملاحظة إدارية (اختياري)')->rows(3),
                ])
                ->visible(fn () => in_array($this->getRecord()->status, ['pending', 'under_review'], true))
                ->action(function (array $data): void {
                    /** @var SupportRequest $record */
                    $record = $this->getRecord();

                    try {
                        app(SupportRequestService::class)->approve($record, auth()->user(), $data['admin_note'] ?? null);
                    } catch (Throwable $e) {
                        Notification::make()->title('تعذّر اعتماد الطلب')->body($e->getMessage())->danger()->send();

                        return;
                    }

                    Notification::make()->title('تم اعتماد الطلب وتوثيق التبرع')->success()->send();
                    $this->refreshFormData([]);
                }),

            Actions\Action::make('reject')
                ->label('رفض')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->form([
                    Forms\Components\Textarea::make('rejection_reason')->label('سبب الرفض')->required()->rows(3),
                ])
                ->visible(fn () => in_array($this->getRecord()->status, ['pending', 'under_review'], true))
                ->action(function (array $data): void {
                    /** @var SupportRequest $record */
                    $record = $this->getRecord();

                    app(SupportRequestService::class)->reject($record, auth()->user(), $data['rejection_reason']);

                    Notification::make()->title('تم رفض الطلب')->warning()->send();
                    $this->refreshFormData([]);
                }),

            Actions\Action::make('mark_under_review')
                ->label('وضع قيد المراجعة')
                ->icon('heroicon-o-eye')
                ->color('info')
                ->visible(fn () => $this->getRecord()->status === 'pending')
                ->action(function (): void {
                    $this->getRecord()->update(['status' => 'under_review', 'reviewed_by' => auth()->id()]);

                    Notification::make()->title('الطلب الآن قيد المراجعة')->info()->send();
                    $this->refreshFormData([]);
                }),
        ];
    }
}
