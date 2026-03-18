<?php
// +----------------------------------------------------------------------
// | saiadmin [ saiadmin快速开发框架 ]
// +----------------------------------------------------------------------
// | Author: your name
// +----------------------------------------------------------------------
namespace plugin\saiai\app\admin\logic\chat;

use plugin\saiadmin\basic\think\BaseLogic;
use plugin\saiadmin\exception\ApiException;
use plugin\saiadmin\utils\Helper;
use plugin\saiai\app\model\chat\AiChatGroup;

/**
 * 对话分组逻辑层
 */
class AiChatGroupLogic extends BaseLogic
{
    /**
     * 构造函数
     */
    public function __construct()
    {
        $this->model = new AiChatGroup();
    }

    /**
     * 读取数据
     * @param $id
     * @return array
     */
    public function read($id): array
    {
        $admin = $this->model->find($id);
        $data = $admin->toArray();
        $data['user'] = $admin->user->toArray() ?: [];
        return $data;
    }

}
