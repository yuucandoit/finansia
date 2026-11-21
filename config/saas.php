<?php

return [
    'enforcement_enabled' => env('SAAS_ENFORCE_LIMITS', false),
    'billing_webhook_secret' => env('SAAS_BILLING_WEBHOOK_SECRET'),
    'sandbox_instant_activate' => env('SAAS_SANDBOX_INSTANT_ACTIVATE', true),

    'plans' => [
        'free' => [
            'name'             => 'Starter (Gratis)',
            'price_monthly'    => 0,
            'price_yearly'     => 0,
            'monthly_ai_limit' => 30,
            'max_savings'      => 5,
            'max_future_plans' => 3,
        ],
        'pro' => [
            'name'             => 'Finansia Pro',
            'price_monthly'    => 39000,
            'price_yearly'     => 390000,
            'monthly_ai_limit' => null, // Unlimited
            'max_savings'      => null,
            'max_future_plans' => null,
        ],
    ],
];
