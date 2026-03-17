<?php
// +----------------------------------------------------------------------
// | saiadmin [ saiadmin快速开发框架 ]
// +----------------------------------------------------------------------
// | Author: your name
// +----------------------------------------------------------------------
namespace plugin\saisms\app\validate;

use plugin\saiadmin\basic\BaseValidate;
use plugin\saisms\app\model\SmsConfig;

/**
 * 短信配置验证器
 */
class SmsConfigValidate extends BaseValidate
{
    /**
     * 定义验证规则
     */
    protected $rule = [
        'config_name' => 'require',
        'gateway' => 'require|unique:' . SmsConfig::class,
    ];

    /**
     * 定义错误信息
     */
    protected $message = [
        'config_name' => '网关名称必须填写',
        'gateway.require' => '网关必须填写',
        'gateway.unique' => '网关不能重复',
    ];

    /**
     * 定义场景
     */
    protected $scene = [
        'save' => [
            'config_name',
            'gateway'
        ],
        'update' => [
            'config_name',
            'gateway'
        ],
    ];

}
