<?php
// +----------------------------------------------------------------------
// | saiadmin [ saiadmin快速开发框架 ]
// +----------------------------------------------------------------------
// | Author: your name
// +----------------------------------------------------------------------
namespace plugin\saiuser\app\validate\setting;

use think\Validate;

/**
 * 站点配置验证器
 */
class SiteInfoValidate extends Validate
{
    /**
     * 定义验证规则
     */
    protected $rule =   [
        'site_name' => 'require',
    ];

    /**
     * 定义错误信息
     */
    protected $message  =   [
        'site_name' => '站点名称必须填写',
    ];

    /**
     * 定义场景
     */
    protected $scene = [
        'save' => [
            'site_name',
        ],
        'update' => [
            'site_name',
        ],
    ];

}
