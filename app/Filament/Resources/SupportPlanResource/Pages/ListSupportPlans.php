<?php

namespace App\Filament\Resources\SupportPlanResource\Pages;

use App\Filament\Resources\SupportPlanResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSupportPlans extends ListRecords
{
    use ListRecords\Concerns\Translatable;

    protected static string $resource = SupportPlanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\LocaleSwitcher::make(),
            Actions\CreateAction::make(),
        ];
    }
}
