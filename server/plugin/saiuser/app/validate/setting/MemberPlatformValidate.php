<?php
// +----------------------------------------------------------------------
// | saiadmin [ saiadmin快速开发框架 ]
// +----------------------------------------------------------------------
// | Author: your name
// +----------------------------------------------------------------------
namespace plugin\saiuser\app\validate\setting;

use think\Validate;

/**
 * 平台配置验证器
 */
class MemberPlatformValidate extends Validate
{
    /**
     * 定义验证规则
     */
    protected $rule =   [
        'platform_name' => 'require',
        'platform_code' => 'require',
    ];

    /**
     * 定义错误信息
     */
    protected $message  =   [
        'platform_name' => '平台名称必须填写',
        'platform_code' => '平台标识必须填写',
    ];

    /**
     * 定义场景
     */
    protected $scene = [
        'save' => [
            'platform_name',
            'platform_code',
        ],
        'update' => [
            'platform_name',
            'platform_code',
        ],
    ];

}
