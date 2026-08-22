<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

class Blog extends Model
{
    use HasTranslations, HasUuid, SoftDeletes;

    public array $translatable = [
        'title',
        'excerpt',
        'content',
        'quote_text',
        'quote_author',
        'author_name',
    ];

    protected $fillable = [
        'title',
        'slug',
        'excerpt',
        'cover_image',
        'hero_image',
        'content',
        'quote_text',
        'quote_author',
        'categories',
        'author_name',
        'read_time_minutes',
        'views_count',
        'status',
        'is_featured',
        'sort_order',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'categories' => 'array',
            'is_featured' => 'boolean',
            'published_at' => 'datetime',
        ];
    }

    public function images(): HasMany
    {
        return $this->hasMany(BlogImage::class)->orderBy('sort_order');
    }

    public function scopePublished($query)
    {
        return $query
            ->where('status', 'published')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeWithCategorySlug($query, string $slug)
    {
        return $query->whereRaw(
            "JSON_SEARCH(categories, 'one', ?, NULL, '$[*].slug') IS NOT NULL",
            [$slug]
        );
    }

    public function heroImagePath(): ?string
    {
        return $this->hero_image ?: $this->cover_image;
    }

    /**
     * @return array<int, array{slug: string|null, name: array{ar: string, en: string}}>
     */
    public function formattedCategories(): array
    {
        $categories = $this->categories;

        if (is_string($categories)) {
            $categories = json_decode($categories, true);
        }

        return collect(is_array($categories) ? $categories : [])
            ->map(fn (array $category) => [
                'slug' => filled($category['slug'] ?? null) ? (string) $category['slug'] : null,
                'name' => [
                    'ar' => (string) ($category['name_ar'] ?? ''),
                    'en' => (string) ($category['name_en'] ?? ''),
                ],
            ])
            ->filter(fn (array $category) => filled($category['slug']) || filled($category['name']['ar']) || filled($category['name']['en']))
            ->values()
            ->all();
    }

    /**
     * @return array<int, string>
     */
    public function categorySlugs(): array
    {
        $categories = $this->categories;

        if (is_string($categories)) {
            $categories = json_decode($categories, true);
        }

        return collect(is_array($categories) ? $categories : [])
            ->pluck('slug')
            ->filter()
            ->values()
            ->all();
    }

    public function readTimeMinutes(): int
    {
        $stored = (int) ($this->attributes['read_time_minutes'] ?? 0);

        if ($stored > 0) {
            return $stored;
        }

        return $this->calculateReadTimeMinutes();
    }

    public function calculateReadTimeMinutes(): int
    {
        $text = collect([
            $this->getTranslation('excerpt', 'ar'),
            $this->getTranslation('excerpt', 'en'),
            strip_tags((string) $this->getTranslation('content', 'ar')),
            strip_tags((string) $this->getTranslation('content', 'en')),
            $this->getTranslation('quote_text', 'ar'),
            $this->getTranslation('quote_text', 'en'),
        ])->filter()->implode(' ');

        $words = preg_match_all('/[\p{L}\p{N}]+/u', $text, $matches) ?: 0;

        return max(1, (int) ceil($words / 200));
    }

    protected static function booted(): void
    {
        static::saving(function (Blog $blog): void {
            if (! filled($blog->read_time_minutes) || (int) $blog->read_time_minutes <= 0) {
                $blog->read_time_minutes = $blog->calculateReadTimeMinutes();
            }
        });
    }
}
