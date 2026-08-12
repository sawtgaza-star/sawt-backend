<?php

namespace App\Filament\Resources\CreatorFaqResource\Pages;

use App\Filament\Resources\CreatorFaqResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCreatorFaqs extends ListRecords
{
    use ListRecords\Concerns\Translatable;

    protected static string $resource = CreatorFaqResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\LocaleSwitcher::make(),
            Actions\CreateAction::make(),
        ];
    }
}
