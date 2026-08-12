<?php

namespace App\Filament\Resources\CreatorResource\Pages;

use App\Filament\Resources\CreatorResource;
use App\Services\CreatorJoinRequestService;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCreator extends EditRecord
{
    use EditRecord\Concerns\Translatable;

    protected static string $resource = CreatorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\LocaleSwitcher::make(),
            Actions\DeleteAction::make(),
        ];
    }

    protected function afterSave(): void
    {
        if ($this->record->user) {
            app(CreatorJoinRequestService::class)->promoteUserToContentCreator($this->record->user);
        }
    }
}
