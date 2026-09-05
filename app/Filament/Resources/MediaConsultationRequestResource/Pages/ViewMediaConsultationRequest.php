<?php

namespace App\Filament\Resources\MediaConsultationRequestResource\Pages;

use App\Filament\Resources\MediaConsultationRequestResource;
use App\Services\MediaConsultationRequestService;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Pages\ViewRecord;

/** View a consultation request; header actions approve/reject + email. */
class ViewMediaConsultationRequest extends ViewRecord
{
    protected static string $resource = MediaConsultationRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('approve')
                ->label(__('Accept'))
                ->color('success')
                ->icon('heroicon-o-check')
                ->visible(fn () => $this->record->status === 'pending')
                ->requiresConfirmation()
                ->modalHeading(__('Accept consultation request'))
                ->modalDescription(__('An acceptance email will be sent to the applicant.'))
                ->action(function () {
                    $service = app(MediaConsultationRequestService::class);
                    $service->approve($this->record, auth()->id());
                    MediaConsultationRequestResource::notifyDecisionResult($service, approved: true);
                    $this->redirect(MediaConsultationRequestResource::getUrl('index'));
                }),
            Actions\Action::make('reject')
                ->label(__('Reject'))
                ->color('danger')
                ->icon('heroicon-o-x-mark')
                ->visible(fn () => $this->record->status === 'pending')
                ->form([
                    Forms\Components\Textarea::make('admin_note')
                        ->label(__('Rejection reason'))
                        ->required()
                        ->rows(3),
                ])
                ->action(function (array $data) {
                    $service = app(MediaConsultationRequestService::class);
                    $service->reject($this->record, auth()->id(), $data['admin_note'] ?? null);
                    MediaConsultationRequestResource::notifyDecisionResult($service, approved: false);
                    $this->redirect(MediaConsultationRequestResource::getUrl('index'));
                }),
            Actions\DeleteAction::make(),
        ];
    }
}
