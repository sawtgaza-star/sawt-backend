<?php

namespace App\Filament\Resources\CourseTrainerResource\Pages;

use App\Filament\Resources\CourseTrainerResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

/** Edit trainer — LocaleSwitcher for AR/EN fields. */
class EditCourseTrainer extends EditRecord
{
    use EditRecord\Concerns\Translatable;

    protected static string $resource = CourseTrainerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\LocaleSwitcher::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
