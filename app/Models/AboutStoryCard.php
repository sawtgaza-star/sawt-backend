<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use App\Support\MediaUrl;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Translatable\HasTranslations;

class AboutStoryCard extends Model
{
    use HasTranslations, HasUuid;

    public array $translatable = ['title', 'description'];

    protected $fillable = [
        'about_page_id',
        'icon',
        'title',
        'description',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function aboutPage(): BelongsTo
    {
        return $this->belongsTo(AboutPage::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function getIconUrlAttribute(): ?string
    {
        return MediaUrl::make($this->icon);
    }
}
