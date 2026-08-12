<?php

namespace App\Filament\Resources\CreatorResource\Pages;

use App\Filament\Resources\CreatorResource;
use App\Services\CreatorJoinRequestService;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateCreator extends CreateRecord
{
    use CreateRecord\Concerns\Translatable;

    protected static string $resource = CreatorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\LocaleSwitcher::make(),
        ];
    }

    protected function afterCreate(): void
    {
        if ($this->record->user) {
            app(CreatorJoinRequestService::class)->promoteUserToContentCreator($this->record->user);
        }
    }
}
