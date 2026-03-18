<?php
// +----------------------------------------------------------------------
// | saiadmin [ saiadmin快速开发框架 ]
// +----------------------------------------------------------------------
// | Author: your name
// +----------------------------------------------------------------------
namespace plugin\saiai\app\api\logic;

use plugin\saiadmin\basic\think\BaseLogic;
use plugin\saiadmin\exception\ApiException;
use plugin\saiadmin\utils\Helper;
use plugin\saiai\app\model\config\AiConfig;

/**
 * AI配置逻辑层
 */
class IndexLogic extends BaseLogic
{
    /**
     * 构造函数
     */
    public function __construct()
    {
        $this->model = new AiConfig();
    }

    /**
     * 模型列表
     * @return array
     */
    public function modelList(): array
    {
        $list = $this->model->field('id, name, type, model')->where('status', 1)->select()->toArray();
        return $list;
    }

    /**
     * 默认模型
     * @return array
     */
    public function getDefaultModel(): array
    {
        $model = $this->model->field('id, name, type, model')
            ->where('is_default', 1)
            ->where('status', 1)
            ->findOrEmpty();
        if ($model->isEmpty()) {
            $model = $this->model->field('id, name, type, model')
                ->where('status', 1)
                ->findOrEmpty();
        }
        return $model->toArray();
    }
}
