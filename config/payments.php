<?php

return [
    'gateway' => env('PAYMENT_GATEWAY', 'stripe'),
    'demo_amount_cents' => (int) env('DEMO_PAYMENT_AMOUNT_CENTS', 1000),
    'currency' => strtolower(env('PAYMENT_CURRENCY', 'mxn')),
];
