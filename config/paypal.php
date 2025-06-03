<?php

return [

    'mode' => env('PAYPAL_MODE', 'live'),

//     'sandbox' => [
//         'client_id' => env('PAYPAL_SANDBOX_CLIENT_ID', ''),
//         'client_secret' => env('PAYPAL_SANDBOX_CLIENT_SECRET', ''),
//         'app_id' => '',
//     ],

    'live' => [
        'client_id' => env('PAYPAL_LIVE_CLIENT_ID', 'AYrGzAFKitQwR53r3vMV9RHt0Wrygn7UQNvhZBEbFkWvj7mAsbl3EKP7gBvePDUX2LQm6C87vSAF2TFm'),
        'client_secret' => env('PAYPAL_LIVE_CLIENT_SECRET', 'EBx0B5F49kWWCihTBcQVGSRaN7YhWtwqEhmhNQoHYhNRoeZ5lFB9WW0qUDK_p6uGsQZhb8xlgC2dknfi'),
        'app_id' => '',
    ],

    'payment_action' => 'Sale',
    'currency' => env('PAYPAL_CURRENCY', 'USD'),
    'notify_url' => '',
    'locale' => 'en_US',
    'validate_ssl' => true,
];
