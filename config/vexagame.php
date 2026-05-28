<?php

return [
    'api_key' => env('VEXAGAME_API_KEY'),

    'is_production' => env('VEXAGAME_IS_PRODUCTION', true),

    'base_url' => env(
        'VEXAGAME_BASE_URL',
        env('VEXAGAME_IS_PRODUCTION', true)
            ? 'https://api.vexaagen.com'
            : 'https://dev.vexapay.vexatechno.com/api'
    ),

    'timeout' => env('VEXAGAME_TIMEOUT', 30),
    'callback_url' => env('VEXAGAME_CALLBACK_URL'),
    'callback_token' => env('VEXAGAME_CALLBACK_TOKEN'),
];
