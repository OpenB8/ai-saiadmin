<?php

namespace plugin\saisms\service;

use Overtrue\EasySms\Contracts\MessageInterface;
use Overtrue\EasySms\Contracts\PhoneNumberInterface;
use Overtrue\EasySms\Exceptions\GatewayErrorException;
use Overtrue\EasySms\Gateways\Gateway;
use Overtrue\EasySms\Support\Config;
use Overtrue\EasySms\Traits\HasHttpRequest;

/**
 * 短信宝网关
 */
class Smsbao extends Gateway
{
    use HasHttpRequest;

    public const ENDPOINT_URL = 'https://api.smsbao.com/%s';

    public const SUCCESS_CODE = '0';

    /**
     * 官方错误码
     *
     * @var array<string, string>
     */
    protected array $errorStatuses = [
        '-1' => '参数不全',
        '-2' => '服务器不支持',
        '30' => '密码错误',
        '40' => '账号不存在',
        '41' => '余额不足',
        '42' => '账号已过期',
        '43' => 'IP 地址限制',
        '50' => '内容含有敏感词',
        '51' => '手机号码不正确',
    ];

    /**
     * 发送短信
     * 支持传入明文密码、MD5 后密码，或直接传 api_key
     */
    public function send(PhoneNumberInterface $to, MessageInterface $message, Config $config): string
    {
        $content = trim((string) $message->getContent($this));
        if ($content === '') {
            throw new GatewayErrorException('短信内容不能为空', 'empty_content', []);
        }

        [$action, $mobile] = $this->resolveTarget($to);

        $result = trim((string) $this->get($this->buildEndpoint($action), [
            'u' => $config->get('user'),
            'p' => $this->resolvePassword($config),
            'm' => $mobile,
            'c' => $content,
        ]));

        if ($result !== self::SUCCESS_CODE) {
            $message = $this->errorStatuses[$result] ?? '短信发送失败';
            throw new GatewayErrorException($message, $result, []);
        }

        return $result;
    }

    protected function buildEndpoint(string $action): string
    {
        return sprintf(self::ENDPOINT_URL, $action);
    }

    /**
     * @return array{string, string}
     */
    protected function resolveTarget(PhoneNumberInterface $to): array
    {
        if (is_null($to->getIDDCode()) || $to->getIDDCode() === '86') {
            return ['sms', $to->getNumber()];
        }

        return ['wsms', $to->getUniversalNumber()];
    }

    protected function resolvePassword(Config $config): string
    {
        $apiKey = trim((string) $config->get('api_key', $config->get('apiKey', '')));
        if ($apiKey !== '') {
            return $apiKey;
        }

        $password = trim((string) $config->get('password'));
        if ($password === '') {
            throw new GatewayErrorException('短信宝配置缺少 password 或 api_key', 'invalid_config', []);
        }

        if (preg_match('/^[a-f0-9]{32}$/i', $password) === 1) {
            return strtolower($password);
        }

        return md5($password);
    }
}
