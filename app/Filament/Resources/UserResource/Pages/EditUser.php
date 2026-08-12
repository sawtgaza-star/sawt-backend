<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\AdminResource;
use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    public function mount(int | string $record): void
    {
        parent::mount($record);

        if ($this->record->isAdmin()) {
            $this->redirect(AdminResource::getUrl('edit', ['record' => $this->record]));
        }
    }

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }

    protected function afterSave(): void
    {
        UserResource::ensureWebsiteUserRole($this->record);
    }

    protected function getRedirectUrl(): string
    {
        return UserResource::getUrl('index', ['activeTab' => 'website']);
    }
}
