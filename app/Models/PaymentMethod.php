<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
{
    protected $table = 'payment_methods';

    protected $fillable = [
        'alumno_id', 'gateway', 'provider_payment_method_id', 'brand',
        'last4', 'exp_month', 'exp_year', 'attached',
    ];

    protected $casts = [
        'attached' => 'boolean',
        'exp_month' => 'integer',
        'exp_year' => 'integer',
    ];
}
