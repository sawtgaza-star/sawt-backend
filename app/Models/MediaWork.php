<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use App\Models\Concerns\PrunesStoredUploads;
use App\Support\StoredUploadCleanup;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Translatable\HasTranslations;

/**
 * Portfolio work for Sawt Media (/media/works/{slug}).
 * Translatable fields as JSON (Spatie); linked optionally to MediaServiceItem.
 */
class MediaWork extends Model
{
    use HasTranslations, HasUuid, PrunesStoredUploads;

    protected $table = 'media_works';

    /** @var list<string> */
    public array $translatable = [
        'title',
        'category',
        'tag',
        'date',
        'summary',
        'about',
        'challenges',
        'solutions',
        'client_role',
        'client_quote',
    ];

    /** @var list<string> */
    protected array $storedUploads = ['cover_image', 'client_avatar'];

    protected $fillable = [
        'media_service_id',
        'slug',
        'title',
        'category',
        'tag',
        'date',
        'summary',
        'cover_image',
        'highlights',
        'about',
        'challenges',
        'solutions',
        'stages',
        'client_name',
        'client_role',
        'client_quote',
        'client_avatar',
        'results',
        'gallery',
        'show_on_landing',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'highlights' => 'array',
            'stages' => 'array',
            'results' => 'array',
            'gallery' => 'array',
            'show_on_landing' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::updating(function (MediaWork $model): void {
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

        static::deleting(function (MediaWork $model): void {
            StoredUploadCleanup::deletePaths(StoredUploadCleanup::collectPaths($model->gallery));
        });
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(MediaServiceItem::class, 'media_service_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)->orderBy('sort_order')->orderBy('id');
    }

    /** Works shown on the media landing «أعمالنا» section. */
    public function scopeOnLanding(Builder $query): Builder
    {
        return $query->active()->where('show_on_landing', true);
    }
}
