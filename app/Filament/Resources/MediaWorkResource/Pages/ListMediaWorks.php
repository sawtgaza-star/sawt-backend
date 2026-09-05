<?php

namespace App\Filament\Resources\MediaWorkResource\Pages;

use App\Filament\Resources\MediaWorkResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

/** Filament index for Sawt Media portfolio works. */
class ListMediaWorks extends ListRecords
{
    protected static string $resource = MediaWorkResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
