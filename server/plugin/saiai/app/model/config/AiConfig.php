<?php
// +----------------------------------------------------------------------
// | saiadmin [ saiadmin快速开发框架 ]
// +----------------------------------------------------------------------
// | Author: sai <1430792918@qq.com>
// +----------------------------------------------------------------------
namespace plugin\saiai\app\model\config;

use plugin\saiadmin\basic\think\BaseModel;

/**
 * AI配置模型
 *
 * saiai_config AI配置表
 *
 * @property  $id 
 * @property  $name 配置名称
 * @property  $type 平台类型
 * @property  $ai_key API Key
 * @property  $model 模型名称
 * @property  $is_default 是否默认
 * @property  $status 状态
 * @property  $remark 备注
 * @property  $created_by 创建者
 * @property  $updated_by 更新者
 * @property  $create_time 
 * @property  $update_time 
 */
class AiConfig extends BaseModel
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
    protected $table = 'saiai_config';

    /**
     * 配置名称 搜索
     */
    public function searchNameAttr($query, $value)
    {
        $query->where('name', 'like', '%' . $value . '%');
    }

}
