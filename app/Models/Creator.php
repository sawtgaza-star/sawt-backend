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

    /** Profile fields AR|EN via Spatie. */
    public array $translatable = [
        'bio',
        'role',
    ];

    protected $fillable = [
        'user_id',
        'username',
        'bio',
        'role',
        'avatar',
        'followers_count',
        'views_count',
        'status',
        'sort_order',
        'is_verified',
    ];

    protected $appends = ['avatar_url'];

    protected function casts(): array
    {
        return [
            'followers_count' => 'integer',
            'views_count' => 'integer',
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

    /**
     * Partner companies for «أبرز التعاونات» — each pivot row has its own caption.
     * Order by pivot.sort_order (qualified — both tables have sort_order).
     */
    public function partnerCompanies()
    {
        return $this->belongsToMany(CreatorPartnerCompany::class, 'creator_partner_company_creator')
            ->using(CreatorCompanyCollaboration::class)
            ->withPivot([
                'sort_order',
                'quote_ar',
                'quote_en',
                'rating',
                'author_name',
                'author_role_ar',
                'author_role_en',
                'author_photo',
            ])
            ->withTimestamps()
            ->orderBy('creator_partner_company_creator.sort_order');
    }

    public function getAvatarUrlAttribute(): ?string
    {
        return MediaUrl::make($this->avatar);
    }

    /**
     * Instagram handle used to match Graph /collaborators on platform reels.
     * Prefer socials.platform = instagram URL; fall back to bare username if it looks like a handle.
     */
    public function instagramUsername(): ?string
    {
        $social = $this->relationLoaded('socials')
            ? $this->socials->first(fn (CreatorSocial $s) => strtolower((string) $s->platform) === 'instagram')
            : $this->socials()->where('platform', 'instagram')->first();

        $raw = $social?->url;
        if (! filled($raw) && filled($this->username) && ! str_contains((string) $this->username, ' ')) {
            $raw = (string) $this->username;
        }

        if (! filled($raw)) {
            return null;
        }

        // Normalize without depending on InstagramService in the model layer
        $raw = trim((string) $raw);
        if (str_contains($raw, 'instagram.com')) {
            $path = (string) parse_url($raw, PHP_URL_PATH);
            $raw = explode('/', trim($path, '/'))[0] ?? '';
        }
        $raw = ltrim($raw, '@');
        $raw = strtok($raw, '?#') ?: $raw;
        $raw = strtolower(trim($raw));

        return $raw !== '' ? $raw : null;
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
