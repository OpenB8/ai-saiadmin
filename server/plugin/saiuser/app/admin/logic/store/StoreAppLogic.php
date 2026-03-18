<?php
// +----------------------------------------------------------------------
// | saiadmin [ saiadmin快速开发框架 ]
// +----------------------------------------------------------------------
// | Author: your name
// +----------------------------------------------------------------------
namespace plugin\appstore\app\logic\store;

use plugin\appstore\app\model\store\AppDocument;
use plugin\appstore\app\model\store\AppVersion;
use plugin\saiadmin\basic\think\BaseLogic;
use plugin\saiadmin\utils\Helper;
use plugin\saiadmin\exception\ApiException;
use plugin\appstore\app\model\store\StoreApp;

/**
 * 应用列表逻辑层
 */
class StoreAppLogic extends BaseLogic
{
    /**
     * 构造函数
     */
    public function __construct()
    {
        $this->model = new StoreApp();
    }

    /**
     * 读取数据
     * @param $id
     * @return mixed
     */
    public function read($id): mixed
    {
        $model = $this->model->findOrEmpty($id);
        if ($model->isEmpty()) {
            throw new ApiException('数据不存在');
        }
        $version = AppVersion::where('app_id', $model->id)->order('create_time desc')->select()->toArray();
        $document = AppDocument::where('app_id', $model->id)->order('sort desc')->select()->toArray();
        $data = $model->toArray();
        $data['version_list'] = $version;
        $data['ducument_list'] = $document;
        return $data;
    }

    /**
     * 下载版本
     */
    public function downloadVersion($id)
    {
        $model = AppVersion::where('id', $id)->findOrEmpty();
        if ($model->isEmpty()) {
            throw new ApiException('应用信息查找失败');
        }
        $extension = pathinfo($model->file_path, PATHINFO_EXTENSION);
        $file = base_path() . $model->file_path;

        return [
            'file' => $file,
            'file_name' => $model->version . '.' . $extension
        ];
    }

    /**
     * 应用-认证审核
     */
    public function approval($data)
    {
        $model = $this->model->where('id', $data['id'])->findOrEmpty();
        if ($model->isEmpty()) {
            throw new ApiException('应用信息读取失败');
        }
        if ($model->app_status !== 1) {
            throw new ApiException('该应用状态异常，未提交审核！');
        }
        if ($data['approval_status'] == 3) {
            $model->app_status = 3;
            $model->approval_desc = $data['approval_desc'];
            return $model->save();
        }

        // 1 判断版本是否存在
        $version = AppVersion::where('app_id', $model->id)->count();
        if ($version < 1) {
            throw new ApiException('未找到应用版本信息！');
        }

        // 2 判断文档是否添加
        $document = AppDocument::where('app_id', $model->id)->count();
        if ($document < 1) {
            throw new ApiException('未找到应用文档信息！');
        }

        $model->app_status = 2;
        $model->approval_desc = $data['approval_desc'];
        return $model->save();
    }

}
