<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CreatorSocial extends Model
{
    protected $fillable = ['creator_id', 'platform', 'url', 'followers_count', 'display_order'];

    public function creator()
    {
        return $this->belongsTo(Creator::class);
    }
}
