<?php
// +----------------------------------------------------------------------
// | saiadmin [ saiadmin快速开发框架 ]
// +----------------------------------------------------------------------
// | Author: sai <1430792918@qq.com>
// +----------------------------------------------------------------------
namespace plugin\saiadmin\basic\think;

use support\think\Db;
use plugin\saiadmin\app\cache\UserInfoCache;
use plugin\saiadmin\basic\AbstractLogic;
use plugin\saiadmin\exception\ApiException;

/**
 * ThinkORM 逻辑层基类
 */
class BaseLogic extends AbstractLogic
{
    /**
     * 是否启用数据权限
     */
    protected bool $scope = false;

    /**
     * 当前数据权限命中的用户ID
     */
    public array $userIds = [];

    /**
     * 全部数据权限
     */
    public const ALL_SCOPE = 1;

    /**
     * 自定义数据权限
     */
    public const CUSTOM_SCOPE = 2;

    /**
     * 本部门数据权限
     */
    public const SELF_DEPT_SCOPE = 3;

    /**
     * 本部门及以下数据权限
     */
    public const DEPT_BELOW_SCOPE = 4;

    /**
     * 本人数据权限
     */
    public const SELF_SCOPE = 5;

    /**
     * 数据权限处理
     * 约定基于 created_by 字段做行级过滤。
     */
    public function userDataScope($query): mixed
    {
        $info = getCurrentInfo();
        if (!$info) {
            throw new ApiException('数据权限验证失败');
        }

        $this->adminInfo = UserInfoCache::getUserInfo($info['id']);
        $dataScope = self::ALL_SCOPE;
        $roleId = 1;

        foreach ($this->adminInfo['roleList'] as $role) {
            if ($role['data_scope'] > $dataScope) {
                $dataScope = $role['data_scope'];
                $roleId = $role['id'];
            }
        }

        switch ($dataScope) {
            case self::ALL_SCOPE:
                return $query;
            case self::CUSTOM_SCOPE:
                $deptIds = Db::table('sa_system_role_dept')->where('role_id', $roleId)->column('dept_id');
                $userIds = Db::table('sa_system_user')->where('dept_id', 'in', $deptIds)->column('id');
                $this->userIds = array_merge($this->userIds, $userIds);
                break;
            case self::SELF_DEPT_SCOPE:
                $deptId = $this->adminInfo['dept_id'];
                $userIds = Db::table('sa_system_user')->where('dept_id', $deptId)->column('id');
                $this->userIds = array_merge($this->userIds, $userIds);
                break;
            case self::DEPT_BELOW_SCOPE:
                $deptId = $this->adminInfo['dept_id'];
                $deptInfo = $this->adminInfo['deptList'];
                $oldLevel = $deptInfo['level'] . $deptId . ',';
                $deptIds = Db::table('sa_system_dept')->where('level', 'like', $oldLevel . '%')->column('id');
                $deptIds[] = $deptId;
                $userIds = Db::table('sa_system_user')->where('dept_id', 'in', $deptIds)->column('id');
                $this->userIds = array_merge($this->userIds, $userIds);
                break;
            case self::SELF_SCOPE:
                $this->userIds = array_merge($this->userIds, [$this->adminInfo['id']]);
                break;
            default:
                break;
        }

        return $query->where('created_by', 'in', array_unique($this->userIds));
    }

    /**
     * 数据库事务操作
     * @param callable $closure
     * @param bool $isTran
     * @return mixed
     */
    public function transaction(callable $closure, bool $isTran = true): mixed
    {
        return $isTran ? Db::transaction($closure) : $closure();
    }

    /**
     * 添加数据
     * @param array $data
     * @return mixed
     */
    public function add(array $data): mixed
    {
        $model = $this->model->create($data);
        return $model->getKey();
    }

    /**
     * 修改数据
     * @param mixed $id
     * @param array $data
     * @return mixed
     */
    public function edit($id, array $data): mixed
    {
        $query = $this->model->newQuery();
        if ($this->scope) {
            $query = $this->userDataScope($query);
        }

        $model = $query->findOrEmpty($id);
        if ($model->isEmpty()) {
            throw new ApiException('数据不存在');
        }
        return $model->save($data);
    }

    /**
     * 读取数据
     * @param mixed $id
     * @return mixed
     */
    public function read($id): mixed
    {
        $query = $this->model->newQuery();
        if ($this->scope) {
            $query = $this->userDataScope($query);
        }

        $model = $query->findOrEmpty($id);
        if ($model->isEmpty()) {
            throw new ApiException('数据不存在');
        }
        return $model;
    }

    /**
     * 删除数据
     * @param mixed $ids
     * @return bool
     */
    public function destroy($ids): bool
    {
        return $this->model->destroy(function ($query) use ($ids) {
            if ($this->scope) {
                $query = $this->userDataScope($query);
            }
            $query->whereIn($this->model->getPk(), $ids);
        });
    }

    /**
     * 搜索器搜索
     * @param array $searchWhere
     * @return mixed
     */
    public function search(array $searchWhere = []): mixed
    {
        $withSearch = array_keys($searchWhere);
        $data = [];
        foreach ($searchWhere as $key => $value) {
            if ($value !== '' && $value !== null && $value !== []) {
                $data[$key] = $value;
            }
        }
        $withSearch = array_keys($data);
        return $this->model->withSearch($withSearch, $data);
    }

    /**
     * 分页查询数据
     * @param mixed $query
     * @return mixed
     */
    public function getList($query): mixed
    {
        $request = request();
        $saiType = $request ? $request->input('saiType', 'list') : 'list';
        $page = $request ? $request->input('page', 1) : 1;
        $limit = $request ? $request->input('limit', 10) : 10;
        $orderField = $request ? $request->input('orderField', '') : '';
        $orderType = $request ? $request->input('orderType', $this->orderType) : $this->orderType;

        if ($this->scope) {
            $query = $this->userDataScope($query);
        }

        if (empty($orderField)) {
            $orderField = $this->orderField !== '' ? $this->orderField : $this->model->getPk();
        }

        $query->order($orderField, $orderType);

        if ($saiType === 'all') {
            return $query->select()->toArray();
        }

        return $query->paginate($limit, false, ['page' => $page])->toArray();
    }

    /**
     * 获取全部数据
     * @param mixed $query
     * @return mixed
     */
    public function getAll($query): mixed
    {
        $request = request();
        $orderField = $request ? $request->input('orderField', '') : '';
        $orderType = $request ? $request->input('orderType', $this->orderType) : $this->orderType;

        if ($this->scope) {
            $query = $this->userDataScope($query);
        }

        if (empty($orderField)) {
            $orderField = $this->orderField !== '' ? $this->orderField : $this->model->getPk();
        }

        $query->order($orderField, $orderType);
        return $query->select()->toArray();
    }
}
