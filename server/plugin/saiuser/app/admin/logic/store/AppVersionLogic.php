<?php
// +----------------------------------------------------------------------
// | saiadmin [ saiadmin快速开发框架 ]
// +----------------------------------------------------------------------
// | Author: your name
// +----------------------------------------------------------------------
namespace plugin\appstore\app\logic\store;

use plugin\saiadmin\basic\think\BaseLogic;
use plugin\saiadmin\utils\Helper;
use plugin\appstore\app\model\store\AppVersion;

/**
 * 应用版本逻辑层
 */
class AppVersionLogic extends BaseLogic
{
    /**
     * 构造函数
     */
    public function __construct()
    {
        $this->model = new AppVersion();
    }

}
