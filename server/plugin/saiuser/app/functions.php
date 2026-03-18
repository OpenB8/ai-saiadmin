<?php

use Tinywan\Jwt\JwtToken;

if (!function_exists('getSaiUser')) {
    /**
     * 获取当前登录用户
     */
    function getSaiUser(): bool|array
    {
        if (!request()) {
            return false;
        }
        try {
            $token = JwtToken::getExtend();
        } catch (\Throwable $e) {
            return false;
        }
        return $token;
    }
}

if (!function_exists('getStoreAppType')) {
    /**
     * 获取应用类型列表
     */
    function getStoreAppType()
    {
       $typeMap = [
            ['label' => '安装插件', 'value' => 1, 'icon' => 'i-lucide-plug'],
            ['label' => '独立应用', 'value' => 2, 'icon' => 'i-lucide-box'],
            ['label' => '工具', 'value' => 3, 'icon' => 'i-lucide-wrench'],
            ['label' => '其他', 'value' => 4, 'icon' => 'i-lucide-shield-question-mark'],
       ];
        return $typeMap;
    }
}

if (!function_exists('getSupportVersion')) {
    /**
     * 获取支持版本列表
     */
    function getSupportVersion()
    {
       $typeMap = [
            ['label' => 'saiadmin 5.x', 'value' => 1],
            ['label' => 'saiadmin 6.x', 'value' => 2],
            ['label' => 'saimulti 5.x', 'value' => 3],
            ['label' => 'saimulti 6.x', 'value' => 4],
            ['label' => 'unknown', 'value' => 9],
       ];
        return $typeMap;
    }
}