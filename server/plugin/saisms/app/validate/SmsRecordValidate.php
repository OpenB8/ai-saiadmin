<?php
// +----------------------------------------------------------------------
// | saiadmin [ saiadmin快速开发框架 ]
// +----------------------------------------------------------------------
// | Author: your name
// +----------------------------------------------------------------------
namespace plugin\saisms\app\validate;

use plugin\saiadmin\basic\BaseValidate;

/**
 * 短信记录验证器
 */
class SmsRecordValidate extends BaseValidate
{
    /**
     * 定义验证规则
     */
    protected $rule = [
        'gateway' => 'require',
        'mobile' => 'require',
    ];

    /**
     * 定义错误信息
     */
    protected $message = [
        'gateway' => '网关必须填写',
        'mobile' => '手机号码必须填写',
    ];

    /**
     * 定义场景
     */
    protected $scene = [
        'save' => [
            'gateway',
            'mobile',
        ],
        'update' => [
            'gateway',
            'mobile',
        ],
    ];
}
