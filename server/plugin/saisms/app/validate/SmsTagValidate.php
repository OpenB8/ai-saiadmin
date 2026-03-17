<?php
// +----------------------------------------------------------------------
// | saiadmin [ saiadmin快速开发框架 ]
// +----------------------------------------------------------------------
// | Author: your name
// +----------------------------------------------------------------------
namespace plugin\saisms\app\validate;

use plugin\saiadmin\basic\BaseValidate;

/**
 * 短信标签验证器
 */
class SmsTagValidate extends BaseValidate
{
    /**
     * 定义验证规则
     */
    protected $rule = [
        'tag_name' => 'require',
        'gateway' => 'require',
    ];

    /**
     * 定义错误信息
     */
    protected $message = [
        'tag_name' => '标签名称必须填写',
        'gateway' => '选择网关必须填写',
    ];

    /**
     * 定义场景
     */
    protected $scene = [
        'save' => [
            'tag_name',
            'gateway',
        ],
        'update' => [
            'tag_name',
            'gateway',
        ],
    ];
}
