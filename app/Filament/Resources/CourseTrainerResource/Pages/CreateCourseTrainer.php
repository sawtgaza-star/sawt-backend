<?php

namespace App\Filament\Resources\CourseTrainerResource\Pages;

use App\Filament\Resources\CourseTrainerResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

/** Create trainer — LocaleSwitcher for AR/EN name/title/bio. */
class CreateCourseTrainer extends CreateRecord
{
    use CreateRecord\Concerns\Translatable;

    protected static string $resource = CourseTrainerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\LocaleSwitcher::make(),
        ];
    }
}
