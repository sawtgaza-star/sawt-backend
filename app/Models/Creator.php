<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

class Creator extends Model
{
    use SoftDeletes, HasTranslations, HasUuid;

    public array $translatable = ['bio'];

    protected $fillable = [
        'user_id', 'username', 'bio', 'content_type', 'followers_count',
        'views_count', 'total_videos', 'avatar', 'cover', 'monthly_goal_amount',
        'is_verified', 'is_featured', 'status',
        'bank_name', 'bank_account_owner', 'bank_account_number', 'bank_iban', 'paypal_email',
    ];

    protected function casts(): array
    {
        return [
            'is_verified' => 'boolean',
            'is_featured' => 'boolean',
            'monthly_goal_amount' => 'decimal:2',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function videos()
    {
        return $this->hasMany(Video::class);
    }

    public function socials()
    {
        return $this->hasMany(CreatorSocial::class)->orderBy('display_order');
    }

    public function collaborations()
    {
        return $this->hasMany(CreatorCollaboration::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }
}
