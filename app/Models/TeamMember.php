<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use App\Support\MediaUrl;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Translatable\HasTranslations;

class TeamMember extends Model
{
    use HasTranslations, HasUuid;

    public array $translatable = ['name', 'role', 'bio'];

    protected $fillable = [
        'major_id',
        'name',
        'role',
        'years_of_experience',
        'bio',
        'photo',
        'facebook_url',
        'linkedin_url',
        'twitter_url',
        'instagram_url',
        'sort_order',
        'is_active',
    ];

    protected $appends = ['photo_url'];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'years_of_experience' => 'integer',
        ];
    }

    public function major(): BelongsTo
    {
        return $this->belongsTo(Major::class);
    }

    public function getPhotoUrlAttribute(): ?string
    {
        return MediaUrl::make($this->photo);
    }

    /**
     * @return array<string, string|null>
     */
    public function socialLinks(): array
    {
        return [
            'facebook' => $this->facebook_url ?: null,
            'linkedin' => $this->linkedin_url ?: null,
            'twitter' => $this->twitter_url ?: null,
            'instagram' => $this->instagram_url ?: null,
        ];
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
