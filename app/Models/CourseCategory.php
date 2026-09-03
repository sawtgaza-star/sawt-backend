<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

/**
 * Course taxonomy for incubator courses (not blog/story categories).
 */
class CourseCategory extends Model
{
    use HasTranslations, HasUuid;

    public array $translatable = ['name'];

    protected $fillable = [
        'name',
        'slug',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function courses()
    {
        return $this->hasMany(Course::class, 'course_category_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
