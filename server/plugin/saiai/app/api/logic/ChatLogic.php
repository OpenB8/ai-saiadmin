<?php
// +----------------------------------------------------------------------
// | saiadmin [ saiadmin快速开发框架 ]
// +----------------------------------------------------------------------
namespace plugin\saiai\app\api\logic;

use plugin\saiadmin\basic\think\BaseLogic;
use plugin\saiai\app\model\chat\AiChat;

/**
 * AI对话逻辑层
 */
class ChatLogic extends BaseLogic
{
    /**
     * 构造函数
     */
    public function __construct()
    {
        $this->model = new AiChat();
    }

    /**
     * 保存对话记录
     * @param int $userId 用户ID
     * @param string $role 角色
     * @param string $content 内容
     * @param string $model 模型
     * @return mixed
     */
    public function saveChat(int $userId, string $role, string $content, string $model, string $groupId = '')
    {
        return $this->model->create([
            'group_id' => $groupId,
            'user_id' => $userId,
            'role' => $role,
            'content' => $content,
            'model' => $model,
        ]);
    }
}
