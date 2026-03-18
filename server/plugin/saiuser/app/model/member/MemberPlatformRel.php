<?php
// +----------------------------------------------------------------------
// | saiadmin [ saiadmin快速开发框架 ]
// +----------------------------------------------------------------------
// | Author: your name
// +----------------------------------------------------------------------
namespace plugin\saiuser\app\model\member;

use plugin\saiadmin\basic\think\BaseModel;

/**
 * 平台绑定模型
 */
class MemberPlatformRel extends BaseModel
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
    protected $table = 'sa_member_platform_rel';

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
     * 注册方式
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
