<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class Course extends Model
{
    use HasTranslations, HasUuid;

    public array $translatable = ['title', 'description'];

    protected $fillable = [
        'instructor_id', 'category_id', 'title', 'slug', 'description', 'image',
        'level', 'price', 'currency', 'is_free', 'duration_minutes',
        'students_count', 'status',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'is_free' => 'boolean',
        ];
    }

    public function instructor()
    {
        return $this->belongsTo(Creator::class, 'instructor_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function sections()
    {
        return $this->hasMany(CourseSection::class)->orderBy('sort_order');
    }

    public function lessons()
    {
        return $this->hasMany(Lesson::class)->orderBy('sort_order');
    }

    public function enrollments()
    {
        return $this->hasMany(CourseEnrollment::class);
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    public function isFree(): bool
    {
        return $this->is_free || (float) $this->price <= 0;
    }

    /** هل هذا المستخدم مسجّل ودفع (وصول فعّال)؟ */
    public function isPurchasedBy(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        return $this->enrollments()
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->exists();
    }
}
