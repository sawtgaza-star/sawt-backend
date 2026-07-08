<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CreatorApplicationSocial extends Model
{
    public $timestamps = false;

    protected $fillable = ['creator_application_id', 'platform', 'url'];

    public function application()
    {
        return $this->belongsTo(CreatorApplication::class, 'creator_application_id');
    }
}
