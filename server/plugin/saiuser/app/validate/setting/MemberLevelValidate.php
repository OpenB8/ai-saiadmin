<?php
// +----------------------------------------------------------------------
// | saiadmin [ saiadmin快速开发框架 ]
// +----------------------------------------------------------------------
// | Author: your name
// +----------------------------------------------------------------------
namespace plugin\saiuser\app\validate\setting;

use think\Validate;

/**
 * 会员等级验证器
 */
class MemberLevelValidate extends Validate
{
    /**
     * 定义验证规则
     */
    protected $rule =   [
        'level_name' => 'require',
        'level_code' => 'require',
        'min_points' => 'require',
        'max_points' => 'require',
    ];

    /**
     * 定义错误信息
     */
    protected $message  =   [
        'level_name' => '等级名称必须填写',
        'level_code' => '等级标识必须填写',
        'min_points' => '升级积分必须填写',
        'max_points' => '上限积分必须填写',
    ];

    /**
     * 定义场景
     */
    protected $scene = [
        'save' => [
            'level_name',
            'level_code',
            'min_points',
            'max_points',
        ],
        'update' => [
            'level_name',
            'level_code',
            'min_points',
            'max_points',
        ],
    ];

}
