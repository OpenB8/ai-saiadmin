<?php
namespace plugin\saiuser\app\api\controller\user;

use plugin\saiuser\basic\BaseController;
use plugin\saiuser\app\api\logic\user\UserLogic;
use plugin\saiuser\app\cache\MemberInfoCache;
use support\Request;
use support\Response;

/**
 * 用户控制器
 */
class UserController extends BaseController
{
    protected $logic;

    /**
     * 构造函数
     */
    public function __construct()
    {
        $this->logic = new UserLogic();
        parent::__construct();
    }

    /**
     * 读取用户信息
     * @param Request $request
     * @return Response
     */
    public function userInfo(Request $request): Response
    {
        return $this->success($this->memberInfo);
    }

    /**
     * 上传图片
     * @param Request $request
     * @return Response
     */
    public function uploadImage(Request $request): Response
    {
        $type = $request->input('mode', 'system');
        $isLocal = ($type == 'local');
        $data = $this->logic->upload('image', $isLocal);
        return $this->success($data);
    }

    /**
     * 上传文件
     * @param Request $request
     * @return Response
     */
    public function uploadFile(Request $request): Response
    {
        $type = $request->input('mode', 'system');
        $isLocal = ($type == 'local');
        $data = $this->logic->upload('file', $isLocal);
        return $this->success($data);
    }

    /**
     * 修改资料
     * @param Request $request
     * @return Response
     */
    public function updateProfile(Request $request): Response
    {
        $data = $request->post();
        $this->logic->updateProfile($this->memberId, $data);
        return $this->success('操作成功');
    }

    /**
     * 修改密码
     * @param Request $request
     * @return Response
     */
    public function updatePassword(Request $request): Response
    {
        $data = $request->post();
        $this->logic->updatePassword($this->memberId, $data);
        return $this->success('操作成功');
    }

    /**
     * 日志明细
     * @param Request $request
     * @return Response
     */
    public function logsDetail(Request $request): Response
    {
        $where = $request->more([
            ['login_result', ''],
            ['platform_id', ''],
        ]);
        $data = $this->logic->getLoginLogs($this->memberId, $where);
        return $this->success($data);
    }

    /**
     * 获取当前积分
     */
    public function getIntegral(Request $request): Response
    {
        $data = $this->logic->getIntegral($this->memberId);
        return $this->success($data);
    }

    /**
     * 积分明细
     * @param Request $request
     * @return Response
     */
    public function pointsDetail(Request $request): Response
    {
        $where = $request->more([
            ['operate_type', ''],
            ['order_no', ''],
            ['create_time', []],
        ]);
        $data = $this->logic->getPointsLogs($this->memberId, $where);
        return $this->success($data);
    }

    /**
     * 清除缓存
     * @param Request $request
     * @return Response
     */
    public function clearCache(Request $request): Response
    {
        MemberInfoCache::clearUserInfo($this->memberId);
        return $this->success('操作成功');
    }

}
