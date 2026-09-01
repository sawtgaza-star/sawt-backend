<?php

namespace App\Models\Concerns;

use App\Support\StoredUploadCleanup;

trait PrunesStoredUploads
{
    public static function bootPrunesStoredUploads(): void
    {
        static::updating(function (self $model): void {
            foreach ($model->storedUploadAttributes() as $attribute) {
                if (! $model->isDirty($attribute)) {
                    continue;
                }

                $old = $model->getOriginal($attribute);

                if (filled($old)) {
                    StoredUploadCleanup::deletePaths([(string) $old]);
                }
            }
        });

        static::deleting(function (self $model): void {
            foreach ($model->storedUploadAttributes() as $attribute) {
                $value = $model->getAttribute($attribute);

                if (filled($value)) {
                    StoredUploadCleanup::deletePaths([(string) $value]);
                }
            }
        });
    }

    /**
     * @return list<string>
     */
    protected function storedUploadAttributes(): array
    {
        return property_exists($this, 'storedUploads') ? $this->storedUploads : [];
    }
}
