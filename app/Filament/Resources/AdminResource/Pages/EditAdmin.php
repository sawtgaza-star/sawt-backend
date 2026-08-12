<?php

namespace App\Filament\Resources\AdminResource\Pages;

use App\Filament\Resources\AdminResource;
use App\Filament\Resources\UserResource;
use App\Models\User;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAdmin extends EditRecord
{
    protected static string $resource = AdminResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['type'] = User::TYPE_ADMIN;

        return $data;
    }

    protected function afterSave(): void
    {
        $this->record->forceFill(['type' => User::TYPE_ADMIN])->save();
        $this->record->removeRole(User::ROLE_USER);
        $this->record->removeRole(User::ROLE_CONTENT_CREATOR);
    }

    protected function getRedirectUrl(): string
    {
        return UserResource::getUrl('index', ['activeTab' => 'admins']);
    }
}
