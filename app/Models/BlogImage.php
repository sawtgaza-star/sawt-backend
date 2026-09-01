<?php

namespace App\Models;

use App\Models\Concerns\PrunesStoredUploads;
use Illuminate\Database\Eloquent\Model;

class BlogImage extends Model
{
    use PrunesStoredUploads;

    /** @var list<string> */
    protected array $storedUploads = ['image'];
    protected $fillable = [
        'blog_id',
        'image',
        'sort_order',
    ];

    public function blog()
    {
        return $this->belongsTo(Blog::class);
    }
}
