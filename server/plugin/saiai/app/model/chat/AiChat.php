<?php
// +----------------------------------------------------------------------
// | saiadmin [ saiadmin快速开发框架 ]
// +----------------------------------------------------------------------
// | Author: your name
// +----------------------------------------------------------------------
namespace plugin\saiai\app\model\chat;

use plugin\saiadmin\app\model\system\SystemUser;
use plugin\saiadmin\basic\think\BaseModel;

/**
 * 对话记录模型
 *
 * saiai_chat AI对话记录表
 *
 * @property  $id 
 * @property  $group_id 分组ID
 * @property  $user_id 用户ID
 * @property  $role 角色
 * @property  $model 模型名称
 * @property  $content 消息内容
 * @property  $tokens 消耗token数
 * @property  $ip IP地址
 * @property  $user_agent User Agent
 * @property  $created_by 创建人
 * @property  $updated_by 修改人
 * @property  $create_time 创建时间
 * @property  $update_time 修改时间
 */
class AiChat extends BaseModel
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
    protected $table = 'saiai_chat';

    /**
     * 关联模型 SystemUser
     */
    public function user()
    {
        return $this->belongsTo(SystemUser::class, 'user_id', 'id');
    }

}
