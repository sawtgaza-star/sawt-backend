<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use App\Models\Concerns\PrunesStoredUploads;
use App\Support\MediaUrl;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

class Creator extends Model
{
    use HasTranslations, HasUuid, PrunesStoredUploads, SoftDeletes;

    /** @var list<string> */
    protected array $storedUploads = ['avatar'];

    public array $translatable = ['bio', 'role'];

    protected $fillable = [
        'user_id',
        'username',
        'bio',
        'role',
        'avatar',
        'followers_count',
        'status',
        'sort_order',
        'is_verified',
    ];

    protected $appends = ['avatar_url'];

    protected function casts(): array
    {
        return [
            'followers_count' => 'integer',
            'sort_order' => 'integer',
            'is_verified' => 'boolean',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function socials()
    {
        return $this->hasMany(CreatorSocial::class)->orderBy('display_order');
    }

    public function videos()
    {
        return $this->hasMany(Video::class);
    }

    public function partnerCompanies()
    {
        return $this->belongsToMany(CreatorPartnerCompany::class, 'creator_partner_company_creator')
            ->withPivot('sort_order')
            ->orderByPivot('sort_order');
    }

    public function getAvatarUrlAttribute(): ?string
    {
        return MediaUrl::make($this->avatar);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
