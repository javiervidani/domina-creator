<?php

// Settings for the read-only ops API (/ops/health, /ops/logs). The API stays hidden until OPS_TOKEN is set.
return [
    // At least 32 random characters. Empty means the endpoints answer 404 to everyone.
    'token' => env('OPS_TOKEN'),

    // Optional end date (YYYY-MM-DD, UTC) after which the token stops working. Rotate it after each debugging session.
    'expires' => env('OPS_TOKEN_EXPIRES'),

    'log_path' => env('OPS_LOG_PATH', storage_path('logs/laravel.log')),

    // How much of the end of the log file is read, so a large log never costs memory or tokens.
    'tail_bytes' => 262144,

    // Caps that keep every answer small.
    'max_entries' => 50,
    'line_max' => 300,
];
