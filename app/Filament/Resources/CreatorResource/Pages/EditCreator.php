<?php

namespace App\Filament\Resources\CreatorResource\Pages;

use App\Filament\Resources\CreatorResource;
use App\Filament\Resources\CreatorResource\Concerns\ProvisionsCreatorUser;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCreator extends EditRecord
{
    use EditRecord\Concerns\Translatable;
    use ProvisionsCreatorUser;

    protected static string $resource = CreatorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\LocaleSwitcher::make(),
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        return $this->fillCreatorVirtualFields($data);
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        return $this->mutateCreatorFormData($data);
    }

    protected function afterSave(): void
    {
        $this->afterCreatorSaved();
    }
}
