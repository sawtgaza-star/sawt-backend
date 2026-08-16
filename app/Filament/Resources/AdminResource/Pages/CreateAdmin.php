<?php

namespace App\Filament\Resources\AdminResource\Pages;

use App\Filament\Resources\AdminResource;
use App\Models\User;
use Filament\Resources\Pages\CreateRecord;

class CreateAdmin extends CreateRecord
{
    protected static string $resource = AdminResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['type'] = User::TYPE_ADMIN;

        return $data;
    }

    protected function afterCreate(): void
    {
        $this->record->forceFill(['type' => User::TYPE_ADMIN])->save();
        $this->record->removeRole(User::ROLE_USER);
        $this->record->removeRole(User::ROLE_CONTENT_CREATOR);
    }
}
