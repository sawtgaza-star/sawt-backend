<?php

namespace App\Filament\Resources\CreatorJoinRequestResource\Pages;

use App\Filament\Resources\CreatorJoinRequestResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCreatorJoinRequest extends EditRecord
{
    protected static string $resource = CreatorJoinRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make()
                ->modalHeading('حذف طلب الانضمام')
                ->modalDescription('سيُحذف الطلب وحساب المستخدم وملف صانع المحتوى المرتبطين به.'),
        ];
    }
}
