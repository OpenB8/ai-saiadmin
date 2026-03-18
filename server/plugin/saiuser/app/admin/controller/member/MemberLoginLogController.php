<?php
// +----------------------------------------------------------------------
// | saiadmin [ saiadmin快速开发框架 ]
// +----------------------------------------------------------------------
// | Author: your name
// +----------------------------------------------------------------------
namespace plugin\saiuser\app\admin\controller\member;

use plugin\saiadmin\basic\BaseController;
use plugin\saiuser\app\admin\logic\member\MemberLoginLogLogic;
use plugin\saiuser\app\validate\member\MemberLoginLogValidate;
use plugin\saiadmin\service\Permission;
use support\Request;
use support\Response;

/**
 * 登录日志控制器
 */
class MemberLoginLogController extends BaseController
{
    /**
     * 构造函数
     */
    public function __construct()
    {
        $this->logic = new MemberLoginLogLogic();
        $this->validate = new MemberLoginLogValidate;
        parent::__construct();
    }

    /**
     * 数据列表
     * @param Request $request
     * @return Response
     */
    #[Permission('登录日志列表', 'saiuser:member:log:index')]
    public function index(Request $request): Response
    {
        $where = $request->more([
            ['member_id', ''],
            ['username', ''],
            ['platform_id', ''],
            ['create_time', ''],
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
    #[Permission('登录日志读取', 'saiuser:member:log:index')]
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

    /**
     * 删除数据
     * @param Request $request
     * @return Response
     */
    #[Permission('登录日志删除', 'saiuser:member:log:destroy')]
    public function destroy(Request $request): Response
    {
        $ids = $request->post('ids', '');
        if (empty($ids)) {
            return $this->fail('请选择要删除的数据');
        }
        $result = $this->logic->destroy($ids);
        if ($result) {
            return $this->success('删除成功');
        } else {
            return $this->fail('删除失败');
        }
    }
}
