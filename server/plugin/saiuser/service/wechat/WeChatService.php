<?php
namespace plugin\saiuser\service\wechat;

use EasyWeChat\OfficialAccount\Application;
use plugin\saiadmin\exception\ApiException;
use Symfony\Component\HttpFoundation\HeaderBag;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;

/**
 * 微信公众号功能类
 * Class WeChatService
 * @package plugin\saiuser\service\wechat
 */
class WeChatService
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
        $config = WeChatConfigService::getOaConfig();
        if (empty($config['app_id']) || empty($config['secret'])) {
            throw new ApiException('请先设置公众号配置');
        }
        return $config;
    }

    /**
     * 服务端验证
     */
    public function serve($request)
    {
        $symfony_request = new SymfonyRequest($request->get(), $request->post(), [], $request->cookie(), [], [], $request->rawBody());
        $symfony_request->headers = new HeaderBag($request->header());
        $app = $this->app;
        $app->setRequestFromSymfonyRequest($symfony_request);
        $server = $this->app->getServer();

        $server->addEventListener('subscribe', function ($message, \Closure $next) {
            return '感谢您关注 EasyWeChat!';
        });

        $response = $server->serve();

        return response($response->getBody()->getContents(), $response->getStatusCode(), $response->getHeaders());
    }

    /**
     * 公众号-构造OAuth授权页面
     */
    public function getOAuthUrl($target): string
    {
        $oauth = $this->app->getOauth();
        return $oauth->scopes(['snsapi_userinfo'])->redirect($target);
    }

    /**
     * 公众号-根据code获取用户信息
     */
    public function getWechatByCode(string $code): array
    {
        $oauth = $this->app->getOauth();
        $user = $oauth->userFromCode($code);
        $data = $user->toArray();

        return [
            'platform_id' => 3,
            'openid' => $data['id'],
            'nickname' => $data['nickname'],
            'avatar' => $data['avatar']
        ];
    }

}