<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model;

/**
 * Guest "Subscribe now" application from the 3-step course modal.
 * Distinct from CourseJoinRequest (authenticated join / waitlist).
 * Reviewed in Filament; acceptance emails the applicant.
 */
class CourseSubscribeRequest extends Model
{
    use HasUuid;

    protected $fillable = [
        'course_id',
        'full_name',
        'phone',
        'phone_country_code',
        'email',
        'academic_level',
        'attended_similar_course',
        'goals_interests',
        'join_goal',
        'additional_notes',
        'status',
        'admin_notes',
        'reviewed_by',
        'reviewed_at',
    ];

    protected function casts(): array
    {
        return [
            'attended_similar_course' => 'boolean',
            'reviewed_at' => 'datetime',
        ];
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isAccepted(): bool
    {
        return $this->status === 'accepted';
    }
}
