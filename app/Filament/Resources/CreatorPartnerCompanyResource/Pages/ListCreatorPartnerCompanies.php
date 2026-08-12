<?php

namespace App\Filament\Resources\CreatorPartnerCompanyResource\Pages;

use App\Filament\Resources\CreatorPartnerCompanyResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCreatorPartnerCompanies extends ListRecords
{
    use ListRecords\Concerns\Translatable;

    protected static string $resource = CreatorPartnerCompanyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\LocaleSwitcher::make(),
            Actions\CreateAction::make(),
        ];
    }
}
