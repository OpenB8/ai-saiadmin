<?php
// +----------------------------------------------------------------------
// | saiadmin [ saiadmin快速开发框架 ]
// +----------------------------------------------------------------------
// | Author: your name
// +----------------------------------------------------------------------
namespace plugin\saisms\app\model;

use plugin\saiadmin\basic\think\BaseModel;

/**
 * 短信记录模型
 */
class SmsRecord extends BaseModel
{
    /**
     * 短信记录表没有 delete_time 字段，关闭软删除。
     *
     * @var bool
     */
    protected $deleteTime = false;

    /**
     * 数据表主键
     * @var string
     */
    protected $pk = 'id';

    /**
     * 数据库表名称
     * @var string
     */
    protected $table = 'saisms_record';
}
