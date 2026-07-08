<?php

namespace App\Filament\Resources\CreatorApplicationResource\Pages;

use App\Filament\Resources\CreatorApplicationResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCreatorApplication extends EditRecord
{
    protected static string $resource = CreatorApplicationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
