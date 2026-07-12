<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;

class User extends Authenticatable implements FilamentUser
{
    use Notifiable, HasRoles, HasUuid;

    protected $fillable = [
        'name', 'email', 'phone', 'country_code', 'password', 'avatar', 'status',
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function creator()
    {
        return $this->hasOne(Creator::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function likes()
    {
        return $this->hasMany(Like::class);
    }

    public function donations()
    {
        return $this->hasMany(Donation::class);
    }

    public function enrollments()
    {
        return $this->hasMany(CourseEnrollment::class);
    }

    public function lessonProgress()
    {
        return $this->hasMany(LessonProgress::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    // يتحكم مين يقدر يدخل لوحة Filament (لازم يكون عنده أي دور معطى من الأدمن)
    public function canAccessPanel(Panel $panel): bool
    {
        return $this->status === 'active' && $this->hasAnyRole(['super_admin', 'admin', 'moderator']);
    }
}
