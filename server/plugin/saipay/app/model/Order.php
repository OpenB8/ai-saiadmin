<?php
// +----------------------------------------------------------------------
// | saiadmin [ saiadmin快速开发框架 ]
// +----------------------------------------------------------------------
// | Author: your name
// +----------------------------------------------------------------------
namespace plugin\saipay\app\model;

use plugin\saiadmin\basic\think\BaseModel;

/**
 * 订单记录模型
 */
class Order extends BaseModel
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
    protected $table = 'saipay_order';


    /**
     * 搜索器-订单名称
     */
    public function searchOrderNameAttr($query, $value)
    {
        $query->where('order_name', 'like', '%' . $value . '%');
    }

}
