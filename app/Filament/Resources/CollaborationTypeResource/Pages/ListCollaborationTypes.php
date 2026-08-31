<?php

namespace App\Filament\Resources\CollaborationTypeResource\Pages;

use App\Filament\Resources\CollaborationTypeResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCollaborationTypes extends ListRecords
{
    use ListRecords\Concerns\Translatable;

    protected static string $resource = CollaborationTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\LocaleSwitcher::make(),
            Actions\CreateAction::make(),
        ];
    }
}
