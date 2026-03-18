<?php

namespace plugin\saiai\app\tool;

use Symfony\AI\Agent\Toolbox\Attribute\AsTool;

/**
 * 文档工具 - 扫描 saiadmin 框架获取用法和知识库
 */
#[AsTool(name: 'get_framework_structure', description: 'Get the directory structure of the saiadmin framework, showing all modules and components.', method: 'getFrameworkStructure')]
#[AsTool(name: 'get_base_classes', description: 'Get information about base classes (BaseController, AbstractLogic, etc.) and their methods.', method: 'getBaseClasses')]
#[AsTool(name: 'get_helper_functions', description: 'Get all helper functions and utilities available in the framework.', method: 'getHelperFunctions')]
#[AsTool(name: 'get_controller_list', description: 'Get list of all controllers in the saiadmin plugin with their descriptions.', method: 'getControllerList')]
#[AsTool(name: 'get_model_list', description: 'Get list of all models in the saiadmin plugin with their table associations.', method: 'getModelList')]
#[AsTool(name: 'get_logic_list', description: 'Get list of all logic classes in the saiadmin plugin.', method: 'getLogicList')]
#[AsTool(name: 'get_class_source', description: 'Get the source code of a specific class file from saiadmin plugin.', method: 'getClassSource')]
#[AsTool(name: 'search_framework_code', description: 'Search for code patterns in the saiadmin framework.', method: 'searchFrameworkCode')]
class DocTool
{
    /**
     * saiadmin 插件路径
     */
    private string $basePath;

    public function __construct()
    {
        $this->basePath = base_path('plugin/saiadmin');
    }

    /**
     * 获取框架目录结构
     */
    public function getFrameworkStructure(): string
    {
        $structure = [];
        $structure[] = "# SaiAdmin Framework Structure\n";

        $dirs = [
            'app' => '应用核心目录',
            'app/controller' => '控制器目录 - 处理HTTP请求',
            'app/model' => '模型目录 - 数据库模型定义',
            'app/logic' => '逻辑层目录 - 业务逻辑处理',
            'app/validate' => '验证器目录 - 数据验证规则',
            'app/middleware' => '中间件目录 - 请求拦截处理',
            'app/cache' => '缓存目录 - 缓存相关',
            'basic' => '基类目录 - 核心基类定义',
            'utils' => '工具类目录 - 帮助函数和工具',
            'config' => '配置目录 - 框架配置',
            'exception' => '异常目录 - 自定义异常',
            'service' => '服务目录 - 服务提供者',
        ];

        $structure[] = "| Directory | Description | Files |";
        $structure[] = "| :--- | :--- | :--- |";

        foreach ($dirs as $dir => $desc) {
            $fullPath = $this->basePath . '/' . $dir;
            if (is_dir($fullPath)) {
                $count = $this->countFiles($fullPath);
                $structure[] = "| {$dir} | {$desc} | {$count} |";
            }
        }

        return implode("\n", $structure);
    }

    /**
     * 获取基类信息
     */
    public function getBaseClasses(): string
    {
        $output = [];
        $output[] = "# SaiAdmin Base Classes\n";

        $baseClasses = [
            'basic/BaseController.php' => [
                'desc' => '控制器基类 - 所有控制器继承此类',
                'features' => [
                    '自动注入登录用户信息 ($adminInfo, $adminId, $adminName)',
                    '逻辑层注入 ($logic) - 自动传递用户信息',
                    '验证器注入 ($validate) - 快速数据验证',
                    'validate($scene, $data) - 执行场景验证',
                ]
            ],
            'basic/OpenController.php' => [
                'desc' => '开放控制器基类 - 无需登录验证',
                'features' => [
                    '用于公开接口',
                    '不进行登录检查',
                ]
            ],
            'basic/AbstractLogic.php' => [
                'desc' => '逻辑层抽象基类 - 业务逻辑处理',
                'features' => [
                    '模型注入 ($model) - 自动数据库操作代理',
                    '管理员信息 ($adminInfo) - 获取当前操作用户',
                    'setOrderField($field) - 设置排序字段',
                    'setOrderType($type) - 设置排序方式 ASC/DESC',
                    'getModel() - 获取模型实例',
                    'getImport($file) - 处理上传的导入文件',
                    '__call() - 方法自动代理到模型',
                ]
            ],
            'basic/BaseValidate.php' => [
                'desc' => '验证器基类 - 数据验证',
                'features' => [
                    '定义验证规则 ($rule)',
                    '定义错误消息 ($message)',
                    '定义验证场景 ($scene)',
                    'scene($name) - 切换验证场景',
                ]
            ],
        ];

        foreach ($baseClasses as $file => $info) {
            $output[] = "## " . basename($file, '.php');
            $output[] = "**路径**: `{$file}`\n";
            $output[] = "**说明**: {$info['desc']}\n";
            $output[] = "**特性**:";
            foreach ($info['features'] as $feature) {
                $output[] = "- {$feature}";
            }
            $output[] = "";
        }

        return implode("\n", $output);
    }

    /**
     * 获取帮助函数
     */
    public function getHelperFunctions(): string
    {
        $output = [];
        $output[] = "# SaiAdmin Helper Functions\n";

        // Helper 类
        $output[] = "## Helper 类 (plugin\\saiadmin\\utils\\Helper)\n";
        $output[] = "| Method | Description |";
        $output[] = "| :--- | :--- |";
        $output[] = "| makeTree(array \$data) | 将平铺数据转为树形结构 |";
        $output[] = "| makeArcoMenus(array \$data) | 生成 Arco Design 菜单格式 |";
        $output[] = "| makeArtdMenus(array \$data) | 生成 Artd 菜单格式 |";
        $output[] = "| camelize(\$str) | 下划线转驼峰命名 |";
        $output[] = "| uncamelize(\$str) | 驼峰转下划线命名 |";
        $output[] = "| camel(string \$value) | 转换为大驼峰 |";
        $output[] = "| get_business(\$tableName) | 从表名获取业务名(小驼峰) |";
        $output[] = "| get_big_business(\$tableName) | 从表名获取业务名(大驼峰) |";
        $output[] = "| str_replace_once() | 只替换一次字符串 |";
        $output[] = "| get_dir(\$path) | 遍历目录获取文件列表 |";
        $output[] = "";

        // 全局函数
        $functionsFile = $this->basePath . '/app/functions.php';
        if (file_exists($functionsFile)) {
            $output[] = "## Global Functions (functions.php)\n";
            $content = file_get_contents($functionsFile);
            preg_match_all('/function\s+(\w+)\s*\([^)]*\)/', $content, $matches);
            if (!empty($matches[1])) {
                $output[] = "| Function | Status |";
                $output[] = "| :--- | :--- |";
                foreach ($matches[1] as $func) {
                    $output[] = "| {$func}() | Available |";
                }
            }
        }

        return implode("\n", $output);
    }

    /**
     * 获取控制器列表
     */
    public function getControllerList(): string
    {
        $controllerPath = $this->basePath . '/app/controller';
        return $this->scanPhpFiles($controllerPath, 'Controllers');
    }

    /**
     * 获取模型列表
     */
    public function getModelList(): string
    {
        $modelPath = $this->basePath . '/app/model';
        return $this->scanPhpFiles($modelPath, 'Models');
    }

    /**
     * 获取逻辑层列表
     */
    public function getLogicList(): string
    {
        $logicPath = $this->basePath . '/app/logic';
        return $this->scanPhpFiles($logicPath, 'Logic Classes');
    }

    /**
     * 获取类源码
     */
    public function getClassSource(string $className): string
    {
        // 支持多种格式: UserController, controller/UserController, app/controller/UserController.php
        $searchPaths = [
            'app/controller',
            'app/model',
            'app/logic',
            'app/validate',
            'app/middleware',
            'app/cache',
            'basic',
            'utils',
        ];

        // 清理类名
        $className = str_replace(['\\', '/'], '/', $className);
        $className = preg_replace('/\.php$/i', '', $className);

        foreach ($searchPaths as $path) {
            $fullPath = $this->basePath . '/' . $path . '/' . basename($className) . '.php';
            if (file_exists($fullPath)) {
                $content = file_get_contents($fullPath);
                $lines = count(explode("\n", $content));
                return "# Source: {$path}/" . basename($className) . ".php\n" .
                    "Lines: {$lines}\n\n" .
                    "```php\n{$content}\n```";
            }
        }

        // 尝试直接路径
        $directPath = $this->basePath . '/' . $className . '.php';
        if (file_exists($directPath)) {
            $content = file_get_contents($directPath);
            return "```php\n{$content}\n```";
        }

        return "Class '{$className}' not found. Available paths:\n" .
            implode("\n", array_map(fn($p) => "- plugin/saiadmin/{$p}/", $searchPaths));
    }

    /**
     * 搜索框架代码
     */
    public function searchFrameworkCode(string $pattern): string
    {
        $output = [];
        $output[] = "# Search Results for: `{$pattern}`\n";

        $results = [];
        $this->searchInDirectory($this->basePath, $pattern, $results);

        if (empty($results)) {
            return "No matches found for pattern: {$pattern}";
        }

        $output[] = "Found " . count($results) . " matches:\n";

        $groupedResults = [];
        foreach ($results as $result) {
            $relPath = str_replace($this->basePath . '/', '', $result['file']);
            $dir = dirname($relPath);
            $groupedResults[$dir][] = $result;
        }

        foreach ($groupedResults as $dir => $items) {
            $output[] = "## {$dir}\n";
            foreach ($items as $item) {
                $filename = basename($item['file']);
                $output[] = "### {$filename}:L{$item['line']}";
                $output[] = "```php";
                $output[] = trim($item['content']);
                $output[] = "```";
                $output[] = "";
            }
        }

        return implode("\n", $output);
    }

    // ==================== Private Helper Methods ====================

    /**
     * 统计目录文件数量
     */
    private function countFiles(string $dir): int
    {
        $count = 0;
        if (is_dir($dir)) {
            $files = scandir($dir);
            foreach ($files as $file) {
                if ($file !== '.' && $file !== '..') {
                    $path = $dir . '/' . $file;
                    if (is_file($path) && pathinfo($path, PATHINFO_EXTENSION) === 'php') {
                        $count++;
                    } elseif (is_dir($path)) {
                        $count += $this->countFiles($path);
                    }
                }
            }
        }
        return $count;
    }

    /**
     * 扫描PHP文件
     */
    private function scanPhpFiles(string $path, string $title): string
    {
        $output = [];
        $output[] = "# SaiAdmin {$title}\n";
        $output[] = "| File | Class | Description |";
        $output[] = "| :--- | :--- | :--- |";

        if (!is_dir($path)) {
            return "Directory not found: {$path}";
        }

        $files = scandir($path);
        foreach ($files as $file) {
            if ($file === '.' || $file === '..')
                continue;

            $fullPath = $path . '/' . $file;
            if (is_file($fullPath) && pathinfo($file, PATHINFO_EXTENSION) === 'php') {
                $className = basename($file, '.php');
                $desc = $this->extractClassDescription($fullPath);
                $output[] = "| {$file} | {$className} | {$desc} |";
            }
        }

        return implode("\n", $output);
    }

    /**
     * 提取类描述
     */
    private function extractClassDescription(string $file): string
    {
        $content = file_get_contents($file);

        // 尝试从类注释中提取描述
        if (preg_match('/\/\*\*\s*\n\s*\*\s*(.+?)\s*\n/', $content, $matches)) {
            return trim($matches[1]);
        }

        // 尝试从单行注释中提取
        if (preg_match('/\/\/\s*(.+?)\s*\n.*?class\s+\w+/s', $content, $matches)) {
            return trim($matches[1]);
        }

        return '-';
    }

    /**
     * 在目录中搜索代码
     */
    private function searchInDirectory(string $dir, string $pattern, array &$results, int $maxResults = 20): void
    {
        if (count($results) >= $maxResults)
            return;

        if (!is_dir($dir))
            return;

        $files = scandir($dir);
        foreach ($files as $file) {
            if (count($results) >= $maxResults)
                return;
            if ($file === '.' || $file === '..')
                continue;

            $fullPath = $dir . '/' . $file;

            if (is_dir($fullPath)) {
                $this->searchInDirectory($fullPath, $pattern, $results, $maxResults);
            } elseif (is_file($fullPath) && pathinfo($file, PATHINFO_EXTENSION) === 'php') {
                $content = file_get_contents($fullPath);
                $lines = explode("\n", $content);

                foreach ($lines as $lineNum => $lineContent) {
                    if (count($results) >= $maxResults)
                        return;
                    if (stripos($lineContent, $pattern) !== false) {
                        $results[] = [
                            'file' => $fullPath,
                            'line' => $lineNum + 1,
                            'content' => $lineContent,
                        ];
                    }
                }
            }
        }
    }
}
