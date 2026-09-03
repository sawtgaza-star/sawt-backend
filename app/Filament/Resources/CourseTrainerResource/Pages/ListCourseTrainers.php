<?php

namespace App\Filament\Resources\CourseTrainerResource\Pages;

use App\Filament\Resources\CourseTrainerResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

/** List trainers — LocaleSwitcher filters translated columns. */
class ListCourseTrainers extends ListRecords
{
    use ListRecords\Concerns\Translatable;

    protected static string $resource = CourseTrainerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\LocaleSwitcher::make(),
            Actions\CreateAction::make(),
        ];
    }
}
