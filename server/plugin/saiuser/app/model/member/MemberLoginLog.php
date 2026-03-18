<?php
// +----------------------------------------------------------------------
// | saiadmin [ saiadmin快速开发框架 ]
// +----------------------------------------------------------------------
// | Author: your name
// +----------------------------------------------------------------------
namespace plugin\saiuser\app\model\member;

use plugin\saiadmin\basic\think\BaseModel;

/**
 * 登录日志模型
 */
class MemberLoginLog extends BaseModel
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
    protected $table = 'sa_member_login_log';

    /**
     * 用户名搜索
     */
    public function searchUsernameAttr($query, $value)
    {
        $query->hasWhere('member', function ($query) use ($value) {
            $query->where('username', 'like', '%' . $value . '%');
        });
    }

    /**
     * 时间范围搜索
     */
    public function searchCreateTimeAttr($query, $value)
    {
        $query->whereTime('create_time', 'between', $value);
    }

    /**
     * 登录平台
     */
    public function platform()
    {
        return $this->belongsTo(MemberPlatform::class, 'platform_id', 'id')->bind(['platform_name']);
    }

    /**
     * 会员账号
     */
    public function member()
    {
        return $this->belongsTo(Member::class, 'member_id', 'id')->bind(['username']);
    }

}
