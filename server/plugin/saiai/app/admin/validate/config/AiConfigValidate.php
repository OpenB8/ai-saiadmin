<?php
// +----------------------------------------------------------------------
// | saiadmin [ saiadmin快速开发框架 ]
// +----------------------------------------------------------------------
// | Author: your name
// +----------------------------------------------------------------------
namespace plugin\saiai\app\admin\validate\config;

use plugin\saiadmin\basic\BaseValidate;

/**
 * AI配置验证器
 */
class AiConfigValidate extends BaseValidate
{
    /**
     * 定义验证规则
     */
    protected $rule =   [
        'name' => 'require',
        'type' => 'require',
        'ai_key' => 'require',
        'model' => 'require',
        'is_default' => 'require',
    ];

    /**
     * 定义错误信息
     */
    protected $message  =   [
        'name' => '配置名称必须填写',
        'type' => '平台类型必须填写',
        'ai_key' => 'API Key必须填写',
        'model' => '模型名称必须填写',
        'is_default' => '是否默认必须填写',
    ];

    /**
     * 定义场景
     */
    protected $scene = [
        'save' => [
            'name',
            'type',
            'ai_key',
            'model',
            'is_default',
        ],
        'update' => [
            'name',
            'type',
            'ai_key',
            'model',
            'is_default',
        ],
    ];

}
