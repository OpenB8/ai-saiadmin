<?php

use plugin\saiadmin\app\middleware\SystemLog;
use plugin\saiadmin\app\middleware\CheckLogin;
use plugin\saiadmin\app\middleware\CheckAuth;

return [
    'admin' => [
        CheckLogin::class,
        CheckAuth::class,
        SystemLog::class,
    ]
];
