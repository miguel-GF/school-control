<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SchoolPayment extends Model
{
    protected $table = 'payments';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id', 'alumno_id', 'amount_cents', 'currency', 'status', 'gateway',
        'description', 'provider_payment_id', 'payment_method', 'paid_at',
    ];

    protected $casts = ['paid_at' => 'datetime'];

    public function attempts()
    {
        return $this->hasMany(PaymentAttempt::class, 'payment_id');
    }
}
