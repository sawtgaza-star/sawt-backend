<?php

namespace App\Filament\Resources\CourseSubscribeRequestResource\Pages;

use App\Filament\Resources\CourseSubscribeRequestResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

/** Edit contact / application fields and admin notes on a subscribe request. */
class EditCourseSubscribeRequest extends EditRecord
{
    protected static string $resource = CourseSubscribeRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
