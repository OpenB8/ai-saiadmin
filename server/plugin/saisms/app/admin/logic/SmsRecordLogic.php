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
use plugin\saisms\app\model\SmsRecord;
use plugin\saisms\service\Sms;
use Throwable;

/**
 * 短信记录逻辑层
 */
class SmsRecordLogic extends BaseLogic
{
    /**
     * 构造函数
     */
    public function __construct()
    {
        $this->model = new SmsRecord();
    }

    /**
     * 发送验证码
     * @param array $data
     * @return array
     */
    public function sendCode(array $data): array
    {
        $mobile = $data['mobile'];
        $code = generateRandomNumber(6);

        $model = $this->model->where('mobile', $mobile)->order('create_time', 'desc')->findOrEmpty();
        if (!$model->isEmpty()) {
            $interval = 60 * 2;
            if (time() - strtotime($model->create_time) < $interval) {
                throw new ApiException('请勿频繁发送验证码，等待2分钟后再操作');
            }
        }

        $record = SmsRecord::create([
            'mobile' => $mobile,
            'code' => $code,
        ]);
        $gateways = [];
        if (isset($data['gateway'])) {
            $gateways = $data['gateway'];
        }

        try {
            $result = Sms::sendByTag($mobile, $data['tag_name'], ['code' => $code], $gateways);
            $response = end($result);
            $record->gateway = $response['gateway'];
            $record->status = $response['status'];
            $record->response = json_encode($response['result'], JSON_UNESCAPED_UNICODE);
            $record->save();
        } catch (Throwable $e) {
            $record->gateway = current($gateways) ?: '';
            $record->status = 'unsend';
            if (method_exists($e, 'getExceptions')) {
                $record->response = current($e->getExceptions())->getMessage();
            } else {
                $record->response = $e->getMessage();
            }
            $record->save();
        }
        return $record->toArray();
    }
}
