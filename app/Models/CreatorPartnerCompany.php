<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use App\Models\Concerns\PrunesStoredUploads;
use App\Support\MediaUrl;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Translatable\HasTranslations;

class CreatorPartnerCompany extends Model
{
    use HasTranslations, HasUuid, PrunesStoredUploads;

    /** @var list<string> */
    protected array $storedUploads = ['logo'];

    public array $translatable = ['name', 'category'];

    protected $fillable = [
        'name',
        'category',
        'logo',
        'url',
        'sort_order',
        'is_active',
    ];

    protected $appends = ['logo_url'];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function creators(): BelongsToMany
    {
        return $this->belongsToMany(Creator::class, 'creator_partner_company_creator')
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
            // Qualify: creators.sort_order and pivot.sort_order both exist
            ->orderBy('creator_partner_company_creator.sort_order');
    }

    public function getLogoUrlAttribute(): ?string
    {
        return MediaUrl::make($this->logo);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
