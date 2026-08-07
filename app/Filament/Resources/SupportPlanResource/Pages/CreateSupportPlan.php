<?php

namespace App\Filament\Resources\SupportPlanResource\Pages;

use App\Filament\Resources\SupportPlanResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateSupportPlan extends CreateRecord
{
    use CreateRecord\Concerns\Translatable;

    protected static string $resource = SupportPlanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\LocaleSwitcher::make(),
        ];
    }
}
