<?php
// +----------------------------------------------------------------------
// | saiadmin [ saiadmin快速开发框架 ]
// +----------------------------------------------------------------------
// | Author: your name
// +----------------------------------------------------------------------
namespace plugin\saisms\app\admin\controller;

use plugin\saiadmin\basic\BaseController;
use plugin\saisms\app\admin\logic\SmsTagLogic;
use plugin\saisms\app\validate\SmsTagValidate;
use plugin\saiadmin\service\Permission;
use support\Request;
use support\Response;

/**
 * 短信标签控制器
 */
class SmsTagController extends BaseController
{
    /**
     * 构造函数
     */
    public function __construct()
    {
        $this->logic = new SmsTagLogic();
        $this->validate = new SmsTagValidate;
        parent::__construct();
    }

    /**
     * 数据列表
     * @param Request $request
     * @return Response
     */
    #[Permission('短信标签列表', 'saisms:tag:index')]
    public function index(Request $request): Response
    {
        $where = $request->more([
            ['tag_name', ''],
        ]);
        $query = $this->logic->search($where);
        $data = $this->logic->getList($query);
        return $this->success($data);
    }

    /**
     * 读取数据
     * @param Request $request
     * @return Response
     */
    #[Permission('短信标签读取', 'saisms:tag:read')]
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
     * 保存数据
     * @param Request $request
     * @return Response
     */
    #[Permission('短信标签添加', 'saisms:tag:save')]
    public function save(Request $request): Response
    {
        $data = $request->post();
        $this->validate('save', $data);
        $result = $this->logic->add($data);
        if ($result) {
            return $this->success('添加成功');
        } else {
            return $this->fail('添加失败');
        }
    }

    /**
     * 更新数据
     * @param Request $request
     * @return Response
     */
    #[Permission('短信标签修改', 'saisms:tag:update')]
    public function update(Request $request): Response
    {
        $data = $request->post();
        $this->validate('update', $data);
        $result = $this->logic->edit($data['id'], $data);
        if ($result) {
            return $this->success('修改成功');
        } else {
            return $this->fail('修改失败');
        }
    }

    /**
     * 删除数据
     * @param Request $request
     * @return Response
     */
    #[Permission('短信标签删除', 'saisms:tag:destroy')]
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

    /**
     * 测试短信发送
     * @param Request $request
     * @return Response
     */
    #[Permission('短信测试发送', 'saisms:tag:testTag')]
    public function testTag(Request $request): Response
    {
        $data = $request->post();
        $result = $this->logic->testTag($data);
        return $this->success($result);
    }

}
