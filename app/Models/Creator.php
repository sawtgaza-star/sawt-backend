<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use App\Support\MediaUrl;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

class Creator extends Model
{
    use SoftDeletes, HasTranslations, HasUuid;

    public array $translatable = ['bio'];

    protected $fillable = [
        'user_id',
        'username',
        'bio',
        'avatar',
        'followers_count',
        'status',
    ];

    protected $appends = ['avatar_url'];

    protected function casts(): array
    {
        return [
            'followers_count' => 'integer',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function socials()
    {
        return $this->hasMany(CreatorSocial::class)->orderBy('display_order');
    }

    public function videos()
    {
        return $this->hasMany(Video::class);
    }

    public function courses()
    {
        return $this->hasMany(Course::class, 'instructor_id');
    }

    public function getAvatarUrlAttribute(): ?string
    {
        return MediaUrl::make($this->avatar);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
