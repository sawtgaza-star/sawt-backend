<?php

namespace App\Filament\Resources\SupportMethodResource\Pages;

use App\Filament\Resources\SupportMethodResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateSupportMethod extends CreateRecord
{
    use CreateRecord\Concerns\Translatable;

    protected static string $resource = SupportMethodResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\LocaleSwitcher::make(),
        ];
    }
}
