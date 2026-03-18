<?php
// +----------------------------------------------------------------------
// | saiadmin [ saiadmin快速开发框架 ]
// +----------------------------------------------------------------------
// | Author: your name
// +----------------------------------------------------------------------
namespace plugin\saiuser\app\validate\member;

use think\Validate;

/**
 * 会员信息验证器
 */
class MemberValidate extends Validate
{
    /**
     * 定义验证规则
     */
    protected $rule = [
        'username' => 'require|alphaNum|min:4',
        'password' => 'require|min:6',
        'member_level_id' => 'require',
        'status' => 'require',
    ];

    /**
     * 定义错误信息
     */
    protected $message = [
        'username' => '用户名必须由英文和字母组成,长度必须大于4位',
        'password' => '密码长度必须大于6位',
        'member_level_id' => '会员等级必须填写',
        'status' => '状态必须填写',
    ];

    /**
     * 定义场景
     */
    protected $scene = [
        'save' => [
            'username',
            'password',
            'member_level_id',
            'status',
        ],
        'update' => [
            'username',
            'member_level_id',
            'status',
        ],
    ];

}
