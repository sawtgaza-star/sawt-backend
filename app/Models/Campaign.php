<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use App\Models\Concerns\PrunesStoredUploads;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class Campaign extends Model
{
    use HasTranslations, HasUuid, PrunesStoredUploads;

    /** @var list<string> */
    protected array $storedUploads = ['image'];

    public array $translatable = ['title', 'description'];

    protected $fillable = [
        'title', 'slug', 'description', 'image',
        'target_amount', 'current_amount', 'start_date', 'end_date', 'status',
    ];

    protected function casts(): array
    {
        return [
            'target_amount' => 'decimal:2',
            'current_amount' => 'decimal:2',
            'start_date' => 'date',
            'end_date' => 'date',
        ];
    }

    public function donations()
    {
        return $this->hasMany(Donation::class);
    }

    public function getProgressPercentAttribute(): float
    {
        return $this->target_amount > 0
            ? round(($this->current_amount / $this->target_amount) * 100, 1)
            : 0;
    }
}
