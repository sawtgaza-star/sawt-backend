<?php

namespace App\Filament\Resources\CreatorApplicationResource\Pages;

use App\Filament\Resources\CreatorApplicationResource;
use Filament\Resources\Pages\ListRecords;

class ListCreatorApplications extends ListRecords
{
    protected static string $resource = CreatorApplicationResource::class;

    // ما في CreateAction — الطلبات تجي من فورم عام بالموقع، الأدمن بس يراجع
    protected function getHeaderActions(): array
    {
        return [];
    }
}
