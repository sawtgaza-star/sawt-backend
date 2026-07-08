<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model;

class CreatorApplication extends Model
{
    use HasUuid;

    protected $fillable = [
        'reference_number', 'name', 'email', 'phone', 'content_type',
        'followers_count', 'bio', 'extra_notes', 'status',
        'rejection_reason', 'reviewed_by', 'reviewed_at', 'creator_id',
    ];

    protected function casts(): array
    {
        return ['reviewed_at' => 'datetime'];
    }

    public function socials()
    {
        return $this->hasMany(CreatorApplicationSocial::class);
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function creator()
    {
        return $this->belongsTo(Creator::class);
    }
}
