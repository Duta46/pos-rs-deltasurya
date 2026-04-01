<?php

return [
    'url'      => env('API_URL', 'https://recruitment.rsdeltasurya.com/api/v1'),
    'email'    => env('API_EMAIL'),
    'password' => env('API_PASSWORD'),
    'mode'     => env('API_MODE', 'live'),
    'cache_key' => 'external_api_token',
];
