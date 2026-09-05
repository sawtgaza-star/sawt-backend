<?php

namespace App\Filament\Resources\MediaServiceResource\Pages;

use App\Filament\Resources\MediaServiceResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

/** Edit a Sawt Media service (landing + detail). */
class EditMediaService extends EditRecord
{
    protected static string $resource = MediaServiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
