<?php
namespace plugin\saiuser\service\wechat;

use EasyWeChat\MiniApp\Application;
use plugin\saiadmin\exception\ApiException;

/**
 * 微信小程序功能类
 * Class WeChatMnpService
 * @package plugin\saiuser\service\wechat
 */
class WeChatMnpService
{
    protected $app;

    protected $config;

    public function __construct()
    {
        $this->config = $this->getConfig();
        $this->app = new Application($this->config);
    }

    /**
     * 配置
     */
    protected function getConfig(): array
    {
        $config = WeChatConfigService::getMnpConfig();
        if (empty($config['app_id']) || empty($config['secret'])) {
            throw new ApiException('请先设置小程序配置');
        }
        return $config;
    }

    /**
     * 小程序-根据code获取微信信息
     */
    public function getMnpResByCode(string $code): array
    {
        $utils = $this->app->getUtils();
        $response = $utils->codeToSession($code);
        if (empty($response['openid'])) {
            throw new ApiException('获取openID失败');
        }
        return [
            'platform_id' => 2,
            'openid' => $response['openid'],
            'nickname' => '',
            'avatar' => ''
        ];
    }

    /**
     * 获取手机号
     */
    public function getUserPhoneNumber(string $code)
    {
        return $this->app->getClient()->postJson('wxa/business/getuserphonenumber', [
            'code' => $code,
        ]);
    }

}