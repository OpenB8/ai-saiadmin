<?php

use app\support\EnvResolver;

return [
    'default' => [
        'password' => EnvResolver::get('REDIS_PASSWORD', ''),
        'host' => EnvResolver::get('REDIS_HOST', '127.0.0.1'),
        'port' => EnvResolver::get('REDIS_PORT', 6379),
        'database' => EnvResolver::get('REDIS_DB', 0),
        'pool' => [
            'max_connections' => 5,
            'min_connections' => 1,
            'wait_timeout' => 3,
            'idle_timeout' => 60,
            'heartbeat_interval' => 50,
        ],
    ]
];
