<?php
// +----------------------------------------------------------------------
// | saiadmin [ saiadmin快速开发框架 ]
// +----------------------------------------------------------------------
// | Author: your name
// +----------------------------------------------------------------------
namespace plugin\saiuser\app\model\setting;

use plugin\saiadmin\basic\think\BaseModel;

/**
 * 站点配置模型
 */
class SiteInfo extends BaseModel
{
    /**
     * 数据表主键
     * @var string
     */
    protected $pk = 'id';

    /**
     * 数据库表名称
     * @var string
     */
    protected $table = 'sa_site_info';

    /**
     * 站点名称 搜索
     */
    public function searchSiteNameAttr($query, $value)
    {
        $query->where('site_name', 'like', '%' . $value . '%');
    }

}
