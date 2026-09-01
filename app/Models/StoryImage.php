<?php

namespace App\Models;

use App\Models\Concerns\PrunesStoredUploads;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StoryImage extends Model
{
    use PrunesStoredUploads;

    /** @var list<string> */
    protected array $storedUploads = ['image'];
    protected $fillable = [
        'story_id',
        'image',
        'sort_order',
    ];

    public function story(): BelongsTo
    {
        return $this->belongsTo(Story::class);
    }
}
