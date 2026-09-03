<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use App\Models\Concerns\PrunesStoredUploads;
use App\Support\StoredUploadCleanup;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

/**
 * Offline incubator course (detail + listing card).
 * `image` = incubator card cover only; trainer/category are course_* tables (not creators).
 */
class Course extends Model
{
    use HasTranslations, HasUuid, PrunesStoredUploads;

    public array $translatable = ['title', 'description'];

    /** @var list<string> */
    protected array $storedUploads = ['image'];

    protected $fillable = [
        'trainer_id',
        'course_category_id',
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
        'registration_ends_at',
        'duration_weeks',
        'duration_hours',
        'sessions_hours',
        'rating',
        'is_coming_soon',
        'max_seats',
        'requirements',
        'objectives',
        'modules',
        'outcomes_before',
        'outcomes_after',
        'benefits',
        'selection_steps',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'registration_ends_at' => 'datetime',
            'duration_weeks' => 'integer',
            'rating' => 'float',
            'is_coming_soon' => 'boolean',
            'max_seats' => 'integer',
            'requirements' => 'array',
            'objectives' => 'array',
            'modules' => 'array',
            'outcomes_before' => 'array',
            'outcomes_after' => 'array',
            'benefits' => 'array',
            'selection_steps' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Course $course): void {
            $course->delivery_mode = 'offline';
        });

        static::updating(function (Course $course): void {
            foreach (['objectives', 'selection_steps', 'benefits'] as $field) {
                if (! $course->isDirty($field)) {
                    continue;
                }

                $old = $course->getOriginal($field);
                if (is_string($old)) {
                    $decoded = json_decode($old, true);
                    $old = is_array($decoded) ? $decoded : $old;
                }

                StoredUploadCleanup::pruneReplaced($old, $course->getAttribute($field));
            }
        });

        static::deleting(function (Course $course): void {
            StoredUploadCleanup::pruneReplaced([
                'objectives' => $course->objectives,
                'selection_steps' => $course->selection_steps,
                'benefits' => $course->benefits,
            ], []);
        });
    }

    public function trainer()
    {
        return $this->belongsTo(CourseTrainer::class, 'trainer_id');
    }

    public function courseCategory()
    {
        return $this->belongsTo(CourseCategory::class, 'course_category_id');
    }

    public function joinRequests()
    {
        return $this->hasMany(CourseJoinRequest::class);
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    public function modulesCount(): int
    {
        $modules = $this->modules;

        return is_array($modules) ? count($modules) : 0;
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
