<?php

namespace app\mcp;

use Mcp\Capability\Attribute\McpTool;
use Mcp\Capability\Attribute\Schema;
use Mcp\Exception\ToolCallException;
use plugin\saiadmin\utils\Captcha;
use Throwable;

class SaiAdminCaptchaTool
{
    #[McpTool(
        name: 'get_captcha_code',
        description: '根据 uuid 获取 SaiAdmin 后台登录验证码真实值，读取逻辑与登录校验保持一致'
    )]
    public function getCaptchaCode(
        #[Schema(description: '验证码 uuid')]
        string $uuid,
    ): string {
        try {
            $code = Captcha::getCaptchaCode($uuid);
        } catch (Throwable $e) {
            throw new ToolCallException($e->getMessage(), previous: $e);
        }

        if ($code === null) {
            $code = $this->readCaptchaCodeFromRedis($uuid);
        }

        if ($code === null) {
            throw new ToolCallException("captcha code not found: {$uuid}");
        }

        return $code;
    }

    /**
     * 兼容某些常驻进程上下文未正确命中 Think Cache 驱动时的回退读取。
     */
    private function readCaptchaCodeFromRedis(string $uuid): ?string
    {
        if (config('plugin.saiadmin.saithink.captcha.mode', 'session') !== 'cache') {
            return null;
        }

        if (config('think-cache.default', 'file') !== 'redis') {
            return null;
        }

        if (!extension_loaded('redis')) {
            return null;
        }

        $host = (string) config('think-cache.stores.redis.host', '127.0.0.1');
        $port = (int) config('think-cache.stores.redis.port', 6379);
        $password = (string) config('think-cache.stores.redis.password', '');
        $database = (int) config('think-cache.stores.redis.select', 0);
        $prefix = (string) config('think-cache.stores.redis.prefix', '');

        try {
            $redis = new \Redis();
            $redis->connect($host, $port, (int) config('think-cache.stores.redis.timeout', 0));
            if ($password !== '') {
                $redis->auth($password);
            }
            if ($database !== 0) {
                $redis->select($database);
            }

            $value = $redis->get($prefix . $uuid);
            if ($value === false || $value === null || $value === '') {
                return null;
            }

            $decoded = @unserialize($value);
            if ($decoded !== false || $value === 'b:0;') {
                return is_scalar($decoded) || $decoded === null ? (string) $decoded : null;
            }

            return (string) $value;
        } catch (Throwable $e) {
            throw new ToolCallException($e->getMessage(), previous: $e);
        } finally {
            if (isset($redis) && $redis instanceof \Redis) {
                try {
                    $redis->close();
                } catch (Throwable) {
                    // ignore close failures
                }
            }
        }
    }
}
