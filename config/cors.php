<?php


return [
    'paths' => ['api/*', 'sanctum/csrf-cookie'], // Apply CORS to API routes

    'allowed_methods' => ['*'], // Allow all HTTP methods

    'allowed_origins' => ['https://karbein.net','https://www.karbein.net'], 

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'], // Allow all headers

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => true, // Enable if using cookies or sessions
];
