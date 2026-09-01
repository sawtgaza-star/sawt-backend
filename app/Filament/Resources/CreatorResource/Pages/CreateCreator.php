<?php

namespace App\Filament\Resources\CreatorResource\Pages;

use App\Filament\Resources\CreatorResource;
use App\Filament\Resources\CreatorResource\Concerns\ProvisionsCreatorUser;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateCreator extends CreateRecord
{
    use CreateRecord\Concerns\Translatable;
    use ProvisionsCreatorUser;

    protected static string $resource = CreatorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\LocaleSwitcher::make(),
        ];
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return $this->mutateCreatorFormData($data);
    }

    protected function afterCreate(): void
    {
        $this->afterCreatorSaved();
    }
}
