<?php
// +----------------------------------------------------------------------
// | saiadmin [ saiadmin快速开发框架 ]
// +----------------------------------------------------------------------
// | Author: your name
// +----------------------------------------------------------------------
namespace plugin\saiuser\app\validate\cms;

use think\Validate;

/**
 * 轮播列表验证器
 */
class ArticleBannerValidate extends Validate
{
    /**
     * 定义验证规则
     */
    protected $rule =   [
        'image' => 'require',
        'title' => 'require',
    ];

    /**
     * 定义错误信息
     */
    protected $message  =   [
        'image' => '图片地址必须填写',
        'title' => '标题必须填写',
    ];

    /**
     * 定义场景
     */
    protected $scene = [
        'save' => [
            'image',
            'title',
        ],
        'update' => [
            'image',
            'title',
        ],
    ];

}
