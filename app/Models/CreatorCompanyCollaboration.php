<?php

namespace App\Models;

use App\Support\MediaUrl;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * Pivot: creator ↔ partner company with per-collaboration caption (أبرز التعاونات).
 * Video for the section is a shared latest Instagram reel — not stored here.
 */
class CreatorCompanyCollaboration extends Pivot
{
    protected $table = 'creator_partner_company_creator';

    public $incrementing = true;

    protected $fillable = [
        'creator_partner_company_id',
        'creator_id',
        'sort_order',
        'quote_ar',
        'quote_en',
        'rating',
        'author_name',
        'author_role_ar',
        'author_role_en',
        'author_photo',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'rating' => 'integer',
        ];
    }

    public function getAuthorPhotoUrlAttribute(): ?string
    {
        return MediaUrl::make($this->author_photo);
    }

    /**
     * @return array{ar: string, en: string}
     */
    public function quoteI18n(): array
    {
        return [
            'ar' => (string) ($this->quote_ar ?? ''),
            'en' => (string) ($this->quote_en ?? ''),
        ];
    }

    /**
     * @return array{ar: string, en: string}
     */
    public function authorRoleI18n(): array
    {
        return [
            'ar' => (string) ($this->author_role_ar ?? ''),
            'en' => (string) ($this->author_role_en ?? ''),
        ];
    }
}
