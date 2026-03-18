<?php
namespace plugin\saipay\service;

use Yansongda\Pay\Pay;
use Yansongda\Supports\Collection;
use Yansongda\Pay\Provider\Unipay;
use plugin\saiadmin\utils\Arr;
use plugin\saiadmin\exception\ApiException;
use plugin\saiadmin\app\logic\system\SystemConfigLogic;

/**
 * 银联支付服务
 */
class UnipayService
{
    /**
     * 读取配置信息
     * @return array
     */
    public static function getConfig(): array
    {
        $logic = new SystemConfigLogic();
        $unipay_config = $logic->getGroup('unipay_config');
        $upload_config = $logic->getGroup('upload_config');
        $config = [];
        foreach ($unipay_config as $key => $value) {
            if (in_array($value['key'], ['mch_cert_path', 'unipay_public_cert_path'])) {
                $config[$value['key']] = urlToPath($upload_config ,$value['value']);
            } else {
                $config[$value['key']] = $value['value'];
            }
        }
        $log = config('plugin.saipay.payment');
        return array_merge([
            'unipay' => [
                'default' => $config
            ]
        ], $log);
    }

    /*
     * 银联支付实例
     */
    public static function getInstance(): Unipay
    {
        $config = self::getConfig();
        try {
            Pay::config($config);
            return Pay::unipay();
        } catch (\Exception $e) {
            throw new ApiException($e->getMessage());
        }
    }

    /**
     * 支付回调
     */
    public static function notify($data): Collection
    {
        $config = self::getConfig();
        try {
            Pay::config($config);
            return Pay::unipay()->callback($data);
        } catch (\Exception $e) {
            throw new ApiException($e->getMessage());
        }
    }

}