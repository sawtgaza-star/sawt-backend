<?php

namespace App\Filament\Resources\MediaWorkResource\Pages;

use App\Filament\Resources\MediaWorkResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

/** Edit a Sawt Media portfolio work. */
class EditMediaWork extends EditRecord
{
    protected static string $resource = MediaWorkResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
