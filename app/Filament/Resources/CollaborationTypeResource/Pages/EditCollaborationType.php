<?php

namespace App\Filament\Resources\CollaborationTypeResource\Pages;

use App\Filament\Resources\CollaborationTypeResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCollaborationType extends EditRecord
{
    use EditRecord\Concerns\Translatable;

    protected static string $resource = CollaborationTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\LocaleSwitcher::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
