<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use App\Models\Concerns\PrunesStoredUploads;
use App\Support\StoredUploadCleanup;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Translatable\HasTranslations;

/**
 * One Sawt Media service (landing card + /media/services/{slug} detail).
 * Translatable fields stored as JSON (Spatie) — same pattern as Course.
 */
class MediaServiceItem extends Model
{
    use HasTranslations, HasUuid, PrunesStoredUploads;

    protected $table = 'media_services';

    /** @var list<string> */
    public array $translatable = ['title', 'tagline', 'description', 'tags', 'includes'];

    /** Cover card image only — gallery is a JSON list (cleaned via model events). */
    protected array $storedUploads = ['image'];

    protected $fillable = [
        'slug',
        'number',
        'title',
        'tagline',
        'description',
        'tags',
        'image',
        'gallery',
        'includes',
        'samples',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'gallery' => 'array',
            'samples' => 'array',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        // Drop removed gallery files when the list changes or the service is deleted
        static::updating(function (MediaServiceItem $model): void {
            if (! $model->isDirty('gallery')) {
                return;
            }

            $old = StoredUploadCleanup::collectPaths($model->getOriginal('gallery'));
            $new = StoredUploadCleanup::collectPaths($model->gallery);
            $removed = array_values(array_diff($old, $new));
            if ($removed !== []) {
                StoredUploadCleanup::deletePaths($removed);
            }
        });

        static::deleting(function (MediaServiceItem $model): void {
            StoredUploadCleanup::deletePaths(StoredUploadCleanup::collectPaths($model->gallery));
        });
    }

    /** Active services for public APIs, ordered for landing. */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)->orderBy('sort_order')->orderBy('id');
    }

    /** Portfolio works linked as «نماذج من أعمالنا» on this service. */
    public function works(): HasMany
    {
        return $this->hasMany(MediaWork::class, 'media_service_id')->orderBy('sort_order');
    }
}
