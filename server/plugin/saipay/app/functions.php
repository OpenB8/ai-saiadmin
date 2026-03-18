<?php

use plugin\saiadmin\utils\Arr;
use Ramsey\Uuid\Uuid;

if (!function_exists('urlToPath')) {
    /**
     * url转为本地地址
     * @param $upload_config
     * @param $url
     * @return string
     */
    function urlToPath($upload_config, $url): string
    {
        $upload_mode = Arr::getConfigValue($upload_config, 'upload_mode');
        $local_root = Arr::getConfigValue($upload_config, 'local_root');
        $local_domain = Arr::getConfigValue($upload_config, 'local_domain');
        $local_uri = Arr::getConfigValue($upload_config, 'local_uri');
        if ($upload_mode == 1) {
            // 本地模式
            $old = $local_domain . $local_uri;
            $url = str_replace($old, $local_root, $url);
        }
        return base_path() . DIRECTORY_SEPARATOR . $url;
    }
}

if (!function_exists('uuid')) {
    /**
     * 获取唯一ID
     * @param int $type 类型
     * @param int|string $data 要计算的KEY数据
     * @param bool $number 是否返回数字
     * @return string
     */
    function uuid(int $type = 0, bool $number = false, $data = ''): string
    {
        if ($type == 0) {
            $snowflake = new Godruoyi\Snowflake\Snowflake();
            return $data . $snowflake->id();
        }
        switch ($type) {
            case 1: //基于时间
                $uuid = Uuid::uuid1();
                break;
            case 2:  //随机
                $uuid = Uuid::uuid4();
                break;
            case 3:  //基于主机ID、序列号
                $uuid = Uuid::uuid6();
                break;
            case 4: //基于散列的MD5(不建议加密敏感数据)
                $uuid = Uuid::uuid3(Uuid::NAMESPACE_DNS, $data);
                break;
            case 5: //基于SHA1(不建议加密敏感数据)
                $uuid = Uuid::uuid5(Uuid::NAMESPACE_DNS, $data);
                break;
            default:
                $uuid = Uuid::uuid7();
                break;
        }
        return $number ? $uuid->getInteger()->toString() : $uuid->toString();
    }
}
