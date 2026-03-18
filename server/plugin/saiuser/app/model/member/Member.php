<?php
// +----------------------------------------------------------------------
// | saiadmin [ saiadmin快速开发框架 ]
// +----------------------------------------------------------------------
// | Author: your name
// +----------------------------------------------------------------------
namespace plugin\saiuser\app\model\member;

use plugin\saiadmin\basic\think\BaseModel;
use plugin\saiuser\app\model\setting\MemberLevel;

/**
 * 会员信息模型
 */
class Member extends BaseModel
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
    protected $table = 'sa_member';

    /**
     * 关键字 搜索
     */
    public function searchKeywordsAttr($query, $value)
    {
        $query->where('username|mobile|email', 'like', '%' . $value . '%');
    }

    /**
     * 时间范围搜索
     */
    public function searchCreateTimeAttr($query, $value)
    {
        $query->whereTime('create_time', 'between', $value);
    }

    /**
     * 生成用户编码
     * @param string $prefix 前缀
     * @param int $length 长度
     * @param int $maxAttempts 最大尝试次数
     * @return string
     * @throws \RuntimeException
     */
    public static function createUserSn($prefix = '', $length = 8, $maxAttempts = 10): string
    {
        for ($attempt = 0; $attempt < $maxAttempts; $attempt++) {
            $rand_str = '';
            for ($i = 0; $i < $length; $i++) {
                $rand_str .= mt_rand(1, 9);
            }
            $sn = $prefix . $rand_str;
            $model = Member::where(['username' => $sn])->findOrEmpty();
            if ($model->isEmpty()) {
                return $sn;
            }
        }
        throw new \RuntimeException('无法生成唯一用户编码，请稍后重试');
    }

    /**
     * 会员等级
     */
    public function level()
    {
        return $this->belongsTo(MemberLevel::class, 'member_level_id', 'id')->bind(['level_name']);
    }

    /**
     * 注册方式
     */
    public function platform()
    {
        return $this->belongsTo(MemberPlatform::class, 'register_platform_id', 'id')->bind(['platform_name']);
    }

}
