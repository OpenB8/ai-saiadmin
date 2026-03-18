<?php
// +----------------------------------------------------------------------
// | saiadmin [ saiadmin快速开发框架 ]
// +----------------------------------------------------------------------
// | Author: sai <1430792918@qq.com>
// +----------------------------------------------------------------------
namespace plugin\saiuser\app\event;

use plugin\saiuser\app\model\member\MemberLoginLog;

/**
 * 会员登录日志事件
 */
class MemberLogin
{
    /**
     * 登录日志
     * @param $item
     */
    public function login($item): void
    {
        $ip = request()->getRealIp();
        $http_user_agent = request()->header('user-agent');
        $data['member_id'] = $item['member_id'];
        $data['platform_id'] = $item['platform_id'];
        $data['login_ip'] = $ip;
        $data['login_location'] = self::getIpLocation($ip);
        $data['user_agent'] = $http_user_agent;
        $data['login_result'] = $item['status'];
        $data['fail_reason'] = $item['message'];
        MemberLoginLog::create($data);
    }

    /**
     * 获取IP地理位置
     */
    protected function getIpLocation($ip): string
    {
        $ip2region = new \Ip2Region();
        try {
            $region = $ip2region->memorySearch($ip);
        } catch (\Exception $e) {
            return '未知';
        }
        list($country, $province, $city, $network) = explode('|', $region['region']);
        if ($network === '内网IP') {
            return $network;
        }
        if ($country == '中国') {
            return $province.'-'.$city.':'.$network;
        } else if ($country == '0') {
            return '未知';
        } else {
            return $country;
        }
    }

}