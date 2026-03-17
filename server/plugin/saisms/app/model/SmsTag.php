<?php
// +----------------------------------------------------------------------
// | saiadmin [ saiadmin快速开发框架 ]
// +----------------------------------------------------------------------
// | Author: your name
// +----------------------------------------------------------------------
namespace plugin\saisms\app\model;

use plugin\saiadmin\basic\think\BaseModel;

/**
 * 短信标签模型
 */
class SmsTag extends BaseModel
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
    protected $table = 'saisms_tag';

    /**
     * 标签名称 搜索
     * @param $query
     * @param $value
     * @return void
     */
    public function searchTagNameAttr($query, $value): void
    {
        $query->where('tag_name', 'like', '%' . $value . '%');
    }
}
