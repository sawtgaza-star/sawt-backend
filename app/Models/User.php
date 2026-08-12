<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements FilamentUser, JWTSubject
{
    use HasRoles, HasUuid, Notifiable;

    public const ROLE_USER = 'user';

    public const ROLE_CONTENT_CREATOR = 'content_creator';

    /** @var list<string> */
    public const FILAMENT_ROLES = ['super_admin', 'admin', 'moderator'];

    /** @var list<string> */
    public const WEBSITE_ROLES = ['user', 'content_creator'];

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

    public function getJWTIdentifier(): mixed
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims(): array
    {
        return [
            'roles' => $this->getRoleNames()->values()->all(),
        ];
    }

    public function isApiUser(): bool
    {
        return $this->hasAnyRole(self::WEBSITE_ROLES) && ! $this->isFilamentAdmin();
    }

    public function isContentCreator(): bool
    {
        return $this->hasRole(self::ROLE_CONTENT_CREATOR);
    }

    public function isFilamentAdmin(): bool
    {
        return $this->hasAnyRole(self::FILAMENT_ROLES);
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

    public function courseJoinRequests()
    {
        return $this->hasMany(CourseJoinRequest::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function canAccessPanel(Panel $panel): bool
    {
        // Website roles never enter Filament.
        return $this->status === 'active'
            && $this->isFilamentAdmin()
            && ! $this->hasAnyRole(self::WEBSITE_ROLES);
    }
}
