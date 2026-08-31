<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use App\Support\MediaUrl;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class CollaborationType extends Model
{
    use HasTranslations, HasUuid;

    public array $translatable = ['title', 'description'];

    protected $fillable = [
        'key',
        'title',
        'description',
        'icon',
        'sort_order',
        'is_active',
    ];

    protected $appends = ['icon_url'];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function getIconUrlAttribute(): ?string
    {
        return MediaUrl::make($this->icon);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
