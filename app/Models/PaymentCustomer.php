<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentCustomer extends Model
{
    protected $table = 'payment_customers';

    protected $fillable = ['alumno_id', 'gateway', 'provider_customer_id'];
}
