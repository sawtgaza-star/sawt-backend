<?php

namespace App\Filament\Resources\MediaServiceResource\Pages;

use App\Filament\Resources\MediaServiceResource;
use Filament\Resources\Pages\CreateRecord;

/** Create a Sawt Media service (landing + detail). */
class CreateMediaService extends CreateRecord
{
    protected static string $resource = MediaServiceResource::class;
}
