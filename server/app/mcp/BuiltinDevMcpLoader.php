<?php

namespace app\mcp;

use Mcp\Capability\Discovery\Discoverer;
use Mcp\Capability\Registry\Loader\LoaderInterface;
use Mcp\Capability\RegistryInterface;
use support\Log;

/**
 * 按工具名过滤插件内置开发工具的 Loader。
 *
 * 作用：
 * 1. 扫描 webman-mcp 插件自带的 DevMcp 工具目录
 * 2. 根据配置中的工具开关决定是否注册某个内置工具
 * 3. 避免直接修改 vendor 内的 DevelopmentMcpLoader，便于后续升级
 */
class BuiltinDevMcpLoader implements LoaderInterface
{
    /**
     * @param array<string, bool> $toolSwitches
     * @param string[] $paths
     */
    public function __construct(
        private readonly array $toolSwitches = [],
        private readonly array $paths = [],
    ) {
    }

    public function load(RegistryInterface $registry): void
    {
        // 使用 SDK 的发现器扫描插件内置的开发工具定义
        $discoverer = new Discoverer(Log::channel('plugin.luoyue.webman-mcp.mcp_error_stderr'));
        $state = $discoverer->discover(base_path(), ['vendor/luoyue/webman-mcp/src/DevMcp', ...$this->paths], []);

        foreach ($state->getTools() as $tool) {
            // 仅对 tool 做按名称过滤。
            // 未显式配置的工具默认开启，避免插件升级新增工具后默认丢失。
            if (!($this->toolSwitches[$tool->tool->name] ?? true)) {
                continue;
            }
            $registry->registerTool($tool->tool, $tool->handler, true);
        }

        // 当前插件内置开发能力主要是 tools，下面这些保留原始注册行为。
        // 如果后续插件新增了 prompt/resource 且你也想单独开关，可以按同样方式扩展。
        foreach ($state->getPrompts() as $prompt) {
            $registry->registerPrompt($prompt->prompt, $prompt->handler, $prompt->completionProviders, true);
        }
        foreach ($state->getResources() as $resource) {
            $registry->registerResource($resource->resource, $resource->handler, true);
        }
        foreach ($state->getResourceTemplates() as $resource) {
            $registry->registerResourceTemplate($resource->resourceTemplate, $resource->handler, $resource->completionProviders, true);
        }
    }
}
