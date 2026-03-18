<?php
// +----------------------------------------------------------------------
// | saiadmin [ saiadmin快速开发框架 ]
// +----------------------------------------------------------------------
// | Author: your name
// +----------------------------------------------------------------------
namespace plugin\saiuser\app\validate\cms;

use think\Validate;

/**
 * 文章分类验证器
 */
class ArticleCategoryValidate extends Validate
{
    /**
     * 定义验证规则
     */
    protected $rule =   [
        'category_name' => 'require',
    ];

    /**
     * 定义错误信息
     */
    protected $message  =   [
        'category_name' => '分类标题必须填写',
    ];

    /**
     * 定义场景
     */
    protected $scene = [
        'save' => [
            'category_name',
        ],
        'update' => [
            'category_name',
        ],
    ];

}
