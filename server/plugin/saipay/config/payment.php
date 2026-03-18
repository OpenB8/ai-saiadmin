<?php
/**
 * @desc 支付配置文件
 */
return [
    'logger' => [
        'enable' => true,
        'file' => runtime_path().'/logs/payment.log',
        'level' => 'debug', // 建议生产环境等级调整为 info，开发环境为 debug
        'type' => 'single', // optional, 可选 daily.
        'max_file' => 30, // optional, 当 type 为 daily 时有效，默认 30 天
    ],
    'http' => [ // optional
        'timeout' => 5.0,
        'connect_timeout' => 5.0,
    ],
    '_force' => true,
];
