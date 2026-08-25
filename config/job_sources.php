<?php

use App\Jobs\Importing\Connectors\RemotiveConnector;

return [
    'connectors' => [
        'remotive' => [
            'name' => 'Remotive',
            'base_url' => 'https://remotive.com',
            'connector' => RemotiveConnector::class,
        ],
    ],

    'remotive' => [
        'endpoint' => env('REMOTIVE_API_URL', 'https://remotive.com/api/remote-jobs'),
        'limit' => (int) env('REMOTIVE_API_LIMIT', 50),
    ],
];
