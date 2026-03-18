<?php
// +----------------------------------------------------------------------
// | saiadmin [ saiadmin快速开发框架 ]
// +----------------------------------------------------------------------
// | Author: your name
// +----------------------------------------------------------------------
namespace plugin\saiuser\app\admin\controller\member;

use plugin\saiadmin\basic\BaseController;
use plugin\saiuser\app\admin\logic\member\MemberPlatformRelLogic;
use plugin\saiuser\app\validate\member\MemberPlatformRelValidate;
use plugin\saiadmin\service\Permission;
use support\Request;
use support\Response;

/**
 * 平台绑定控制器
 */
class MemberPlatformRelController extends BaseController
{
    /**
     * 构造函数
     */
    public function __construct()
    {
        $this->logic = new MemberPlatformRelLogic();
        $this->validate = new MemberPlatformRelValidate;
        parent::__construct();
    }

    /**
     * 数据列表
     * @param Request $request
     * @return Response
     */
    #[Permission('平台绑定列表', 'saiuser:member:rel:index')]
    public function index(Request $request): Response
    {
        $where = $request->more([
            ['member_id', ''],
            ['username', ''],
            ['platform_id', ''],
            ['platform_openid', ''],
        ]);
        $query = $this->logic->search($where);
        $query->with(['member', 'platform']);
        $data = $this->logic->getList($query);
        return $this->success($data);
    }

    /**
     * 读取数据
     * @param Request $request
     * @return Response
     */
    #[Permission('平台绑定读取', 'saiuser:member:rel:index')]
    public function read(Request $request): Response
    {
        $id = $request->input('id', '');
        $model = $this->logic->read($id);
        if ($model) {
            $data = is_array($model) ? $model : $model->toArray();
            return $this->success($data);
        } else {
            return $this->fail('未查找到信息');
        }
    }
}
