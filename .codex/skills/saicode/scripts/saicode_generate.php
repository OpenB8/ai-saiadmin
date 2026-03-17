#!/usr/bin/env php
<?php
/**
 * SaiCode CLI 代码生成脚本
 * 
 * 用法:
 *   php saicode_generate.php list                                          — 列出已装载的数据表
 *   php saicode_generate.php menus [--parent=ID]                            — 查看系统菜单树(确定 belong_menu_id)
 *   php saicode_generate.php load --table=表名 [--source=mysql]            — 装载数据表
 *   php saicode_generate.php config --id=表ID --namespace=xx --package=xx  — 配置生成选项
 *   php saicode_generate.php columns --id=表ID                             — 查看/修改字段配置
 *   php saicode_generate.php preview --id=表ID                             — 预览生成的代码
 *   php saicode_generate.php generate --id=表ID                            — 生成代码到模块
 *   php saicode_generate.php all --table=表名 --namespace=xx --package=xx  — 一键全流程
 * 
 * 注意: 必须在 server/ 目录下执行此脚本
 */

// ========== 引导 webman 框架环境 ==========
$serverDir = getenv('SERVER_DIR') ?: realpath(__DIR__ . '/../../../../server');
if (!$serverDir || !is_dir($serverDir)) {
    fwrite(STDERR, "错误: 找不到 server 目录，请在 server/ 目录下执行或设置 SERVER_DIR 环境变量\n");
    exit(1);
}

chdir($serverDir);
require_once $serverDir . '/vendor/autoload.php';
require_once $serverDir . '/support/bootstrap.php';

use plugin\saicode\app\logic\TableLogic;
use plugin\saicode\app\logic\DbLogic;
use plugin\saicode\app\model\Table;
use plugin\saicode\app\model\Column;
use support\think\Db;

// ========== 解析命令行参数 ==========
$args = parseArgs($argv);
$command = $args['command'] ?? '';

switch ($command) {
    case 'list':
        cmdList();
        break;
    case 'menus':
        cmdMenus($args);
        break;
    case 'load':
        cmdLoad($args);
        break;
    case 'config':
        cmdConfig($args);
        break;
    case 'columns':
        cmdColumns($args);
        break;
    case 'relation':
        cmdRelation($args);
        break;
    case 'preview':
        cmdPreview($args);
        break;
    case 'generate':
        cmdGenerate($args);
        break;
    case 'rollback':
        cmdRollback($args);
        break;
    case 'all':
        cmdAll($args);
        break;
    default:
        printUsage();
        break;
}

// ========== 命令实现 ==========

/**
 * 列出已装载的数据表
 */
function cmdList(): void
{
    $tables = Table::select()->toArray();
    if (empty($tables)) {
        echo "暂无已装载的数据表\n";
        return;
    }
    echo str_pad('ID', 6) . str_pad('表名', 30) . str_pad('类名', 25) . str_pad('命名空间', 20) . str_pad('模块', 15) . "备注\n";
    echo str_repeat('-', 110) . "\n";
    foreach ($tables as $t) {
        echo str_pad($t['id'], 6)
            . str_pad($t['table_name'], 30)
            . str_pad($t['class_name'], 25)
            . str_pad($t['namespace'] ?? '-', 20)
            . str_pad($t['package_name'] ?? '-', 15)
            . ($t['table_comment'] ?? '') . "\n";
    }
    echo "\n共 " . count($tables) . " 张表\n";
}

/**
 * 查看系统菜单树（用于确定 belong_menu_id）
 */
function cmdMenus(array $args): void
{
    $parentId = $args['parent'] ?? null;

    if ($parentId !== null) {
        // 显示指定父菜单的子菜单
        $menus = Db::table('sa_system_menu')
            ->where('parent_id', $parentId)
            ->whereNull('delete_time')
            ->where('type', 'in', [1, 2])  // 只显示目录和菜单
            ->order('sort', 'asc')
            ->select();
    } else {
        // 显示顶级菜单
        $menus = Db::table('sa_system_menu')
            ->where('parent_id', 0)
            ->whereNull('delete_time')
            ->where('type', 'in', [1, 2])
            ->order('sort', 'asc')
            ->select();
    }

    if (empty($menus)) {
        echo "无菜单数据" . ($parentId !== null ? " (parent_id={$parentId})" : '') . "\n";
        return;
    }

    $typeMap = [1 => '目录', 2 => '菜单', 3 => '权限'];
    echo str_pad('ID', 8) . str_pad('名称', 25) . str_pad('类型', 10) . str_pad('路径', 30) . "组件\n";
    echo str_repeat('-', 110) . "\n";
    foreach ($menus as $m) {
        echo str_pad($m['id'], 8)
            . str_pad($m['name'], 25)
            . str_pad($typeMap[$m['type']] ?? '-', 10)
            . str_pad($m['path'] ?? '-', 30)
            . ($m['component'] ?? '-') . "\n";

        // 显示子菜单
        $children = Db::table('sa_system_menu')
            ->where('parent_id', $m['id'])
            ->whereNull('delete_time')
            ->where('type', 'in', [1, 2])
            ->order('sort', 'asc')
            ->select();
        foreach ($children as $c) {
            echo '  ' . str_pad($c['id'], 6)
                . str_pad('└─ ' . $c['name'], 23)
                . str_pad($typeMap[$c['type']] ?? '-', 10)
                . str_pad($c['path'] ?? '-', 30)
                . ($c['component'] ?? '-') . "\n";
        }
    }
    echo "\n提示: 使用 --parent=ID 查看指定菜单的子菜单\n";
    echo "将目标菜单的 ID 用作 --belong_menu_id 参数\n";
}

/**
 * 装载数据表
 */
function cmdLoad(array $args): int
{
    $tableName = $args['table'] ?? '';
    $source = $args['source'] ?? 'mysql';

    if (empty($tableName)) {
        fwrite(STDERR, "错误: 缺少 --table 参数\n");
        return 1;
    }

    // 检查表是否已装载
    $existing = Table::where('table_name', $tableName)->findOrEmpty();
    if (!$existing->isEmpty()) {
        echo "表 {$tableName} 已存在 (ID: {$existing->id})，跳过装载\n";
        return (int)$existing->id;
    }

    $dbLogic = new DbLogic();
    // 检查表是否存在
    $tableInfo = null;
    if (!empty($source)) {
        $list = Db::connect($source)->query('show table status where name=:name', ['name' => $tableName]);
    } else {
        $list = Db::query('show table status where name=:name', ['name' => $tableName]);
    }
    if (empty($list)) {
        fwrite(STDERR, "错误: 数据表 {$tableName} 不存在于数据源 {$source}\n");
        return 1;
    }
    $tableInfo = [
        'name' => $list[0]['Name'],
        'comment' => $list[0]['Comment'],
    ];

    $logic = new TableLogic();
    $logic->loadTable([$tableInfo], $source);

    // 获取刚装载的表ID
    $loaded = Table::where('table_name', $tableName)->findOrEmpty();
    echo "✅ 成功装载表: {$tableName} (ID: {$loaded->id})\n";
    return (int)$loaded->id;
}

/**
 * 配置生成选项
 */
function cmdConfig(array $args): void
{
    $id = $args['id'] ?? '';
    if (empty($id)) {
        fwrite(STDERR, "错误: 缺少 --id 参数\n");
        exit(1);
    }

    $table = Table::findOrEmpty($id);
    if ($table->isEmpty()) {
        fwrite(STDERR, "错误: 未找到 ID={$id} 的表记录\n");
        exit(1);
    }

    $updateData = [];
    $configFields = [
        'namespace'      => '应用名称',
        'package_name'   => '功能模块',
        'template'       => '应用类型(app/plugin)',
        'tpl_category'   => '模板类型(single/tree)',
        'generate_menus' => '生成功能',
        'generate_path'  => '前端项目目录',
        'belong_menu_id' => '上级菜单ID',
        'menu_name'      => '菜单名称',
        'business_name'  => '业务名称',
        'component_type' => '组件类型(1=弹窗/2=抽屉/3=标签页)',
        'form_width'     => '表单弹窗宽度',
        'is_full'        => '是否全屏(1=否/2=是)',
        'span'           => '表单栅格宽度(1-24)',
    ];

    // 处理 package 缩写参数
    if (isset($args['package'])) {
        $args['package_name'] = $args['package'];
    }
    // 处理 menu 缩写参数
    if (isset($args['menu'])) {
        $args['belong_menu_id'] = $args['menu'];
    }

    foreach ($configFields as $field => $label) {
        if (isset($args[$field]) && $args[$field] !== '') {
            $updateData[$field] = $args[$field];
        }
    }

    if (empty($updateData)) {
        echo "当前配置:\n";
        foreach ($configFields as $field => $label) {
            echo "  {$label} ({$field}): " . ($table->$field ?? '-') . "\n";
        }
        echo "\n使用 --字段名=值 修改配置\n";
        return;
    }

    Table::where('id', $id)->update($updateData);
    echo "✅ 配置已更新:\n";
    foreach ($updateData as $field => $value) {
        $label = $configFields[$field] ?? $field;
        echo "  {$label}: {$value}\n";
    }
}

/**
 * 预览生成的代码
 */
function cmdPreview(array $args): void
{
    $id = $args['id'] ?? '';
    if (empty($id)) {
        fwrite(STDERR, "错误: 缺少 --id 参数\n");
        exit(1);
    }

    $logic = new TableLogic();
    $data = $logic->preview($id);

    foreach ($data as $item) {
        echo "\n" . str_repeat('=', 60) . "\n";
        echo "📄 {$item['tab_name']}\n";
        echo str_repeat('=', 60) . "\n";
        echo $item['code'] . "\n";
    }
}

/**
 * 查看/修改已装载表的字段配置
 */
function cmdColumns(array $args): void
{
    $id = $args['id'] ?? '';
    if (empty($id)) {
        fwrite(STDERR, "错误: 缺少 --id 参数 (saicode_table 的 ID)\n");
        exit(1);
    }

    $columns = Column::where('table_id', $id)
        ->order('list_sort', 'asc')
        ->select()
        ->toArray();

    if (empty($columns)) {
        echo "未找到 table_id={$id} 的字段信息\n";
        return;
    }

    // 如果有 --set 参数，批量修改字段属性
    // 格式: --set=column_name:属性=值,属性=值
    if (isset($args['set'])) {
        $setParts = explode(':', $args['set'], 2);
        if (count($setParts) !== 2) {
            fwrite(STDERR, "错误: --set 格式为 字段名:属性=值,属性=值\n");
            fwrite(STDERR, "示例: --set=status:view_type=select,dict_type=data_status\n");
            exit(1);
        }
        $colName = $setParts[0];
        $attrs = [];
        foreach (explode(',', $setParts[1]) as $pair) {
            $kv = explode('=', $pair, 2);
            if (count($kv) === 2) {
                $attrs[$kv[0]] = $kv[1];
            }
        }
        $col = Column::where('table_id', $id)->where('column_name', $colName)->findOrEmpty();
        if ($col->isEmpty()) {
            fwrite(STDERR, "错误: 未找到字段 {$colName}\n");
            exit(1);
        }
        Column::where('id', $col->id)->update($attrs);
        echo "✅ 字段 {$colName} 已更新: " . json_encode($attrs, JSON_UNESCAPED_UNICODE) . "\n";
        return;
    }

    // 显示字段列表
    echo "表 ID={$id} 的字段配置:\n\n";
    echo str_pad('字段名', 22) . str_pad('类型', 12) . str_pad('注释', 18)
        . str_pad('视图组件', 15) . str_pad('字典', 14)
        . str_pad('新增', 5) . str_pad('编辑', 5) . str_pad('列表', 5)
        . str_pad('搜索', 5) . str_pad('必填', 5) . "查询方式\n";
    echo str_repeat('-', 130) . "\n";

    $yn = function ($v) {
        return $v == 2 ? '✓' : '-';
    };
    foreach ($columns as $c) {
        echo str_pad($c['column_name'], 22)
            . str_pad($c['column_type'], 12)
            . str_pad(mb_substr($c['column_comment'] ?? '', 0, 8), 18)
            . str_pad($c['view_type'] ?? 'input', 15)
            . str_pad($c['dict_type'] ?? '-', 14)
            . str_pad($yn($c['is_insert']), 5)
            . str_pad($yn($c['is_edit']), 5)
            . str_pad($yn($c['is_list']), 5)
            . str_pad($yn($c['is_query']), 5)
            . str_pad($yn($c['is_required']), 5)
            . ($c['query_type'] ?? '-') . "\n";
    }
    echo "\n可修改属性: view_type, dict_type, is_insert(1/2), is_edit(1/2), is_list(1/2), is_query(1/2), is_required(1/2), query_type\n";
    echo "修改示例: --set=status:view_type=select,dict_type=data_status\n";
}

/**
 * 查看/添加模型关联配置
 */
function cmdRelation(array $args): void
{
    $id = $args['id'] ?? '';
    if (empty($id)) {
        fwrite(STDERR, "错误: 缺少 --id 参数\n");
        exit(1);
    }

    $table = Table::findOrEmpty($id);
    if ($table->isEmpty()) {
        fwrite(STDERR, "错误: 未找到 ID={$id} 的表记录\n");
        exit(1);
    }

    $options = $table->options ? (is_string($table->options) ? json_decode($table->options, true) : $table->options) : [];
    $relations = $options['relations'] ?? [];

    // 添加关联: --add --type=belongsTo --name=category --model=CategoryModel --localKey=category_id --foreignKey=id
    if (isset($args['add'])) {
        $type = $args['type'] ?? '';
        $name = $args['name'] ?? '';
        $model = $args['model'] ?? '';
        $localKey = $args['localKey'] ?? $args['local_key'] ?? '';
        $foreignKey = $args['foreignKey'] ?? $args['foreign_key'] ?? '';
        $midTable = $args['table'] ?? $args['mid_table'] ?? '';

        if (empty($type) || empty($name) || empty($model)) {
            fwrite(STDERR, "错误: --add 需要 --type, --name, --model 参数\n");
            fwrite(STDERR, "示例: --add --type=belongsTo --name=category --model=app\\model\\Category --localKey=category_id --foreignKey=id\n");
            exit(1);
        }

        $validTypes = ['hasOne', 'hasMany', 'belongsTo', 'belongsToMany'];
        if (!in_array($type, $validTypes)) {
            fwrite(STDERR, "错误: type 必须是 " . implode('/', $validTypes) . "\n");
            exit(1);
        }

        $relations[] = [
            'type' => $type,
            'name' => $name,
            'model' => $model,
            'localKey' => $localKey,
            'foreignKey' => $foreignKey,
            'table' => $midTable,
        ];
        $options['relations'] = $relations;
        Table::where('id', $id)->update(['options' => json_encode($options, JSON_UNESCAPED_UNICODE)]);
        echo "✅ 添加关联成功: {$name} ({$type}) -> {$model}\n";
        return;
    }

    // 删除关联: --del=name
    if (isset($args['del'])) {
        $delName = $args['del'];
        $newRelations = array_values(array_filter($relations, fn($r) => $r['name'] !== $delName));
        $options['relations'] = $newRelations;
        Table::where('id', $id)->update(['options' => json_encode($options, JSON_UNESCAPED_UNICODE)]);
        echo "✅ 已删除关联: {$delName}\n";
        return;
    }

    // 显示关联列表
    if (empty($relations)) {
        echo "表 ID={$id} 暂无关联配置\n";
        echo "\n添加示例: --add --type=belongsTo --name=category --model=CategoryModel --localKey=category_id --foreignKey=id\n";
        return;
    }

    echo "表 ID={$id} 的关联配置:\n\n";
    echo str_pad('名称', 18) . str_pad('类型', 18) . str_pad('关联模型', 35) . str_pad('本地键', 18) . str_pad('外键', 18) . "中间表\n";
    echo str_repeat('-', 130) . "\n";
    foreach ($relations as $r) {
        echo str_pad($r['name'] ?? '-', 18)
            . str_pad($r['type'] ?? '-', 18)
            . str_pad($r['model'] ?? '-', 35)
            . str_pad($r['localKey'] ?? '-', 18)
            . str_pad($r['foreignKey'] ?? '-', 18)
            . ($r['table'] ?? '-') . "\n";
    }
    echo "\n删除关联: --del=关联名称\n";
}

/**
 * 撤回生成：删除生成的文件 + 菜单权限
 */
function cmdRollback(array $args): void
{
    $id = $args['id'] ?? '';
    if (empty($id)) {
        fwrite(STDERR, "错误: 缺少 --id 参数\n");
        exit(1);
    }

    $table = Table::findOrEmpty($id);
    if ($table->isEmpty()) {
        fwrite(STDERR, "错误: 未找到 ID={$id} 的表记录\n");
        exit(1);
    }

    $ns = $table->namespace;
    $pkg = $table->package_name;
    $cls = $table->class_name;
    $biz = $table->business_name;
    $tpl = $table->template;
    $genPath = $table->generate_path ?: 'saiadmin-artd';

    if (empty($ns) || empty($pkg) || empty($cls)) {
        fwrite(STDERR, "错误: 表配置不完整，无法确定生成路径\n");
        exit(1);
    }

    $DS = DIRECTORY_SEPARATOR;
    $serverDir = base_path();
    $projectDir = dirname($serverDir);

    // === 后端文件 ===
    if ($tpl === 'plugin') {
        $rootPath = $serverDir . $DS . 'plugin' . $DS . $ns . $DS . 'app';
        $adminPath = $DS . 'admin';
    } else {
        $rootPath = $serverDir . $DS . 'app' . $DS . $ns;
        $adminPath = '';
    }
    $subPath = $DS . $pkg;

    $backendFiles = [
        $rootPath . $adminPath . $DS . 'controller' . $subPath . $DS . $cls . 'Controller.php',
        $rootPath . $adminPath . $DS . 'logic' . $subPath . $DS . $cls . 'Logic.php',
        $rootPath . $adminPath . $DS . 'validate' . $subPath . $DS . $cls . 'Validate.php',
        $rootPath . $DS . 'model' . $subPath . $DS . $cls . '.php',
    ];

    // === 前端文件 ===
    $frontRoot = $projectDir . $DS . $genPath . $DS . 'src' . $DS . 'views' . $DS . 'plugin' . $DS . $ns;
    $frontendFiles = [
        $frontRoot . $subPath . $DS . $biz . $DS . 'index.vue',
        $frontRoot . $subPath . $DS . $biz . $DS . 'modules' . $DS . 'edit-dialog.vue',
        $frontRoot . $subPath . $DS . $biz . $DS . 'modules' . $DS . 'table-search.vue',
        $frontRoot . $subPath . $DS . $biz . $DS . 'modules' . $DS . 'view-dialog.vue',
        $frontRoot . $DS . 'api' . $subPath . $DS . $biz . '.ts',
    ];

    $allFiles = array_merge($backendFiles, $frontendFiles);

    echo "将要撤回表 [{$table->table_name}] (ID={$id}) 的生成结果:\n\n";

    // 删除文件
    $deletedCount = 0;
    $skippedCount = 0;
    foreach ($allFiles as $file) {
        $rel = str_replace($projectDir . $DS, '', $file);
        if (file_exists($file)) {
            unlink($file);
            echo "✅ 删除: {$rel}\n";
            $deletedCount++;
        } else {
            echo "⏭️  跳过(不存在): {$rel}\n";
            $skippedCount++;
        }
    }

    // 清理空目录
    $dirsToCheck = [
        $frontRoot . $subPath . $DS . $biz . $DS . 'modules',
        $frontRoot . $subPath . $DS . $biz,
        $rootPath . $adminPath . $DS . 'controller' . $subPath,
        $rootPath . $adminPath . $DS . 'logic' . $subPath,
        $rootPath . $adminPath . $DS . 'validate' . $subPath,
        $rootPath . $DS . 'model' . $subPath,
    ];
    foreach ($dirsToCheck as $dir) {
        if (is_dir($dir) && count(scandir($dir)) === 2) { // 只有 . 和 ..
            rmdir($dir);
        }
    }

    // === 删除菜单权限 ===
    $parentMenu = Db::table('sa_system_menu')
        ->where('generate_id', $id)
        ->whereNull('delete_time')
        ->findOrEmpty();

    $menuCount = 0;
    if (!empty($parentMenu)) {
        // 先删子菜单(权限按钮)
        $childCount = Db::table('sa_system_menu')
            ->where('parent_id', $parentMenu['id'])
            ->delete();
        // 再删父菜单
        Db::table('sa_system_menu')
            ->where('id', $parentMenu['id'])
            ->delete();
        $menuCount = $childCount + 1;
        echo "\n✅ 删除菜单权限: {$menuCount} 条\n";
    } else {
        echo "\n⏭️  无关联菜单记录\n";
    }

    // === 可选: 清理 saicode 记录 ===
    if (isset($args['clean'])) {
        Column::where('table_id', $id)->delete();
        Table::where('id', $id)->delete();
        echo "✅ 已清理 saicode_table 和 saicode_column 记录\n";
    }

    echo "\n🗑️  撤回完成! 删除文件: {$deletedCount}, 跳过: {$skippedCount}, 菜单: {$menuCount}\n";
    echo "提示: 请重启 webman 服务使路由变更生效\n";
}

/**
 * 生成代码到模块
 */
function cmdGenerate(array $args): void
{
    $id = $args['id'] ?? '';
    if (empty($id)) {
        fwrite(STDERR, "错误: 缺少 --id 参数\n");
        exit(1);
    }

    // 检查必要配置
    $table = Table::findOrEmpty($id);
    if ($table->isEmpty()) {
        fwrite(STDERR, "错误: 未找到 ID={$id} 的表记录\n");
        exit(1);
    }
    if (empty($table->namespace)) {
        fwrite(STDERR, "错误: 请先配置 namespace (应用名称)\n");
        exit(1);
    }
    if (empty($table->package_name)) {
        fwrite(STDERR, "错误: 请先配置 package_name (功能模块)\n");
        exit(1);
    }

    $logic = new TableLogic();
    $logic->generateFile($id);

    echo "✅ 代码生成成功!\n";
    echo "  表名: {$table->table_name}\n";
    echo "  类名: {$table->class_name}\n";

    if ($table->template === 'plugin') {
        echo "  后端路径: plugin/{$table->namespace}/app/admin/...\n";
    } else {
        echo "  后端路径: app/{$table->namespace}/...\n";
    }
    $generatePath = $table->generate_path ?: 'saiadmin-artd';
    echo "  前端路径: {$generatePath}/src/views/plugin/{$table->namespace}/...\n";
    echo "  菜单权限: 已自动创建\n";
}

/**
 * 一键全流程: load + config + generate
 */
function cmdAll(array $args): void
{
    $tableName = $args['table'] ?? '';
    if (empty($tableName)) {
        fwrite(STDERR, "错误: 缺少 --table 参数\n");
        exit(1);
    }

    // 必须有 namespace 和 package_name
    $namespace = $args['namespace'] ?? '';
    $packageName = $args['package_name'] ?? $args['package'] ?? '';
    if (empty($namespace)) {
        fwrite(STDERR, "错误: 缺少 --namespace 参数\n");
        exit(1);
    }
    if (empty($packageName)) {
        fwrite(STDERR, "错误: 缺少 --package 参数\n");
        exit(1);
    }

    $source = $args['source'] ?? 'mysql';

    // Step 1: Load
    echo ">>> 步骤 1/3: 装载数据表 {$tableName}\n";
    $id = cmdLoad($args);
    if ($id <= 0) {
        exit(1);
    }

    // Step 2: Config
    echo "\n>>> 步骤 2/3: 配置生成选项\n";
    $args['id'] = $id;
    $args['package_name'] = $packageName;
    cmdConfig($args);

    // Step 3: Generate
    echo "\n>>> 步骤 3/3: 生成代码到模块\n";
    cmdGenerate($args);

    echo "\n🎉 全流程完成!\n";
}

// ========== 工具函数 ==========

function parseArgs(array $argv): array
{
    $result = ['command' => $argv[1] ?? ''];
    for ($i = 2; $i < count($argv); $i++) {
        $arg = $argv[$i];
        if (str_starts_with($arg, '--')) {
            $parts = explode('=', substr($arg, 2), 2);
            $key = $parts[0];
            $value = $parts[1] ?? true;
            $result[$key] = $value;
        }
    }
    return $result;
}

function printUsage(): void
{
    echo <<<USAGE
SaiCode CLI 代码生成脚本

用法:
  php saicode_generate.php <command> [options]

命令:
  list                              列出已装载的数据表
  menus   [--parent=ID]             查看系统菜单树(确定 belong_menu_id)
  load    --table=表名              装载数据表到 saicode 系统
  config  --id=表ID                 查看/修改生成配置
  columns --id=表ID                 查看已装载表的字段配置
  columns --id=表ID --set=字段:k=v  修改字段属性
  relation --id=表ID                查看关联配置
  relation --id=表ID --add ...      添加模型关联
  relation --id=表ID --del=名称     删除模型关联
  preview --id=表ID                 预览生成的代码
  generate --id=表ID                生成代码到模块(文件+菜单)
  rollback --id=表ID                撤回生成(删除文件+菜单)
  rollback --id=表ID --clean        撤回并清理 saicode 记录
  all     --table=表名 ...          一键全流程(load+config+generate)

通用选项:
  --source=mysql                    数据源名称 (默认: mysql)
  --namespace=saiadmin              应用名称
  --package=system                  功能模块分组
  --template=plugin                 应用类型: app 或 plugin (默认: app)
  --generate_path=saiadmin-artd     前端项目目录名 (默认: saiadmin-artd)
  --belong_menu_id=80               上级菜单ID (默认: 80)
  --generate_menus=index,save,...   生成的功能列表
  --tpl_category=single             模板类型: single 或 tree
  --component_type=1                组件类型: 1=弹窗 2=抽屉 3=标签页
  --form_width=600                  表单弹窗宽度(px)
  --menu_name=菜单名                菜单显示名称(默认用表注释)

示例:
  # 先查看菜单树，确定上级菜单ID
  php saicode_generate.php menus
  php saicode_generate.php menus --parent=1003

  # 一键为 sa_member 表生成 saiuser 插件的 member 模块代码
  php saicode_generate.php all --table=sa_member --namespace=saiuser --package=member --template=plugin --belong_menu_id=1003

  # 分步操作
  php saicode_generate.php load --table=sa_member
  php saicode_generate.php config --id=1 --namespace=saiuser --package=member --template=plugin
  php saicode_generate.php columns --id=1
  php saicode_generate.php columns --id=1 --set=status:view_type=select,dict_type=data_status
  php saicode_generate.php relation --id=1 --add --type=belongsTo --name=level --model=MemberLevel --localKey=member_level_id --foreignKey=id
  php saicode_generate.php generate --id=1

USAGE;
}
