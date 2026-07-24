<?php

$appUrl = env('APP_URL', 'https://isocalmarket.com');
$allowedOrigins = array_filter(array_map('trim', explode(',', (string) env('CORS_ALLOWED_ORIGINS', ''))));

$defaultOrigins = [
    'http://localhost:4200',
    'http://127.0.0.1:4200',
    'https://isocalmarket.com',
    'https://www.isocalmarket.com',
    'https://isocalmarket.netlify.app',
    rtrim($appUrl, '/')
];

return [
    'paths' => ['api/*', 'sanctum/csrf-cookie'],
    'allowed_methods' => ['*'],
    'allowed_origins' => array_values(array_unique(array_merge($defaultOrigins, $allowedOrigins))),
    'allowed_origins_patterns' => [],
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => true,
];
