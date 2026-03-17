<?php

namespace app\mcp;

use app\support\EnvResolver;
use Mcp\Capability\Attribute\McpTool;
use Mcp\Capability\Attribute\Schema;
use Mcp\Exception\ToolCallException;
use support\think\Cache;

class SaiAdminCaptchaTool
{
    #[McpTool(
        name: 'get_captcha_code',
        description: '根据 uuid 获取 SaiAdmin 后台登录验证码真实值，仅支持 cache 存储模式'
    )]
    public function getCaptchaCode(
        #[Schema(description: '验证码 uuid')]
        string $uuid,
    ): string {
        $mode = EnvResolver::get('CAPTCHA_MODE', 'session');
        if ($mode !== 'cache') {
            throw new ToolCallException('get_captcha_code 仅支持 CAPTCHA_MODE=cache，当前模式无法只凭 uuid 读取验证码');
        }

        try {
            $code = Cache::get($uuid);
        } catch (\Throwable $e) {
            throw new ToolCallException($e->getMessage(), previous: $e);
        }

        if ($code === null || $code === false || $code === '') {
            throw new ToolCallException("captcha code not found: {$uuid}");
        }

        return (string) $code;
    }
}
