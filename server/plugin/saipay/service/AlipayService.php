<?php
namespace plugin\saipay\service;

use Yansongda\Pay\Pay;
use Yansongda\Supports\Collection;
use Yansongda\Pay\Provider\Alipay;
use plugin\saiadmin\utils\Arr;
use plugin\saiadmin\exception\ApiException;
use plugin\saiadmin\app\logic\system\SystemConfigLogic;

/**
 * 支付宝支付服务
 */
class AlipayService
{
    /**
     * 读取配置信息
     * @return array
     */
    public static function getConfig(): array
    {
        $logic = new SystemConfigLogic();
        $alipay_config = $logic->getGroup('alipay_config');
        $upload_config = $logic->getGroup('upload_config');
        $config = [];
        foreach ($alipay_config as $key => $value) {
            if (in_array($value['key'], ['app_public_cert_path', 'alipay_public_cert_path', 'alipay_root_cert_path'])) {
                $config[$value['key']] = urlToPath($upload_config ,$value['value']);
            } else {
                $config[$value['key']] = $value['value'];
            }
        }
        $log = config('plugin.saipay.payment');
        return array_merge([
            'alipay' => [
                'default' => $config
            ]
        ], $log);
    }

    /*
     * 支付宝支付实例
     */
    public static function getInstance(array $params = []): Alipay
    {
        $config = self::getConfig();
        if (!empty($params)) {
            $config = array_merge($config, $params);
        }
        try {
            Pay::config($config);
            return Pay::alipay();
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
            return Pay::alipay()->callback($data);
        } catch (\Exception $e) {
            throw new ApiException($e->getMessage());
        }
    }

}