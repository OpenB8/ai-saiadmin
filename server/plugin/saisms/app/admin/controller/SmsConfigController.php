<?php
// +----------------------------------------------------------------------
// | saiadmin [ saiadmin快速开发框架 ]
// +----------------------------------------------------------------------
// | Author: your name
// +----------------------------------------------------------------------
namespace plugin\saisms\app\admin\controller;

use plugin\saiadmin\basic\BaseController;
use plugin\saisms\app\admin\logic\SmsConfigLogic;
use plugin\saisms\app\validate\SmsConfigValidate;
use plugin\saiadmin\service\Permission;
use support\think\Cache;
use support\Request;
use support\Response;

/**
 * 短信配置控制器
 */
class SmsConfigController extends BaseController
{
    /**
     * 构造函数
     */
    public function __construct()
    {
        $this->logic = new SmsConfigLogic();
        $this->validate = new SmsConfigValidate;
        parent::__construct();
    }

    /**
     * 数据列表
     * @param Request $request
     * @return Response
     */
    #[Permission('短信配置列表', 'saisms:config:index')]
    public function index(Request $request): Response
    {
        $where = $request->more([
            ['gateway', ''],
            ['config_name', ''],
        ]);
        $this->logic->setOrderField('sort');
        $this->logic->setOrderType('desc');
        $query = $this->logic->search($where);
        $data = $this->logic->getList($query);
        return $this->success($data);
    }

    /**
     * 读取数据
     * @param Request $request
     * @return Response
     */
    #[Permission('短信配置读取', 'saisms:config:read')]
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
    #[Permission('短信配置添加', 'saisms:config:save')]
    public function save(Request $request): Response
    {
        $data = $request->post();
        $this->validate('save', $data);
        $result = $this->logic->add($data);
        if ($result) {
            $this->afterChange('save', $result);
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
    #[Permission('短信配置修改', 'saisms:config:update')]
    public function update(Request $request): Response
    {
        $data = $request->post();
        $this->validate('update', $data);
        $result = $this->logic->edit($data['id'], $data);
        if ($result) {
            $this->afterChange('update', $result);
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
    #[Permission('短信配置删除', 'saisms:config:destroy')]
    public function destroy(Request $request): Response
    {
        $ids = $request->post('ids', '');
        if (empty($ids)) {
            return $this->fail('请选择要删除的数据');
        }
        $result = $this->logic->destroy($ids);
        if ($result) {
            $this->afterChange('destroy', $ids);
            return $this->success('删除成功');
        } else {
            return $this->fail('删除失败');
        }
    }

    /**
     * 修改状态
     * @param Request $request
     * @return Response
     */
    #[Permission('短信配置状态', 'saisms:config:changeStatus')]
    public function changeStatus(Request $request): Response
    {
        $id = $request->input('id', '');
        $status = $request->input('status', 1);
        $model = $this->logic->findOrEmpty($id);
        if ($model->isEmpty()) {
            return $this->fail('未查找到信息');
        }
        $result = $model->save(['status' => $status]);
        if ($result) {
            $this->afterChange('changeStatus', $model);
            return $this->success('操作成功');
        } else {
            return $this->fail('操作失败');
        }
    }

    /**
     * 清理缓存
     * @param $type
     * @param $args
     * @return void
     */
    public function afterChange($type, $args): void
    {
        $cacheConfig = config('plugin.saisms.saithink.saisms_gateway', [
            'expire' => 60 * 60 * 24 * 365,
            'tag' => 'saiadmin:saisms_gateway',
        ]);
        Cache::delete($cacheConfig['tag']);
    }
}
