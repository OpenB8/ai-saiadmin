<?php
// +----------------------------------------------------------------------
// | saiadmin [ saiadmin快速开发框架 ]
// +----------------------------------------------------------------------
// | Author: your name
// +----------------------------------------------------------------------
namespace plugin\saiuser\app\validate\member;

use think\Validate;

/**
 * 登录日志验证器
 */
class MemberLoginLogValidate extends Validate
{
    /**
     * 定义验证规则
     */
    protected $rule =   [
        'member_id' => 'require',
        'platform_id' => 'require',
        'login_type' => 'require',
        'login_ip' => 'require',
    ];

    /**
     * 定义错误信息
     */
    protected $message  =   [
        'member_id' => '会员ID必须填写',
        'platform_id' => '登录平台ID必须填写',
        'login_type' => '登录方式必须填写',
        'login_ip' => '登录IP必须填写',
    ];

    /**
     * 定义场景
     */
    protected $scene = [
        'save' => [
            'member_id',
            'platform_id',
            'login_type',
            'login_ip',
        ],
        'update' => [
            'member_id',
            'platform_id',
            'login_type',
            'login_ip',
        ],
    ];

}
