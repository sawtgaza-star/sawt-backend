<?php

namespace App\Filament\Resources\CreatorResource\Pages;

use App\Filament\Resources\CreatorResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateCreator extends CreateRecord
{
    use CreateRecord\Concerns\Translatable;

    protected static string $resource = CreatorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\LocaleSwitcher::make(),
        ];
    }
}
