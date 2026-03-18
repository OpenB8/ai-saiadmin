<?php
// +----------------------------------------------------------------------
// | saiadmin [ saiadmin快速开发框架 ]
// +----------------------------------------------------------------------
// | Author: your name
// +----------------------------------------------------------------------
namespace plugin\saiai\app\model\chat;

use plugin\saiadmin\basic\think\BaseModel;
use plugin\saiadmin\app\model\system\SystemUser;

/**
 * 对话分组模型
 *
 * saiai_chat_group AI对话分组表
 *
 * @property  $id 
 * @property  $user_id 用户
 * @property  $title 会话标题
 * @property  $is_top 是否置顶
 * @property  $created_by 创建者
 * @property  $updated_by 更新者
 * @property  $create_time 创建时间
 * @property  $update_time 修改时间
 */
class AiChatGroup extends BaseModel
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
    protected $table = 'saiai_chat_group';

    /**
     * 用户 搜索
     */
    public function searchUserNameAttr($query, $value)
    {
        $query->hasWhere('user', function ($query) use ($value) {
            $query->where('username', 'like', '%' . $value . '%');
        });
    }

    /**
     * 会话标题 搜索
     */
    public function searchTitleAttr($query, $value)
    {
        $query->where('title', 'like', '%' . $value . '%');
    }

    /**
     * 修改时间 搜索
     */
    public function searchUpdateTimeAttr($query, $value)
    {
        $query->whereBetween('update_time', $value);
    }

    /**
     * 关联模型 SystemUser
     */
    public function user()
    {
        return $this->belongsTo(SystemUser::class, 'user_id', 'id');
    }

}
