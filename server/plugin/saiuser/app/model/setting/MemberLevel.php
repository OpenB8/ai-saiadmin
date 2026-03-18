<?php
// +----------------------------------------------------------------------
// | saiadmin [ saiadmin快速开发框架 ]
// +----------------------------------------------------------------------
// | Author: your name
// +----------------------------------------------------------------------
namespace plugin\saiuser\app\model\setting;

use plugin\saiadmin\basic\think\BaseModel;

/**
 * 会员等级模型
 */
class MemberLevel extends BaseModel
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
    protected $table = 'sa_member_level';

    /**
     * 等级名称 搜索
     */
    public function searchLevelNameAttr($query, $value)
    {
        $query->where('level_name', 'like', '%' . $value . '%');
    }

}
