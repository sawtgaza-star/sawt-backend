<?php

namespace App\Filament\Resources\CreatorPartnerCompanyResource\Pages;

use App\Filament\Resources\CreatorPartnerCompanyResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateCreatorPartnerCompany extends CreateRecord
{
    use CreateRecord\Concerns\Translatable;

    protected static string $resource = CreatorPartnerCompanyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\LocaleSwitcher::make(),
        ];
    }
}
