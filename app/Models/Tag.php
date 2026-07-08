<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class Tag extends Model
{
    use HasTranslations, HasUuid;

    public array $translatable = ['name'];

    protected $fillable = ['name', 'slug'];

    public function videos()
    {
        return $this->belongsToMany(Video::class, 'video_tags');
    }
}
