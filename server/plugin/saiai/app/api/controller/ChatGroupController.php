<?php
// +----------------------------------------------------------------------
// | saiadmin [ saiadmin快速开发框架 ]
// +----------------------------------------------------------------------
namespace plugin\saiai\app\api\controller;

use plugin\saiadmin\basic\BaseController;
use plugin\saiai\app\api\logic\ChatGroupLogic;
use support\Request;
use support\Response;

/**
 * AI对话分组控制器
 */
class ChatGroupController extends BaseController
{
    /**
     * 构造函数
     */
    public function __construct()
    {
        $this->logic = new ChatGroupLogic();
        parent::__construct();
    }

    /**
     * 获取会话列表
     */
    public function getList(Request $request): Response
    {
        $userId = $this->adminId;
        $list = $this->logic->getUserGroupList($userId);
        return $this->success($list);
    }

    /**
     * 创建新会话
     */
    public function create(Request $request): Response
    {
        $userId = $this->adminId;
        $title = $request->input('title', '新对话');
        $group = $this->logic->createGroup($userId, $title);
        return $this->success($group);
    }

    /**
     * 获取会话详情（包含聊天记录）
     */
    public function detail(Request $request): Response
    {
        $id = $request->input('id');
        if (!$id) {
            return $this->fail('参数错误');
        }
        $userId = $this->adminId;
        $data = $this->logic->getDetail($id, $userId);
        if (!$data) {
            return $this->fail('会话不存在');
        }
        return $this->success($data);
    }

    /**
     * 更新会话标题
     */
    public function update(Request $request): Response
    {
        $id = $request->input('id');
        $title = $request->input('title');
        if (!$id || !$title) {
            return $this->fail('参数错误');
        }
        $userId = $this->adminId;
        $this->logic->updateTitle($id, $userId, $title);
        return $this->success();
    }

    /**
     * 删除会话
     */
    public function delete(Request $request): Response
    {
        $id = $request->input('id');
        if (!$id) {
            return $this->fail('参数错误');
        }
        $userId = $this->adminId;
        $this->logic->deleteGroup($id, $userId);
        return $this->success();
    }
}
