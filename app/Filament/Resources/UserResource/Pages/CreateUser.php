<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Models\User;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['type'] = User::TYPE_USER;

        return $data;
    }

    protected function afterCreate(): void
    {
        UserResource::ensureWebsiteUserRole($this->record);
    }
}
