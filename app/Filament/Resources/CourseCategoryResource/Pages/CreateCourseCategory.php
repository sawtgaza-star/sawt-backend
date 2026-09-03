<?php

namespace App\Filament\Resources\CourseCategoryResource\Pages;

use App\Filament\Resources\CourseCategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

/** Create course category — LocaleSwitcher for AR/EN name. */
class CreateCourseCategory extends CreateRecord
{
    use CreateRecord\Concerns\Translatable;

    protected static string $resource = CourseCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\LocaleSwitcher::make(),
        ];
    }
}
