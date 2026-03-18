<?php
// +----------------------------------------------------------------------
// | saiadmin [ saiadmin快速开发框架 ]
// +----------------------------------------------------------------------
// | Author: your name
// +----------------------------------------------------------------------
namespace plugin\saiuser\app\validate\member;

use think\Validate;

/**
 * 积分记录验证器
 */
class MemberPointsLogValidate extends Validate
{
    /**
     * 定义验证规则
     */
    protected $rule =   [
        'member_id' => 'require',
        'platform_id' => 'require',
        'points_change' => 'require',
        'points_before' => 'require',
        'points_after' => 'require',
        'operate_type' => 'require',
    ];

    /**
     * 定义错误信息
     */
    protected $message  =   [
        'member_id' => '会员ID（关联member表）必须填写',
        'platform_id' => '操作平台必须填写',
        'points_change' => '积分变动值必须填写',
        'points_before' => '变动前积分必须填写',
        'points_after' => '变动后积分必须填写',
        'operate_type' => '操作类型必须填写',
    ];

    /**
     * 定义场景
     */
    protected $scene = [
        'save' => [
            'member_id',
            'platform_id',
            'points_change',
            'points_before',
            'points_after',
            'operate_type',
        ],
        'update' => [
            'member_id',
            'platform_id',
            'points_change',
            'points_before',
            'points_after',
            'operate_type',
        ],
    ];

}
