<?php

namespace App\Filament\Resources\AboutPageResource\Pages;

use App\Filament\Resources\AboutPageResource;
use App\Models\AboutPage;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAboutPages extends ListRecords
{
    use ListRecords\Concerns\Translatable;

    protected static string $resource = AboutPageResource::class;

    public function mount(): void
    {
        $page = AboutPage::current();

        $this->redirect(AboutPageResource::getUrl('edit', ['record' => $page]));
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\LocaleSwitcher::make(),
        ];
    }
}
