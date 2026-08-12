<?php

namespace App\Filament\Resources\SupportMethodResource\Pages;

use App\Filament\Resources\SupportMethodResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSupportMethods extends ListRecords
{
    use ListRecords\Concerns\Translatable;

    protected static string $resource = SupportMethodResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\LocaleSwitcher::make(),
            Actions\CreateAction::make(),
        ];
    }
}
