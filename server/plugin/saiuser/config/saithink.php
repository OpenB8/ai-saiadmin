<?php

return [
    // 用户信息缓存
    'user_cache' => [
        'prefix' => 'saiuser:user_cache:info_',
        'expire' => 60 * 60 * 4,
        'level' => 'saiuser:user_cache:level_'
    ],

    // 默认头像
    'avatar' => 'https://robohash.org/',

    // 注册邮件
    'email' => [
        'subject' => 'saiadmin移动端',
        'content' => '如您未发起过此邮件，请忽略！'
    ],

    // 短信标签
    'sms' => [
        'tag' => 'action_code',
    ],

];