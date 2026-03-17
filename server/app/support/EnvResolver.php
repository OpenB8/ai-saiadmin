<?php

namespace app\support;

use Dotenv\Parser\Parser;

class EnvResolver
{
    /**
     * 统一读取环境变量。
     *
     * 优先级：
     * 1. 当前进程已加载的 getenv()
     * 2. server/.env 文件中的原始值
     * 3. 调用方传入的默认值
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        $value = getenv($key);
        if ($value !== false) {
            return static::cast($value, $default);
        }

        $items = static::loadEnvFile();
        if (array_key_exists($key, $items)) {
            return static::cast($items[$key], $default);
        }

        return $default;
    }

    /**
     * @return array<string, string|null>
     */
    private static function loadEnvFile(): array
    {
        static $items = null;
        if (is_array($items)) {
            return $items;
        }

        $envFile = base_path(false) . '/.env';
        if (!is_file($envFile)) {
            return $items = [];
        }

        $content = file_get_contents($envFile);
        if ($content === false) {
            return $items = [];
        }

        $entries = (new Parser())->parse($content);
        $items = [];
        foreach ($entries as $entry) {
            $items[$entry->getName()] = $entry->getValue()->map(
                static fn ($value) => $value->getChars()
            )->getOrElse(null);
        }

        return $items;
    }

    private static function cast(string|null $value, mixed $default): mixed
    {
        if ($value === null) {
            return null;
        }

        if (is_int($default)) {
            return (int) $value;
        }

        if (is_float($default)) {
            return (float) $value;
        }

        if (is_bool($default)) {
            return filter_var($value, FILTER_VALIDATE_BOOL);
        }

        return $value;
    }
}
