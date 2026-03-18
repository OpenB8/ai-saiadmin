<?php
// +----------------------------------------------------------------------
// | saiadmin [ saiadmin快速开发框架 ]
// +----------------------------------------------------------------------
// | Author: sai <1430792918@qq.com>
// +----------------------------------------------------------------------
declare(strict_types=1);

namespace plugin\saiuser\app\cache;

use plugin\saiuser\app\admin\logic\member\MemberLogic;
use support\think\Cache;

/**
 * 会员信息缓存
 */
class MemberInfoCache
{
    /**
     * 读取缓存配置
     * @return array
     */
    public static function cacheConfig(): array
    {
        return config('plugin.saiuser.saithink.user_cache', [
            'prefix' => 'saiuser:user_cache:info_',
            'expire' => 60 * 60 * 4,
            'level' => 'saiuser:user_cache:level_',
        ]);
    }

    /**
     * 通过id获取缓存会员信息
     */
    public static function getUserInfo($uid): array
    {
        if (empty($uid)) {
            return [];
        }
        $cache = static::cacheConfig();
        // 直接从缓存获取
        $memberInfo = Cache::get($cache['prefix'] . $uid);

        if ($memberInfo) {
            return $memberInfo;
        }

        // 获取缓存信息并返回
        $memberInfo = static::setUserInfo($uid);
        if ($memberInfo) {
            return $memberInfo;
        }

        return [];
    }

    /**
     * 设置管理员信息
     */
    public static function setUserInfo($uid): array
    {
        $data = (new MemberLogic())->where('id', $uid)->field('id,username,nickname,avatar,email,mobile,member_level_id,last_login_time,last_login_ip')->findOrEmpty();
        if ($data->isEmpty()) {
            return [];
        }
        $data = $data->toArray();
        $cache = static::cacheConfig();

        $tags = [
            $cache['level'] . $data['member_level_id']
        ];
        Cache::tag($tags)->set($cache['prefix'] . $uid, $data, $cache['expire']);
        return $data;
    }

    /**
     * 清理管理员信息缓存
     */
    public static function clearUserInfo($uid): bool
    {
        $cache = static::cacheConfig();
        return Cache::delete($cache['prefix'] . $uid);
    }

    /**
     * 清理指定等级下用户缓存
     */
    public static function clearUserInfoByLevel($level_id): bool
    {
        $cache = static::cacheConfig();
        $tags = $cache['level'] . $level_id;
        return Cache::tag($tags)->clear();
    }

}
