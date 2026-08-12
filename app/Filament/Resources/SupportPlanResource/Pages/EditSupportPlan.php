<?php

namespace App\Filament\Resources\SupportPlanResource\Pages;

use App\Filament\Resources\SupportPlanResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSupportPlan extends EditRecord
{
    use EditRecord\Concerns\Translatable;

    protected static string $resource = SupportPlanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\LocaleSwitcher::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
