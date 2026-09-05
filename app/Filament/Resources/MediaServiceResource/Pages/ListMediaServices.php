<?php

namespace App\Filament\Resources\MediaServiceResource\Pages;

use App\Filament\Resources\MediaServiceResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

/** Filament index for Sawt Media services. */
class ListMediaServices extends ListRecords
{
    protected static string $resource = MediaServiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
