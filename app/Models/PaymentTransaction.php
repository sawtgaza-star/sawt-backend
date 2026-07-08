<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentTransaction extends Model
{
    protected $fillable = ['donation_id', 'gateway', 'gateway_transaction_id'];

    public function donation()
    {
        return $this->belongsTo(Donation::class);
    }
}
