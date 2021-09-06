<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Stripe, Mailgun, SparkPost and others. This file provides a sane
    | default location for this type of information, allowing packages
    | to have a conventional place to find your various credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'sparkpost' => [
        'secret' => env('SPARKPOST_SECRET'),
    ],

    'stripe' => [
        'model' => App\User::class,
        'currency' => env('CURRENCY', 'INR'),
        //'key' => env('STRIPE_KEY', 'pk_live_51Hsy4xIlDpfdRREIR0lFLCMABkuR5Aorx3pVqf9rNsLW5VQTNUC0SI4mtxeA3NOuhNrBdPSoqT5oguTRmzMLMfRG00p6ahA2X9'),
        //'secret' => env('STRIPE_SECRET', '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'),
        'key' => env('STRIPE_KEY', 'pk_test_51HxGmYCbhZel9NwN2juYj7tS0YxoEXQsQBbT8ywV4y6aidfeTJARgwb7B8zCOpoRrqJFdxmIzfRqrtfmJK2nBetj00QCzrI8fc'),
        'secret' => env('STRIPE_SECRET', 'sk_test_51HxGmYCbhZel9NwNbpZZcVWJOfJiAD5JtvTdkcwbNuVYbsdvBWVAX9SEroZrjAbS9daJQxlMnI6erCzYDN6DsnIT00bsvBrBhy'),
        'webhook' => [
            'secret' => env('STRIPE_WEBHOOK_SECRET'),
            'tolerance' => env('STRIPE_WEBHOOK_TOLERANCE', 300),
        ],
    ],

    'twilio' => [
        'accountSid' => env('TWILIO_SID', 'AC19f6918a42e3246e0e3de1796a7b12ab'),
        'accountToken' => env('TWILIO_AUTH_TOKEN', '409a98e838c1d6a549128a2b89be839b'),
        'twilioNumber' => env('TWILIO_NUMBER', '+19389991640'),
    ],

];
