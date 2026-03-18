<?php
namespace plugin\saipay\service;

use Yansongda\Pay\Pay;
use Yansongda\Supports\Collection;
use Yansongda\Pay\Provider\Wechat;
use plugin\saiadmin\exception\ApiException;
use plugin\saiadmin\app\logic\system\SystemConfigLogic;

/**
 * 微信支付服务
 */
class WechatPayService
{
    /**
     * 读取配置信息
     * @return array
     */
    public static function getConfig(): array
    {
        $logic = new SystemConfigLogic();
        $wxpay_config = $logic->getGroup('wxpay_config');
        $upload_config = $logic->getGroup('upload_config');
        $config = [];
        foreach ($wxpay_config as $key => $value) {
            if (in_array($value['key'], ['mch_secret_cert', 'mch_public_cert_path'])) {
                $config[$value['key']] = urlToPath($upload_config ,$value['value']);
            } else {
                $config[$value['key']] = $value['value'];
            }
        }
        $log = config('plugin.saipay.payment');
        return array_merge([
            'wechat' => [
                'default' => $config
            ]
        ], $log);
    }

    /*
     * 微信支付实例
     */
    public static function getInstance(array $params = []): Wechat
    {
        $config = self::getConfig();
        if (!empty($params)) {
            $config = array_merge($config, $params);
        }
        try {
            Pay::config($config);
            return Pay::wechat();
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
            return Pay::wechat()->callback($data);
        } catch (\Exception $e) {
            throw new ApiException($e->getMessage());
        }
    }

}