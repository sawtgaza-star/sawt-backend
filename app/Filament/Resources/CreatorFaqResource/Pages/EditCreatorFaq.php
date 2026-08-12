<?php

namespace App\Filament\Resources\CreatorFaqResource\Pages;

use App\Filament\Resources\CreatorFaqResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCreatorFaq extends EditRecord
{
    use EditRecord\Concerns\Translatable;

    protected static string $resource = CreatorFaqResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\LocaleSwitcher::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
