<?php

namespace app\mcp;

use Dotenv\Parser\Parser;
use Mcp\Capability\Attribute\McpTool;
use Mcp\Capability\Attribute\Schema;
use Mcp\Exception\ToolCallException;

class EnvFileTool
{
    #[McpTool(
        name: 'get_env_file',
        description: '读取 .env 文件中的原始环境变量键值',
        outputSchema: [
            'type' => 'object',
            'additionalProperties' => ['type' => ['string', 'null']],
        ]
    )]
    public function getEnvFile(
        #[Schema(description: '环境变量名，为空时返回 .env 文件内全部键值')]
        ?string $key = null,
    ): array|string|null {
        $envFile = base_path(false) . '/.env';
        if (!is_file($envFile)) {
            throw new ToolCallException(".env file not found: {$envFile}");
        }

        $content = file_get_contents($envFile);
        if ($content === false) {
            throw new ToolCallException(".env file unreadable: {$envFile}");
        }

        $entries = (new Parser())->parse($content);
        $items = [];
        foreach ($entries as $entry) {
            $items[$entry->getName()] = $entry->getValue()->map(
                static fn ($value) => $value->getChars()
            )->getOrElse(null);
        }

        if ($key !== null) {
            if (!array_key_exists($key, $items)) {
                throw new ToolCallException("env key not found: {$key}");
            }
            return $items[$key];
        }

        return $items;
    }
}
