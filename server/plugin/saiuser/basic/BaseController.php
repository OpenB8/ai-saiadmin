<?php
// +----------------------------------------------------------------------
// | saiadmin [ saiadmin快速开发框架 ]
// +----------------------------------------------------------------------
// | Author: sai <1430792918@qq.com>
// +----------------------------------------------------------------------
namespace plugin\saiuser\basic;

use plugin\saiadmin\basic\OpenController;
use plugin\saiadmin\exception\ApiException;
use plugin\saiuser\app\cache\MemberInfoCache;

/**
 * 基类 控制器继承此类
 */
class BaseController extends OpenController
{

    /**
     * 当前登陆用户
     */
    protected $memberInfo;

    /**
     * 当前登陆用户ID
     */
    protected $memberId;

    /**
     * 当前登陆账号
     */
    protected $memberName;

    /**
     * 逻辑层
     */
    protected $logic;

    /**
     * 初始化
     */
    protected function init(): void
    {
        $result = getSaiUser();
        if (!$result) {
            throw new ApiException('用户信息读取失败,请重新登录', 400);
        }
        if ($result['plat'] && $result['plat'] !== 'saiuser') {
            throw new ApiException('用户信息读取失败,请重新登录', 400);
        }
        $userInfoCache = MemberInfoCache::getUserInfo($result['id']);
        $this->memberId = $result['id'];
        $this->memberName = $result['username'];
        $this->memberInfo = $userInfoCache;
    }

}
