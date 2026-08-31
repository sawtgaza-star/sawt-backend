<?php

namespace App\Filament\Resources\CollaborationJoinRequestResource\Pages;

use App\Filament\Resources\CollaborationJoinRequestResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCollaborationJoinRequest extends EditRecord
{
    protected static string $resource = CollaborationJoinRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
