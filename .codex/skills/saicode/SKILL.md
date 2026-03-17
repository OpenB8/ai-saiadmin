---
name: SaiCode 低代码生成器
description: SaiAdmin 低代码生成插件使用指南，基于数据库表自动生成前后端 CRUD 代码，包含代码生成流程、模板定制和生成文件结构说明
---

# SaiCode 低代码生成器

SaiCode 是 SaiAdmin 框架的低代码生成插件，能根据数据库表结构自动生成完整的前后端 CRUD 代码。

## 插件目录结构

```
server/plugin/saicode/
├── app/
│   ├── controller/
│   │   └── TableController.php      # 低代码控制器（API 入口）
│   ├── logic/
│   │   ├── TableLogic.php            # 核心业务逻辑（装载/同步/预览/生成/菜单部署）
│   │   ├── ColumnLogic.php           # 字段处理逻辑（自动推断视图类型）
│   │   └── DbLogic.php              # 数据库表/列信息查询
│   ├── model/
│   │   ├── Table.php                 # 代码生成表模型
│   │   └── Column.php               # 代码生成字段模型
│   └── validate/
│       └── TableValidate.php         # 数据验证
├── config/                           # 插件配置
└── utils/code/
    ├── CodeEngine.php                # Twig 模板引擎代码生成器
    ├── CodeZip.php                   # ZIP 打包下载
    └── stub/saiadmin/                # 代码模板
        ├── php/                      # 后端模板
        │   ├── controller.stub
        │   ├── logic.stub
        │   ├── model.stub
        │   └── validate.stub
        ├── sql/
        │   └── sql.stub
        ├── vue/                      # 前端模板
        │   ├── index.stub
        │   ├── edit-dialog.stub
        │   ├── view-dialog.stub
        │   └── table-search.stub
        └── ts/
            └── api.stub
```

## 代码生成流程

### 1. 装载数据表

通过管理后台选择数据库数据源和表名，系统会读取表结构和字段信息，存入 `saicode_table` 和 `saicode_column` 两张表：

```
选择数据源 → 选择数据表 → 装载表信息 → 自动解析字段
```

装载时自动处理：

- 移除表前缀，生成 PascalCase 的 `class_name`
- 根据字段名自动推断视图类型（如 `image` → `uploadImage`，`status` → `radio`）
- 根据字段类型设置查询方式（如 `datetime` → `between`）

### 2. 编辑生成信息

管理后台的编辑弹窗包含 **4 个 Tab**：

#### Tab 1：配置信息

**基础信息**：

| 字段            | 说明           | 备注                       |
| --------------- | -------------- | -------------------------- |
| `table_name`    | 表名称（只读） | 自动读取                   |
| `table_comment` | 表描述         | 自动读取表注释，可修改     |
| `class_name`    | 实体类名       | PascalCase，可修改去掉前缀 |
| `business_name` | 业务名称       | 英文，同一包下唯一         |
| `source`        | 数据源         | 数据库配置中的连接名       |

**生成信息**：

| 字段             | 说明           | 可选值                                         |
| ---------------- | -------------- | ---------------------------------------------- |
| `template`       | 应用类型       | `app`（应用目录）/ `plugin`（插件目录）        |
| `namespace`      | 应用名称       | 如 `saiuser`、`helpsupport`（禁用 `saiadmin`） |
| `package_name`   | 包名(功能模块) | 如 `system`、`member`                          |
| `tpl_category`   | 生成类型       | `single`（单表 CRUD）/ `tree`（树表 CRUD）     |
| `generate_path`  | 前端生成路径   | 前端根目录文件夹名，须与后端同级               |
| `stub`           | 模型类型       | `think`（ThinkORM）/ `eloquent`（EloquentORM） |
| `belong_menu_id` | 所属菜单       | 级联选择系统菜单                               |
| `menu_name`      | 菜单名称       | 显示在菜单栏上的名称                           |
| `component_type` | 表单样式       | `1`=模态框（Modal）/ `2`=抽屉（Drawer）        |
| `form_width`     | 表单宽度(px)   | 默认 `600`                                     |
| `is_full`        | 表单全屏       | `1`=否 / `2`=是                                |

**树表配置**（仅 `tpl_category=tree` 时显示）：

| 字段             | 说明       | 示例        |
| ---------------- | ---------- | ----------- |
| `tree_id`        | 树主 ID    | 一般为主键  |
| `tree_parent_id` | 树父 ID    | `parent_id` |
| `tree_name`      | 树名称字段 | `name`      |

以上树表字段存储在 `saicode_table.options` JSON 中。

#### Tab 2：字段配置

按列展示所有字段的属性，支持拖拽排序：

| 列                       | 说明                       | 备注                            |
| ------------------------ | -------------------------- | ------------------------------- |
| 字段名称                 | `column_name`（只读）      |                                 |
| 字段描述                 | `column_comment`（可编辑） |                                 |
| **关联显示**             | `table_field`（可编辑）    | 格式如 `category.category_name` |
| 列表宽度                 | `column_width`             | 数字                            |
| 物理类型                 | `column_type`（只读）      |                                 |
| 查看/列表/排序/表单/查询 | checkbox 开关              | 批量全选支持                    |

**`table_field`（关联显示）** 是关键配置：当字段是外键（如 `category_id`），可设置为 `category.category_name`，生成的代码会自动使用 with 关联加载并显示关联模型的字段。

#### Tab 3：菜单功能

选择要生成的 CRUD 功能按钮。默认自带 `index`、`save`、`update`、`read`、`destroy`，可额外添加：

- `import` — 导入功能
- `export` — 导出功能

#### Tab 4：关联配置

配置模型关联关系，支持 4 种类型：

| 关联类型       | 值              | 说明           |
| -------------- | --------------- | -------------- |
| 一对一         | `hasOne`        |                |
| 一对多         | `hasMany`       |                |
| 一对一（反向） | `belongsTo`     | 常用于外键关联 |
| 多对多         | `belongsToMany` | 需指定中间表   |

每条关联需要配置：

| 字段         | 说明                                                      |
| ------------ | --------------------------------------------------------- |
| `type`       | 关联类型                                                  |
| `name`       | 关联名称（代码中 `with()` 调用的名称）                    |
| `model`      | 关联模型（完整类名）                                      |
| `localKey`   | hasOne/hasMany=当前模型主键；belongsTo/belongsToMany=外键 |
| `foreignKey` | hasOne/hasMany=外键(当前模型\_id)；belongsTo=关联模型主键 |
| `table`      | 仅 `belongsToMany`：中间表模型                            |

关联配置存储在 `saicode_table.options` JSON 的 `relations` 数组中。

### 3. 表单设计 & 搜索设计

通过管理后台的可视化设计器配置每个字段：

**表单设计** — 可视化拖拽排序，右侧属性面板配置：

- `view_type` — 视图组件类型（见下表）
- `is_insert` — 是否在表单中显示（新增/编辑）
- `is_required` — 是否必填
- `dict_type` — 关联字典类型（如 `data_status`、`yes_or_no`）
- `options` — 组件配置 JSON（如上传数量限制、编辑器高度、日期模式等）
- `span` — 栅格宽度（`24`=1 列，`12`=半宽，`8`=1/3，`6`=1/4）
- `default_value` — 默认值

**可用视图组件（`view_type`）**：

| 值            | 组件名         | 可配置 `options`                               |
| ------------- | -------------- | ---------------------------------------------- |
| `input`       | 输入框         | —                                              |
| `password`    | 密码框         | —                                              |
| `textarea`    | 文本域         | —                                              |
| `inputNumber` | 数字输入框     | `min`、`max`、`step`                           |
| `inputTag`    | 标签输入框     | —                                              |
| `switch`      | 开关           | —                                              |
| `slider`      | 滑块           | `min`、`max`、`step`                           |
| `select`      | 数据下拉框     | `field_label`、`field_value`、`url`            |
| `saSelect`    | 字典下拉框     | 需设 `dict_type`                               |
| `treeSelect`  | 树形下拉框     | `field_label`、`field_value`、`url`            |
| `radio`       | 字典单选框     | 需设 `dict_type`                               |
| `checkbox`    | 字典复选框     | 需设 `dict_type`                               |
| `date`        | 日期选择器     | `mode`=`date`/`datetime`                       |
| `time`        | 时间选择器     | —                                              |
| `rate`        | 评分器         | `min`、`max`、`step`                           |
| `cascader`    | 级联选择器     | `field_label`、`field_value`、`check_strictly` |
| `userSelect`  | 用户选择器     | —                                              |
| `uploadImage` | 图片上传       | `multiple`、`limit`                            |
| `imagePicker` | 图片选择       | `multiple`、`limit`                            |
| `uploadFile`  | 文件上传       | `multiple`、`limit`                            |
| `chunkUpload` | 大文件切片上传 | `multiple`、`limit`                            |
| `editor`      | 富文本编辑器   | `height`                                       |

**搜索设计** — 配置搜索栏的字段和组件：

- `is_query` — 是否作为搜索条件
- `query_type` — 查询方式：`eq`、`neq`、`gt`、`gte`、`lt`、`lte`、`like`、`in`、`notin`、`between`
- `query_component` — 搜索组件类型：`input`、`radio`、`saSelect`、`date`、`select`、`treeSelect`、`cascader`
- `query_dict` — 搜索字典类型
- `query_span` — 搜索栏栅格宽度
- `query_sort` — 搜索字段排序

### 4. 代码生成

有三种生成方式：

| 方式           | 说明                                                                          |
| -------------- | ----------------------------------------------------------------------------- |
| **预览**       | 在线查看生成的代码，不写入文件                                                |
| **生成到模块** | 直接将文件写入后端和前端项目对应目录（仅 debug 模式），同时自动创建菜单和权限 |
| **下载 ZIP**   | 打包所有生成文件为 ZIP 下载                                                   |

## 生成文件列表（共 10 个文件）

### 后端文件（PHP）

| 文件       | 路径模式                                                         | 说明                       |
| ---------- | ---------------------------------------------------------------- | -------------------------- |
| Controller | `app/{namespace}/controller/{package}/{ClassName}Controller.php` | 控制器，含 CRUD + 导入导出 |
| Logic      | `app/{namespace}/logic/{package}/{ClassName}Logic.php`           | 业务逻辑层                 |
| Model      | `app/{namespace}/model/{package}/{ClassName}.php`                | 数据模型                   |
| Validate   | `app/{namespace}/validate/{package}/{ClassName}Validate.php`     | 数据验证                   |
| SQL        | `sql.sql`                                                        | 菜单权限 SQL               |

如果 `template=plugin`，路径模式为 `plugin/{namespace}/app/admin/...`。

### 前端文件（Vue/TS）

前端文件生成到 `{generate_path}/src/views/plugin/{namespace}/` 下：

| 文件             | 路径                                            | 说明                             |
| ---------------- | ----------------------------------------------- | -------------------------------- |
| index.vue        | `{package}/{business}/index.vue`                | 列表主页面（含搜索、表格、操作） |
| edit-dialog.vue  | `{package}/{business}/modules/edit-dialog.vue`  | 编辑弹窗                         |
| view-dialog.vue  | `{package}/{business}/modules/view-dialog.vue`  | 查看详情弹窗                     |
| table-search.vue | `{package}/{business}/modules/table-search.vue` | 搜索表单                         |
| api.ts           | `api/{package}/{business}.ts`                   | API 接口定义                     |

## 字段自动推断规则

`ColumnLogic.php` 在装载表时会根据字段名和类型自动设置视图组件：

| 规则         | 触发条件                    | 设置                                       |
| ------------ | --------------------------- | ------------------------------------------ |
| 文本输入     | `varchar` 类型              | `view_type = input`                        |
| 富文本编辑器 | `text` / `longtext` 类型    | `view_type = editor`，不显示在列表和搜索   |
| 日期时间     | `datetime` 类型             | `view_type = date`，`query_type = between` |
| 日期         | `date` 类型                 | `view_type = date`，`query_type = between` |
| 图片上传     | 字段名含 `image`            | `view_type = uploadImage`                  |
| 文件上传     | 字段名含 `file` 或 `attach` | `view_type = uploadFile`                   |
| 数字输入     | 字段名为 `sort`             | `view_type = inputNumber`                  |
| 状态单选     | 字段名为 `status`           | `view_type = radio`，`dict = data_status`  |
| 是否单选     | 字段名含 `is_`              | `view_type = radio`，`dict = yes_or_no`    |
| 搜索 + 模糊  | 字段名含 `name` 或 `title`  | `is_query = true`，`query_type = like`     |
| 搜索 + 精确  | 字段名含 `type`             | `is_query = true`，`query_type = eq`       |

## 生成的控制器模板说明

控制器继承 `BaseController`，使用 `#[Permission]` 注解控制权限：

```php
#[Permission('功能名称列表', 'namespace:package:business:index')]
public function index(Request $request): Response
{
    $where = $request->more([...]);
    $query = $this->logic->search($where);
    $data = $this->logic->getList($query);
    return $this->success($data);
}
```

标准 CRUD 方法：`index`（列表）、`read`（读取）、`save`（新增）、`update`（更新）、`destroy`（删除），可选 `import`（导入）、`export`（导出）。

## 生成的前端 API 模板说明

```typescript
import request from "@/utils/http";

export default {
  list(params) {
    return request.get({ url: "/path/index", params });
  },
  read(id) {
    return request.get({ url: "/path/read?id=" + id });
  },
  save(params) {
    return request.post({ url: "/path/save", data: params });
  },
  update(params) {
    return request.put({ url: "/path/update", data: params });
  },
  delete(params) {
    return request.del({ url: "/path/destroy", data: params });
  },
};
```

## 前端页面使用的核心组件

生成的 `index.vue` 使用以下 Art Design Pro + SaiAdmin 组件：

| 组件                    | 说明                                         |
| ----------------------- | -------------------------------------------- |
| `ArtTable`              | 数据表格（分页、排序、选择）                 |
| `ArtTableHeader`        | 表格头部（刷新、列设置）                     |
| `TableSearch`           | 搜索面板（生成的 table-search.vue）          |
| `EditDialog`            | 编辑弹窗（生成的 edit-dialog.vue）           |
| `ViewDialog`            | 查看详情弹窗（生成的 view-dialog.vue）       |
| `SaButton`              | SaiAdmin 按钮组件（success/secondary/error） |
| `SaImport` / `SaExport` | 导入导出组件                                 |
| `useTable`              | 表格 Hook（@/hooks/core/useTable）           |
| `useSaiAdmin`           | SaiAdmin 通用 Hook（对话框、删除等）         |

## 注意事项

1. **生成到模块**仅在 `debug=true` 模式下可用
2. 前端项目目录必须与后端为**同级目录**（如 `server/` 和 `saiadmin-artd/` 同级）
3. 生成到模块时会**自动创建菜单和权限**到 `sa_system_menu` 表
4. 字段同步（`sync`）会保留已手动修改过的字段配置
5. 模板引擎使用 **Twig**，模板文件在 `utils/code/stub/saiadmin/` 下
6. 支持自定义 stub 模板来扩展生成的代码风格

---

## AI 自动执行代码生成

### 脚本位置

```
.agent/skills/saicode/scripts/saicode_generate.php
```

### 在 server/ 目录下执行

所有命令必须在 `server/` 目录下执行：

```bash
cd /path/to/project/server
php ../.agent/skills/saicode/scripts/saicode_generate.php <command> [options]
```

### 可用命令

| 命令       | 说明                                   | 必需参数                                   |
| ---------- | -------------------------------------- | ------------------------------------------ |
| `list`     | 列出已装载的数据表                     | 无                                         |
| `menus`    | 查看系统菜单树（确定 belong_menu_id）  | 可选 `--parent=ID`                         |
| `load`     | 装载数据表到 saicode 系统              | `--table=表名`                             |
| `config`   | 查看/修改生成配置                      | `--id=表ID`                                |
| `columns`  | 查看已装载表的字段配置                 | `--id=表ID`                                |
| `columns`  | 修改字段属性                           | `--id=表ID --set=字段:属性=值`             |
| `relation` | 查看关联配置                           | `--id=表ID`                                |
| `relation` | 添加模型关联                           | `--id=表ID --add --type=xx --name=xx ...`  |
| `relation` | 删除模型关联                           | `--id=表ID --del=关联名称`                 |
| `preview`  | 预览生成的代码                         | `--id=表ID`                                |
| `generate` | 生成代码到模块（文件 + 菜单权限）      | `--id=表ID`                                |
| `rollback` | 撤回生成（删除文件 + 菜单权限）        | `--id=表ID`                                |
| `rollback` | 撤回并清理 saicode 记录                | `--id=表ID --clean`                        |
| `all`      | 一键全流程（load + config + generate） | `--table=表名 --namespace=xx --package=xx` |

### 配置参数

| 参数               | 说明           | 默认值                           | 示例                                    |
| ------------------ | -------------- | -------------------------------- | --------------------------------------- |
| `--table`          | 数据库表名     | —                                | `sa_member`                             |
| `--source`         | 数据源名称     | `mysql`                          | `mysql`                                 |
| `--namespace`      | 应用名称       | —                                | `saiuser`                               |
| `--package`        | 功能模块分组   | —                                | `member`                                |
| `--template`       | 应用类型       | `app`                            | `plugin` 或 `app`                       |
| `--generate_path`  | 前端项目目录名 | `saiadmin-artd`                  | `saiadmin-artd`                         |
| `--belong_menu_id` | 上级菜单 ID    | `80`                             | `1003`                                  |
| `--generate_menus` | 生成的功能列表 | `index,save,update,read,destroy` | `index,save,update,read,destroy,export` |
| `--tpl_category`   | 模板类型       | `single`                         | `single` 或 `tree`                      |
| `--menu_name`      | 菜单显示名称   | 表注释                           | `会员管理`                              |
| `--component_type` | 组件类型       | `1`                              | `1`=弹窗 `2`=抽屉                       |
| `--form_width`     | 表单弹窗宽度   | `600`                            | `800`                                   |
| `--is_full`        | 是否全屏       | `1`                              | `1`=否 `2`=是                           |
| `--span`           | 表单栅格宽度   | `24`                             | `12`（半宽双列布局）                    |

### AI 调用工作流

当需要为一张数据库表自动生成完整的前后端 CRUD 代码时，AI 应按以下流程操作：

#### 步骤 0：确定上级菜单 ID（重要）

```bash
# 查看顶级菜单树
php ../.agent/skills/saicode/scripts/saicode_generate.php menus

# 查看某个菜单的子菜单
php ../.agent/skills/saicode/scripts/saicode_generate.php menus --parent=1003
```

从输出中找到目标父菜单的 ID，用作 `--belong_menu_id` 参数。

#### 方式一：一键全流程（推荐，不需要关联配置时）

```bash
php ../.agent/skills/saicode/scripts/saicode_generate.php all \
  --table=表名 \
  --namespace=应用名 \
  --package=模块名 \
  --template=plugin \
  --belong_menu_id=上级菜单ID \
  --menu_name=菜单显示名称
```

#### 方式二：分步操作（需要精细调整字段或添加关联时）

```bash
# 1. 装载数据表
php ../.agent/skills/saicode/scripts/saicode_generate.php load --table=sa_member

# 2. 配置生成选项
php ../.agent/skills/saicode/scripts/saicode_generate.php config \
  --id=1 --namespace=saiuser --package=member --template=plugin --belong_menu_id=1003

# 3. 查看字段配置
php ../.agent/skills/saicode/scripts/saicode_generate.php columns --id=1

# 4. 按需调整字段属性（可选）
php ../.agent/skills/saicode/scripts/saicode_generate.php columns \
  --id=1 --set=status:view_type=select,dict_type=data_status
php ../.agent/skills/saicode/scripts/saicode_generate.php columns \
  --id=1 --set=avatar:view_type=uploadImage

# 5. 添加关联配置（可选）
php ../.agent/skills/saicode/scripts/saicode_generate.php relation \
  --id=1 --add --type=belongsTo --name=level \
  --model=MemberLevel --localKey=member_level_id --foreignKey=id
php ../.agent/skills/saicode/scripts/saicode_generate.php columns \
  --id=1 --set=member_level_id:table_field=level.name

# 6. 生成到模块
php ../.agent/skills/saicode/scripts/saicode_generate.php generate --id=1
```

### 关联配置 CLI 参数

| 参数           | 说明                                           |
| -------------- | ---------------------------------------------- |
| `--type`       | `hasOne`/`hasMany`/`belongsTo`/`belongsToMany` |
| `--name`       | 关联名称（代码中 with 调用名）                 |
| `--model`      | 关联模型类名                                   |
| `--localKey`   | 本地键                                         |
| `--foreignKey` | 外键                                           |
| `--mid_table`  | 中间表（仅 belongsToMany）                     |

### 关键决策参考

- **belong_menu_id**：必须先用 `menus` 命令查询，不要猜测
- **template 选择**：当前项目使用插件体系时选 `plugin`，独立应用选 `app`
- **namespace**：对应插件/应用名，如 `saiuser`、`helpsupport`、`saiadmin`
- **package**：功能模块分组，如 `member`、`system`、`cms`
- **关联 + table_field 配合使用**：添加关联后，设置字段的 `table_field` 来显示关联数据
- 生成后需要**重启 webman 服务** (`php webman restart`) 使新路由生效
- 生成后需要给管理员角色**分配新菜单权限**（可通过管理后台操作）
