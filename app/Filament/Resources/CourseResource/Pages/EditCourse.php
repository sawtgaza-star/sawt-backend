<?php

namespace App\Filament\Resources\CourseResource\Pages;

use App\Filament\Resources\CourseResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

/** Edit course — normalize repeater/text lists + keep delivery_mode offline. */
class EditCourse extends EditRecord
{
    protected static string $resource = CourseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['delivery_mode'] = 'offline';
        $data['requirements'] = $this->normalizeTextList($data['requirements'] ?? null);
        $data['outcomes_before'] = $this->normalizeTextList($data['outcomes_before'] ?? null);
        $data['outcomes_after'] = $this->normalizeTextList($data['outcomes_after'] ?? null);
        $data['benefits'] = $this->normalizeTextList($data['benefits'] ?? null);

        return $data;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['delivery_mode'] = 'offline';

        return $data;
    }

    /**
     * @return list<array{ar: string, en: string}>
     */
    protected function normalizeTextList(mixed $items): array
    {
        if (! is_array($items)) {
            return [];
        }

        return collect(array_values($items))
            ->map(function ($item) {
                if (is_string($item)) {
                    return ['ar' => $item, 'en' => ''];
                }
                if (! is_array($item)) {
                    return null;
                }

                return [
                    'ar' => (string) ($item['ar'] ?? ''),
                    'en' => (string) ($item['en'] ?? ''),
                ];
            })
            ->filter()
            ->values()
            ->all();
    }
}
