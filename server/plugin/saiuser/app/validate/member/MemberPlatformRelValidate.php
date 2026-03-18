<?php
// +----------------------------------------------------------------------
// | saiadmin [ saiadmin快速开发框架 ]
// +----------------------------------------------------------------------
// | Author: your name
// +----------------------------------------------------------------------
namespace plugin\saiuser\app\validate\member;

use think\Validate;

/**
 * 平台绑定验证器
 */
class MemberPlatformRelValidate extends Validate
{
    /**
     * 定义验证规则
     */
    protected $rule =   [
        'member_id' => 'require',
        'platform_id' => 'require',
        'platform_openid' => 'require',
    ];

    /**
     * 定义错误信息
     */
    protected $message  =   [
        'member_id' => '会员ID必须填写',
        'platform_id' => '平台ID必须填写',
        'platform_openid' => '唯一标识必须填写',
    ];

    /**
     * 定义场景
     */
    protected $scene = [
        'save' => [
            'member_id',
            'platform_id',
            'platform_openid',
        ],
        'update' => [
            'member_id',
            'platform_id',
            'platform_openid',
        ],
    ];

}
