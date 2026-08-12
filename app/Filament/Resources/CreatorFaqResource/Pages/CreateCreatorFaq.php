<?php

namespace App\Filament\Resources\CreatorFaqResource\Pages;

use App\Filament\Resources\CreatorFaqResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateCreatorFaq extends CreateRecord
{
    use CreateRecord\Concerns\Translatable;

    protected static string $resource = CreatorFaqResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\LocaleSwitcher::make(),
        ];
    }
}
