<?php
// +----------------------------------------------------------------------
// | saiadmin [ saiadmin快速开发框架 ]
// +----------------------------------------------------------------------
// | Author: sai <1430792918@qq.com>
// +----------------------------------------------------------------------
namespace plugin\saicode\app\controller;

use support\Request;
use support\Response;
use plugin\saiadmin\basic\BaseController;
use plugin\saicode\app\logic\TableLogic;
use plugin\saicode\app\logic\DbLogic;
use plugin\saicode\app\validate\TableValidate;
use plugin\saiadmin\service\Permission;

/**
 * 低代码控制器
 */
class TableController extends BaseController
{

    /**
     * 数据源逻辑层
     */
    public $dbLogic;

    /**
     * 构造
     */
    public function __construct()
    {
        $this->logic = new TableLogic();
        $this->dbLogic = new DbLogic();
        $this->validate = new TableValidate;
        parent::__construct();
    }

    /**
     * 读取系统数据源
     * @return Response
     */
    #[Permission('读取数据源', 'saicode:generate:edit')]
    public function source(): Response
    {
        $data = config('think-orm.connections');
        $list = [];
        foreach ($data as $k => $v) {
            $list[] = $k;
        }
        return $this->success($list);
    }

    /**
     * 数据源数据表列表
     * @param Request $request
     * @return Response
     */
    #[Permission('数据源数据表列表', 'saicode:generate:edit')]
    public function sourceTable(Request $request): Response
    {
        $where = $request->more([
            ['name', ''],
            ['source', ''],
        ]);
        $data = $this->dbLogic->getList($where);
        return $this->success($data);
    }

    /**
     * 装载数据表
     * @param Request $request
     * @return Response
     */
    #[Permission('装载数据表', 'saicode:generate:edit')]
    public function loadTable(Request $request): Response
    {
        $names = $request->input('names', []);
        $source = $request->input('source', '');
        $this->logic->loadTable($names, $source);
        return $this->success('操作成功');
    }

    /**
     * 数据列表
     * @param Request $request
     * @return Response
     */
    #[Permission('代码生成数据列表', 'saicode:generate:edit')]
    public function index(Request $request): Response
    {
        $where = $request->more([
            ['table_name', ''],
            ['source', ''],
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
    #[Permission('读取代码生成数据', 'saicode:generate:edit')]
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
     * 修改数据
     * @param Request $request
     * @return Response
     */
    #[Permission('修改代码生成数据', 'saicode:generate:edit')]
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
     * 同步数据表字段信息
     * @param Request $request
     * @return Response
     */
    #[Permission('同步数据表', 'saicode:generate:edit')]
    public function sync(Request $request): Response
    {
        $id = $request->input('id');
        $this->logic->sync($id);
        return $this->success('操作成功');
    }

    /**
     * 代码预览
     */
    #[Permission('代码预览', 'saicode:generate:edit')]
    public function preview(Request $request): Response
    {
        $id = $request->input('id');
        $data = $this->logic->preview($id);
        return $this->success($data);
    }

    /**
     * 代码生成
     */
    #[Permission('代码生成', 'saicode:generate:edit')]
    public function generate(Request $request): Response
    {
        $ids = $request->input('ids', '');
        $data = $this->logic->generate($ids);
        return response()->download($data['download'], $data['filename']);
    }

    /**
     * 生成到模块
     */
    #[Permission('生成到模块', 'saicode:generate:edit')]
    public function generateFile(Request $request): Response
    {
        $id = $request->input('id', '');
        $this->logic->generateFile($id);
        return $this->success('操作成功');
    }

    /**
     * 获取数据表字段信息
     * @param Request $request
     * @return Response
     */
    #[Permission('获取数据表字段信息', 'saicode:generate:edit')]
    public function getTableColumns(Request $request): Response
    {
        $table_id = $request->input('table_id', '');
        $data = $this->logic->getTableColumns($table_id);
        return $this->success($data);
    }

    /**
     * 保存表单设计
     * @param Request $request
     * @return Response
     */
    #[Permission('保存表单设计', 'saicode:generate:edit')]
    public function saveDesign(Request $request): Response
    {
        $table = $request->input('table');
        $columns = $request->input('columns', []);
        $data = [
            'id' => $table['id'],
            'form_width' => $table['form_width'],
            'is_full' => $table['is_full'] === true ? 2 : 1,
            'component_type' => $table['component_type'],
            'span' => $table['form_span'],
        ];
        $this->logic->saveDesign($data, $columns);
        return $this->success('操作成功');
    }

    /**
     * 保存搜索设计
     * @param Request $request
     * @return Response
     */
    #[Permission('保存搜索设计', 'saicode:generate:edit')]
    public function saveSearchDesign(Request $request): Response
    {
        $table_id = $request->input('table_id');
        $columns = $request->input('columns', []);
        $this->logic->saveSearchDesign($table_id, $columns);
        return $this->success('操作成功');
    }

}