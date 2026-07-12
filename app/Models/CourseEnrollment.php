<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model;

class CourseEnrollment extends Model
{
    use HasUuid;

    protected $fillable = [
        'course_id', 'user_id', 'status', 'price_paid', 'currency', 'enrolled_at',
    ];

    protected function casts(): array
    {
        return [
            'price_paid' => 'decimal:2',
            'enrolled_at' => 'datetime',
        ];
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function payment()
    {
        return $this->morphOne(Payment::class, 'payable');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
