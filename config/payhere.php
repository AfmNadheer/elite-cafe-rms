<?php

/*
 * PayHere payment gateway (https://www.payhere.lk).
 * Values come from the .env file so the secret is never stored in the code or on GitHub.
 */
return [
    'merchant_id' => env('PAYHERE_MERCHANT_ID'),
    'merchant_secret' => env('PAYHERE_MERCHANT_SECRET'),

    // true = sandbox (test cards, no real money); false = live payments
    'sandbox' => env('PAYHERE_SANDBOX', true),

    // PayHere must reach this URL from the internet. On localhost use an ngrok URL, e.g.
    // PAYHERE_NOTIFY_URL=https://abcd-1234.ngrok-free.app/payhere/notify
    'notify_url' => env('PAYHERE_NOTIFY_URL'),

    'currency' => 'LKR',

    // Billing address sent to PayHere for walk-in customers
    'address' => env('PAYHERE_ADDRESS', 'Elite Cafe (Pvt) Ltd'),
    'city' => env('PAYHERE_CITY', 'Sri Lanka'),
    'country' => 'Sri Lanka',
];
