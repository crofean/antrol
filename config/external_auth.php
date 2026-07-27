<?php

return [
    'api_url' => env('AUTH_API_URL', 'https://backend-idrg.rsam.co.id'),
    'login_rate_limit' => env('AUTH_LOGIN_RATE_LIMIT', 5),    // per minute per IP
    'api_rate_limit' => env('AUTH_API_RATE_LIMIT', 120),      // per minute per IP
];
