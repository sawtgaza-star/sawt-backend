<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class CreatorCollaboration extends Model
{
    use HasTranslations;

    public array $translatable = ['description'];

    protected $fillable = [
        'creator_id', 'company_name', 'company_logo', 'description',
        'reviewer_name', 'reviewer_role', 'rating',
        'featured_video_url', 'featured_video_views', 'is_featured', 'sort_order',
    ];

    protected function casts(): array
    {
        return ['is_featured' => 'boolean'];
    }

    public function creator()
    {
        return $this->belongsTo(Creator::class);
    }
}
