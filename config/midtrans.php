<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Midtrans Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for Midtrans payment gateway integration.
    | Get your credentials from https://dashboard.midtrans.com
    |
    */

    'merchant_id' => env('MIDTRANS_MERCHANT_ID'),
    'client_key' => env('MIDTRANS_CLIENT_KEY'),
    'server_key' => env('MIDTRANS_SERVER_KEY'),
    'is_production' => env('MIDTRANS_IS_PRODUCTION', false),
    'is_sanitized' => env('MIDTRANS_IS_SANITIZED', true),
    'is_3ds' => env('MIDTRANS_IS_3DS', true),
    
    /*
    |--------------------------------------------------------------------------
    | Snap URL Configuration
    |--------------------------------------------------------------------------
    */
    
    'snap_url' => [
        'sandbox' => 'https://app.sandbox.midtrans.com/snap/snap.js',
        'production' => 'https://app.midtrans.com/snap/snap.js',
    ],
];
