<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentAttempt extends Model
{
    protected $table = 'payment_attempts';

    protected $fillable = [
        'payment_id', 'gateway', 'provider_payment_id', 'status', 'failure_code', 'metadata',
    ];

    protected $casts = ['metadata' => 'array'];

    public function payment()
    {
        return $this->belongsTo(SchoolPayment::class, 'payment_id');
    }
}
