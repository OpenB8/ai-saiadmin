<?php
// +----------------------------------------------------------------------
// | saiadmin [ saiadmin快速开发框架 ]
// +----------------------------------------------------------------------
// | Author: your name
// +----------------------------------------------------------------------
namespace plugin\saiai\app\admin\validate\chat;

use plugin\saiadmin\basic\BaseValidate;

/**
 * 对话分组验证器
 */
class AiChatGroupValidate extends BaseValidate
{
    /**
     * 定义验证规则
     */
    protected $rule =   [
        'user_id' => 'require',
        'title' => 'require',
    ];

    /**
     * 定义错误信息
     */
    protected $message  =   [
        'user_id' => '用户必须填写',
        'title' => '会话标题必须填写',
    ];

    /**
     * 定义场景
     */
    protected $scene = [
        'save' => [
            'user_id',
            'title',
        ],
        'update' => [
            'user_id',
            'title',
        ],
    ];

}
