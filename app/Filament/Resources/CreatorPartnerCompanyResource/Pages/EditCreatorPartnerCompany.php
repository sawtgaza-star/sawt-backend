<?php

namespace App\Filament\Resources\CreatorPartnerCompanyResource\Pages;

use App\Filament\Resources\CreatorPartnerCompanyResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCreatorPartnerCompany extends EditRecord
{
    use EditRecord\Concerns\Translatable;

    protected static string $resource = CreatorPartnerCompanyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\LocaleSwitcher::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
