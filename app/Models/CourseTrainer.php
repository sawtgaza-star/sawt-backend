<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use App\Models\Concerns\PrunesStoredUploads;
use App\Support\MediaUrl;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

/**
 * Course instructor (separate from Creator). Name/title/bio/experience are JSON-translatable.
 * Used on course detail + incubator «فريق خبراء متخصص» section.
 */
class CourseTrainer extends Model
{
    use HasTranslations, HasUuid, PrunesStoredUploads;

    public array $translatable = ['name', 'title', 'bio', 'experience'];

    /** @var list<string> */
    protected array $storedUploads = ['avatar'];

    protected $fillable = [
        'name',
        'title',
        'bio',
        'experience',
        'avatar',
        'phone',
        'email',
        'socials',
        'is_active',
        'sort_order',
    ];

    protected $appends = ['avatar_url'];

    protected function casts(): array
    {
        return [
            'socials' => 'array',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function courses()
    {
        return $this->hasMany(Course::class, 'trainer_id');
    }

    public function getAvatarUrlAttribute(): ?string
    {
        return MediaUrl::make($this->avatar);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
