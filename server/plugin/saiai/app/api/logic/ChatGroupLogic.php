<?php
// +----------------------------------------------------------------------
// | saiadmin [ saiadmin快速开发框架 ]
// +----------------------------------------------------------------------
// | Author: sai <1430792918@qq.com>
// +----------------------------------------------------------------------
namespace plugin\saiai\app\api\logic;

use plugin\saiadmin\basic\think\BaseLogic;
use plugin\saiai\app\model\chat\AiChatGroup;
use plugin\saiai\app\model\chat\AiChat;

/**
 * AI对话分组逻辑层
 */
class ChatGroupLogic extends BaseLogic
{
    /**
     * 构造函数
     */
    public function __construct()
    {
        $this->model = new AiChatGroup();
    }

    /**
     * 获取用户会话列表
     * @param int $userId
     * @return array
     */
    /**
     * 获取用户会话列表
     * @param int $userId
     * @return array
     */
    public function getUserGroupList(int $userId)
    {
        return $this->model
            ->with('user')
            ->where('user_id', $userId)
            ->order('is_top desc, create_time desc')
            ->select()
            ->toArray();
    }

    /**
     * 创建新会话
     * @param int $userId
     * @param string $title
     * @return mixed
     */
    public function createGroup(int $userId, string $title = '新对话')
    {
        return $this->model->create([
            'user_id' => $userId,
            'title' => $title,
        ]);
    }

    /**
     * 更新会话标题
     */
    public function updateTitle(int $id, int $userId, string $title)
    {
        return $this->model->where('id', $id)->where('user_id', $userId)->update(['title' => $title]);
    }

    /**
     * 删除会话
     */
    public function deleteGroup(int $id, int $userId)
    {
        // 软删除分组
        $this->model->destroy(function ($query) use ($id, $userId) {
            $query->where('id', $id)->where('user_id', $userId);
        });

        // 删除对应的聊天记录
        AiChat::destroy(function ($query) use ($id) {
            $query->where('group_id', $id);
        });
        return true;
    }

    /**
     * 获取会话详情（包含聊天记录）
     */
    public function getDetail(int $groupId, int $userId)
    {
        // 验证归属
        $group = $this->model->where('id', $groupId)->where('user_id', $userId)->findOrEmpty();
        if ($group->isEmpty()) {
            return null;
        }

        // 获取聊天记录
        $chats = AiChat::with('user')
            ->where('group_id', $groupId)
            ->order('create_time asc')
            ->select()
            ->toArray();

        return [
            'group' => $group,
            'chats' => $chats
        ];
    }
}
