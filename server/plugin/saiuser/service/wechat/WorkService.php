<?php
namespace plugin\saiuser\service\wechat;

use EasyWeChat\Work\Application;
use plugin\saiadmin\exception\ApiException;

/**
 * 企业微信功能类
 * Class WorkService
 * @package plugin\saiuser\service\wechat
 */
class WorkService
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
        $config = WeChatConfigService::getWorkConfig();
        if (empty($config['corp_id']) || empty($config['secret'])) {
            throw new ApiException('请先设置企业微信配置');
        }
        return $config;
    }

    /**
     * 企业微信-构造OAuth授权页面
     * @param $target
     * @return string
     */
    public function getOAuthUrl($target): string
    {
        $oauth = $this->app->getOAuth();
        return $oauth->redirect($target);
    }

    /**
     * 企业微信-根据code获取用户信息
     */
    public function getWorkByCode(string $code): array
    {
        $oauth = $this->app->getOAuth();
        $user = $oauth->detailed()->userFromCode($code);
        $data = $user->getRaw();

        return [
            'platform_id' => 6,
            'openid' => $data['userid'],
            'nickname' => $data['name'],
            'avatar' => ''
        ];

    }
}