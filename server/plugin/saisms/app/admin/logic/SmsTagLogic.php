<?php
// +----------------------------------------------------------------------
// | saiadmin [ saiadmin快速开发框架 ]
// +----------------------------------------------------------------------
// | Author: your name
// +----------------------------------------------------------------------
namespace plugin\saisms\app\admin\logic;

use plugin\saiadmin\basic\think\BaseLogic;
use plugin\saiadmin\exception\ApiException;
use plugin\saiadmin\utils\Helper;
use plugin\saisms\app\model\SmsTag;
use Throwable;

/**
 * 短信标签逻辑层
 */
class SmsTagLogic extends BaseLogic
{
    /**
     * 构造函数
     */
    public function __construct()
    {
        $this->model = new SmsTag();
    }

    /**
     * 获取标签列表
     * @param array $data
     * @return array
     */
    public function getTagList(array $data): array
    {
        $query = $this->search($data);
        return $this->getAll($query);
    }

    /**
     * 测试短信发送
     * @param array $params
     * @return array
     */
    public function testTag(array $params): array
    {
        $data['mobile'] = $params['mobile'];
        $data['tag_name'] = $params['tag_name'];
        $data['gateway'] = [$params['gateway']];
        $logic = new SmsRecordLogic();
        return $logic->sendCode($data);
    }
}
