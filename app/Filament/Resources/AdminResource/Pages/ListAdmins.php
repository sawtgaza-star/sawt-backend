<?php

namespace App\Filament\Resources\AdminResource\Pages;

use App\Filament\Resources\AdminResource;
use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAdmins extends ListRecords
{
    protected static string $resource = AdminResource::class;

    public function mount(): void
    {
        $this->redirect(UserResource::getUrl('index', ['activeTab' => 'admins']));
    }

    protected function getHeaderActions(): array
    {
        return [];
    }
}
