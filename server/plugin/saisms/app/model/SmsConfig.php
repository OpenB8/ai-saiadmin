<?php
// +----------------------------------------------------------------------
// | saiadmin [ saiadmin快速开发框架 ]
// +----------------------------------------------------------------------
// | Author: your name
// +----------------------------------------------------------------------
namespace plugin\saisms\app\model;

use plugin\saiadmin\basic\think\BaseModel;

/**
 * 短信配置模型
 */
class SmsConfig extends BaseModel
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
    protected $table = 'saisms_config';

    /**
     * 网关名称 搜索
     * @param $query
     * @param $value
     * @return void
     */
    public function searchConfigNameAttr($query, $value): void
    {
        $query->where('config_name', 'like', '%' . $value . '%');
    }

    /**
     * 获取配置
     * @param $value
     * @return array|null
     */
    public function getConfigAttr($value): ?array
    {
        return json_decode($value ?? '', true);
    }

    /**
     * 设置配置
     * @param $value
     * @return string
     */
    public function setConfigAttr($value): string
    {
        return json_encode($value ?? '', JSON_UNESCAPED_UNICODE);
    }
}
