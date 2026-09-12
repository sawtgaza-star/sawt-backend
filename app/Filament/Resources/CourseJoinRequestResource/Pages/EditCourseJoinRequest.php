<?php

namespace App\Filament\Resources\CourseJoinRequestResource\Pages;

use App\Filament\Resources\CourseJoinRequestResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

/** Edit contact fields / admin notes on a course join request. */
class EditCourseJoinRequest extends EditRecord
{
    protected static string $resource = CourseJoinRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
