<?php

namespace App\Filament\Resources\CourseCategoryResource\Pages;

use App\Filament\Resources\CourseCategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

/** Edit course category — LocaleSwitcher for AR/EN name. */
class EditCourseCategory extends EditRecord
{
    use EditRecord\Concerns\Translatable;

    protected static string $resource = CourseCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\LocaleSwitcher::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
