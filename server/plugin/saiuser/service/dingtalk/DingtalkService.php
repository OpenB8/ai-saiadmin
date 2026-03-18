<?php
namespace plugin\saiuser\service\dingtalk;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use plugin\saiadmin\exception\ApiException;

/**
 * H5微引用
 * Class DingtalkService
 * @package plugin\saiuser\service\dingtalk
 */
class DingtalkService
{
    protected $baseApi = 'https://oapi.dingtalk.com';

    protected $http;

    protected $config;

    public function __construct()
    {
        $this->config = $this->getConfig();
        $this->http = new Client([
            'timeout' => 5,
            'verify' => false,
        ]);
    }

    /**
     * 配置
     */
    protected function getConfig(): array
    {
        $config = ConfigService::getH5Config();
        if (empty($config['corp_id']) || empty($config['agent_id'])) {
            throw new ApiException('请先设置钉钉H5配置');
        }
        return $config;
    }

    /**
     * 获取AccessToken
     * @return mixed
     * @throws GuzzleException
     */
    public function getAccessToken(): mixed
    {
        $url = $this->baseApi . '/gettoken';
        $params = [
            'appkey' => $this->config['app_key'],
            'appsecret' => $this->config['app_secret'],
        ];
        $response = $this->http->get($url, [
            'query' => $params,
        ]);
        $body = json_decode($response->getBody()->getContents(), true);
        if ($body['errcode'] != 0) {
            throw new ApiException($body['errmsg']);
        }
        return $body['access_token'];
    }

    /**
     * 钉钉-获取企业ID
     * @return string
     */
    public function getCropId(): string
    {
        return $this->config['corp_id'];
    }

    /**
     * 钉钉-根据code获取用户信息
     */
    public function getUserByCode(string $code): array
    {
        $access_token = $this->getAccessToken();
        $url = $this->baseApi . '/topapi/v2/user/getuserinfo';
        $response = $this->http->post($url, [
            'query' => [
                'access_token' => $access_token
            ],
            'json' => [
                'code' => $code
            ]
        ]);
        $body = json_decode($response->getBody()->getContents(), true);
        if ($body['errcode'] != 0) {
            throw new ApiException($body['errmsg']);
        }
        return [
            'platform_id' => 5,
            'openid' => $body['result']['userid'],
            'nickname' => $body['result']['name'],
            'avatar' => ''
        ];
    }
}