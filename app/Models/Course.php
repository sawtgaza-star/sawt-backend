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
        'instructor_id',
        'category_id',
        'title',
        'slug',
        'description',
        'image',
        'level',
        'students_count',
        'status',
        'delivery_mode',
        'location',
        'location_details',
        'starts_at',
        'ends_at',
        'max_seats',
        'requirements',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'requirements' => 'array',
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

    public function joinRequests()
    {
        return $this->hasMany(CourseJoinRequest::class);
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    public function isJoinedBy(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        return $this->joinRequests()
            ->where('user_id', $user->id)
            ->where('status', 'accepted')
            ->exists();
    }

    public function joinRequestFor(?User $user): ?CourseJoinRequest
    {
        if (! $user) {
            return null;
        }

        return $this->joinRequests()
            ->where('user_id', $user->id)
            ->first();
    }

    public function pendingJoinRequestFor(?User $user): ?CourseJoinRequest
    {
        if (! $user) {
            return null;
        }

        return $this->joinRequests()
            ->where('user_id', $user->id)
            ->where('status', 'pending')
            ->first();
    }

    public function hasAvailableSeats(): bool
    {
        if ($this->max_seats === null) {
            return true;
        }

        $accepted = $this->joinRequests()->where('status', 'accepted')->count();

        return $accepted < $this->max_seats;
    }
}
