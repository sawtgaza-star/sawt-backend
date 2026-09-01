<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use App\Models\Concerns\PrunesStoredUploads;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

class Video extends Model
{
    use HasTranslations, HasUuid, PrunesStoredUploads, SoftDeletes;

    /** @var list<string> */
    protected array $storedUploads = ['cover_url'];

    public array $translatable = ['title', 'description'];

    protected $fillable = [
        'category_id', 'creator_id', 'title', 'slug', 'description',
        'video_url', 'audio_url', 'cover_url', 'duration_seconds',
        'play_count', 'like_count', 'comment_count',
        'status', 'is_featured', 'published_at',
    ];

    protected function casts(): array
    {
        return [
            'is_featured' => 'boolean',
            'published_at' => 'datetime',
        ];
    }

    public function creator()
    {
        return $this->belongsTo(Creator::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'video_tags');
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function likes()
    {
        return $this->hasMany(Like::class);
    }

    public function views()
    {
        return $this->hasMany(VideoView::class);
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    public function scopeMostViewed($query)
    {
        return $query->orderByDesc('play_count');
    }
}
