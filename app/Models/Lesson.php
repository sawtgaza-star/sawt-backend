<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class Lesson extends Model
{
    use HasTranslations, HasUuid;

    public array $translatable = ['title', 'description'];

    protected $fillable = [
        'course_id', 'course_section_id', 'title', 'description',
        'video_provider', 'video_url', 'duration_seconds', 'is_preview', 'sort_order',
    ];

    protected function casts(): array
    {
        return ['is_preview' => 'boolean'];
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function section()
    {
        return $this->belongsTo(CourseSection::class, 'course_section_id');
    }

    public function progress()
    {
        return $this->hasMany(LessonProgress::class);
    }
}
