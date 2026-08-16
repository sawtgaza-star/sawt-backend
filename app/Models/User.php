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

    public const TYPE_ADMIN = 'admin';

    public const TYPE_USER = 'user';

    public const TYPE_CONTENT_CREATOR = 'content_creator';

    public const ROLE_USER = self::TYPE_USER;

    public const ROLE_CONTENT_CREATOR = self::TYPE_CONTENT_CREATOR;

    /** @var list<string> */
    public const FILAMENT_ROLES = ['super_admin', 'admin', 'moderator'];

    /** @var list<string> */
    public const WEBSITE_ROLES = [self::ROLE_USER, self::ROLE_CONTENT_CREATOR];

    /** @var list<string> */
    public const TYPES = [self::TYPE_ADMIN, self::TYPE_USER, self::TYPE_CONTENT_CREATOR];

    protected $fillable = [
        'name', 'email', 'phone', 'country_code', 'password', 'avatar', 'status', 'type',
    ];

    protected $attributes = [
        'type' => self::TYPE_USER,
        'status' => 'active',
        'country_code' => '+970',
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

    public function isActive(): bool
    {
        return ($this->status ?: 'active') === 'active';
    }

    public function getJWTIdentifier(): mixed
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims(): array
    {
        return [
            'type' => $this->type ?: self::TYPE_USER,
            'roles' => $this->getRoleNames()->values()->all(),
        ];
    }

    public function isApiUser(): bool
    {
        return in_array($this->type, [self::TYPE_USER, self::TYPE_CONTENT_CREATOR], true)
            && $this->hasAnyRole(self::WEBSITE_ROLES)
            && ! $this->isAdmin();
    }

    public function isContentCreator(): bool
    {
        return $this->type === self::TYPE_CONTENT_CREATOR
            || $this->hasRole(self::ROLE_CONTENT_CREATOR);
    }

    public function isAdmin(): bool
    {
        return $this->type === self::TYPE_ADMIN
            || $this->hasAnyRole(self::FILAMENT_ROLES);
    }

    public function isFilamentAdmin(): bool
    {
        return $this->isAdmin();
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
        return $this->isActive()
            && $this->isAdmin()
            && ! $this->hasAnyRole(self::WEBSITE_ROLES);
    }
}
