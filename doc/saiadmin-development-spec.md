# SaiAdmin 开发规范

## 1. 适用范围

本文档基于当前仓库实际代码整理，适用于本项目的 SaiAdmin 后端、前端、插件模块、菜单配置与权限节点开发。

本文默认按“后端控制模式”整理，也就是菜单、按钮权限、动态路由都以后端接口返回结果为准。

仓库当前分为两部分：

- `server`：Webman + SaiAdmin 后端
- `saiadmin-artd`：Vue 3 + Art Design Pro 前端

本文不是通用教程，而是当前项目的落地约束。

## 2. 总体目录约定

### 2.1 后端目录

核心后台位于：

- `server/plugin/saiadmin`

业务插件示例位于：

- `server/plugin/saisms`
- `server/plugin/saicode`

推荐目录形态如下：

```text
server/plugin/{plugin}/
├── app/
│   ├── controller/ 或 admin/controller/
│   ├── logic/ 或 admin/logic/
│   ├── model/
│   └── validate/
├── config/
│   ├── middleware.php
│   └── route.php
└── db/
    └── install.sql
```

说明：

- `saiadmin` 核心插件使用显式路由配置。
- 业务插件当前同时存在两种组织方式：核心插件使用 `app/controller/system`、`app/controller/tool`；业务插件示例 `saisms` 使用 `app/admin/controller`。
- 新业务优先保持“控制器、逻辑、验证器、模型”清晰分层，不要把业务逻辑堆在控制器。

### 2.2 前端目录

核心后台页面位于：

- `saiadmin-artd/src/views/system`
- `saiadmin-artd/src/views/safeguard`
- `saiadmin-artd/src/views/tool`

插件页面位于：

- `saiadmin-artd/src/views/plugin/{plugin}`

推荐目录形态如下：

```text
saiadmin-artd/src/views/
├── system/{module}/
│   ├── index.vue
│   └── modules/
│       ├── edit-dialog.vue
│       └── table-search.vue
└── plugin/{plugin}/
    ├── api/
    │   └── xxx.ts
    └── {module}/
        ├── index.vue
        └── modules/
```

## 3. 后端开发规范

## 3.1 控制器规范

控制器统一继承 `plugin\saiadmin\basic\BaseController`。

推荐写法：

1. 在构造函数中注入 `$this->logic` 与 `$this->validate`。
2. 标准 CRUD 方法名固定使用：
   - `index`
   - `read`
   - `save`
   - `update`
   - `destroy`
3. 额外业务动作使用明确动词，例如：
   - `import`
   - `export`
   - `changeStatus`
   - `run`
   - `menuPermission`
4. 入参优先通过 `$request->more([...])` 收集查询条件，通过 `$request->post()` 收集提交数据。
5. 返回统一使用：
   - `$this->success(...)`
   - `$this->fail(...)`

标准示例可参考：

- `server/plugin/saiadmin/app/controller/system/SystemMenuController.php`
- `server/plugin/saisms/app/admin/controller/SmsTagController.php`

### 3.1.1 控制器职责边界

控制器只做四件事：

1. 接收请求参数
2. 调用验证器
3. 调用逻辑层
4. 返回统一响应

禁止在控制器里直接堆复杂查询、事务、跨表写入、权限拼装。

## 3.2 逻辑层规范

逻辑层统一继承 `plugin\saiadmin\basic\think\BaseLogic`。

推荐写法：

1. 构造函数里设置 `$this->model`。
2. 查询场景优先复用：
   - `search()`
   - `getList()`
   - `getAll()`
3. 多表写入或联动更新必须放在逻辑层事务中，通过 `transaction()` 执行。
4. 菜单、角色、用户、权限有缓存联动时，逻辑层负责清缓存。

基础行为：

- `search()` 会自动过滤空字符串、`null`、空数组。
- `getList()` 默认读取 `page`、`limit`、`orderField`、`orderType`，支持 `saiType=all` 返回全量。
- `BaseLogic` 已封装基础 `add/edit/read/destroy`。

说明：

- 简单 CRUD 可以直接调用父类方法。
- 涉及越权、部门隔离、角色级别、菜单树处理时，必须在逻辑层单独封装。

示例：

- `server/plugin/saiadmin/app/logic/system/SystemMenuLogic.php`
- `server/plugin/saiadmin/app/logic/system/SystemRoleLogic.php`
- `server/plugin/saiadmin/app/logic/system/SystemUserLogic.php`

## 3.3 模型规范

模型统一继承 `plugin\saiadmin\basic\think\BaseModel`。

基础约束：

1. 必须声明：
   - `$table`
   - `$pk`
2. 默认启用软删除，删除字段为 `delete_time`。
3. 自动维护：
   - `create_time`
   - `update_time`
   - `created_by`
   - `updated_by`
4. 通用时间搜索器已内置：
   - `searchCreateTimeAttr`
   - `searchUpdateTimeAttr`
5. 业务搜索条件通过 `searchXxxAttr` 形式定义。

推荐数据库字段：

- 主键：`id`
- 创建人：`created_by`
- 更新人：`updated_by`
- 创建时间：`create_time`
- 更新时间：`update_time`
- 删除时间：`delete_time`
- 状态字段统一优先使用 `status`

说明：

- 业务表尽量遵循 SaiAdmin 现有字段风格，避免每个模块自造一套命名。
- 模型中的关联关系、作用域查询、搜索器都应该围绕表语义定义，不要把控制器逻辑塞进模型。

## 3.4 验证器规范

验证器统一继承 `plugin\saiadmin\basic\BaseValidate`。

要求：

1. 必须定义 `$rule`、`$message`、`$scene`。
2. 常用场景统一命名为：
   - `save`
   - `update`
3. 唯一性校验优先使用基类扩展的 `unique` 规则。

说明：

- 控制器统一通过 `$this->validate('save', $data)` 或 `$this->validate('update', $data)` 调用。
- 新增和修改需要不同规则时，必须拆分场景。

## 3.5 路由规范

### 3.5.1 核心模块路由

核心模块显式注册在：

- `server/plugin/saiadmin/config/route.php`

约定如下：

- 核心系统接口前缀：`/core`
- 工具接口前缀：`/tool`
- 标准 CRUD 优先通过 `fastRoute()` 注册

示例：

```php
fastRoute('user', SystemUserController::class);
```

对应接口形态通常为：

- `GET /core/user/index`
- `GET /core/user/read`
- `POST /core/user/save`
- `PUT /core/user/update`
- `DELETE /core/user/destroy`

### 3.5.2 插件模块路由

插件模块当前主要走 Webman 插件路由风格，请求地址通常为：

```text
/app/{plugin}/admin/{Controller}/{action}
```

例如 `saisms`：

- `/app/saisms/admin/SmsTag/index`
- `/app/saisms/admin/SmsTag/save`

插件中间件需保证挂载登录校验、权限校验、系统日志，示例见：

- `server/plugin/saisms/config/middleware.php`

## 4. 前端开发规范

## 4.1 API 层规范

每个页面模块必须有独立 API 文件，放在：

- 核心模块：`saiadmin-artd/src/api/...`
- 插件模块：`saiadmin-artd/src/views/plugin/{plugin}/api/...`

约束：

1. 一个页面对应一个 API 文件。
2. 标准方法优先命名为：
   - `list`
   - `read`
   - `save`
   - `update`
   - `delete`
3. 扩展动作命名必须与后端动作一致，例如：
   - `testTag`
   - `changeStatus`
   - `run`
4. 请求 URL 必须与实际后端地址一一对应。

示例：

- `saiadmin-artd/src/views/plugin/saisms/api/tag.ts`

## 4.2 页面结构规范

列表页推荐固定拆分为：

1. `index.vue`：页面主入口
2. `modules/table-search.vue`：搜索区域
3. `modules/edit-dialog.vue`：编辑弹窗
4. 需要详情弹窗时，再增加 `view-dialog.vue`

页面实现建议：

1. 列表页统一使用 `useTable()` 驱动表格数据。
2. 增删改查统一复用 `useSaiAdmin()` 中的弹窗、删除、批量删除逻辑。
3. 表单字典优先使用：
   - `sa-select`
   - `sa-radio`
   - `sa-switch`
4. 按钮权限统一使用 `v-permission`。

示例：

- `saiadmin-artd/src/views/system/menu/index.vue`
- `saiadmin-artd/src/views/plugin/saisms/tag/index.vue`

## 4.3 菜单组件路径规范

菜单表中的 `component` 字段最终对应前端 `src/views` 下的页面路径。

规则如下：

1. 菜单类型为“目录”时，后端会自动填充为占位组件 `/index/index`。
2. 菜单类型为“菜单”时，`component` 必须填写真实页面路径，格式为：

```text
/system/user
/plugin/saisms/tag
```

3. 不带 `.vue` 后缀。
4. 必须与 `src/views` 内真实文件路径一致。

菜单编辑页的自动补全逻辑可参考：

- `saiadmin-artd/src/views/system/menu/modules/edit-dialog.vue`

## 5. 权限体系规范

## 5.1 权限链路

当前项目的权限控制链路是：

1. 控制器方法声明 `#[Permission('标题', 'slug')]`
2. `ReflectionCache` 反射读取权限注解
3. `CheckAuth` 中间件按 `slug` 校验当前用户权限
4. `UserAuthCache` 根据“用户角色 -> 角色菜单 -> type=3 权限节点”生成按钮权限列表
5. `/core/system/user` 返回 `buttons`
6. 前端 `v-permission` 依据 `buttons` 控制按钮显示

因此，权限是否生效取决于三处是否一致：

1. 后端方法上的 `slug`
2. 菜单表中的权限节点 `slug`
3. 前端 `v-permission="'slug'"` 使用的字符串

只要三者有一个不一致，权限就不会生效。

## 5.2 权限注解规范

所有需要权限控制的后台动作，都必须声明 `#[Permission]`。

推荐格式：

```php
#[Permission('用户数据添加', 'core:user:save')]
```

要求：

1. 标题必须可读，直接体现业务动作。
2. `slug` 必须全局唯一。
3. `slug` 必须稳定，不能今天叫 `save`，明天改成 `create` 而不同步菜单与前端。
4. 未配置 `slug` 的 `Permission` 注解只会保留标题，不能形成实际权限校验。

特别说明：

- `CheckAuth` 使用字符串精确匹配。
- 前端 `v-permission` 也是字符串精确匹配。
- 大小写、分隔符、动词名不一致都会直接导致权限失效。

## 5.3 权限标识命名规范

推荐统一采用：

```text
{域}:{资源}:{动作}
```

核心模块示例：

- `core:user:index`
- `core:user:read`
- `core:user:save`
- `core:user:update`
- `core:user:destroy`
- `core:role:menu`

工具模块示例：

- `tool:crontab:index`
- `tool:crontab:edit`
- `tool:crontab:run`

插件模块示例：

- `saisms:tag:index`
- `saisms:tag:testTag`
- `saisms:config:changeStatus`

建议：

1. 域名固定体现模块来源，如 `core`、`tool`、`saisms`。
2. 资源名尽量使用单数且稳定。
3. 标准动作优先使用：
   - `index`
   - `read`
   - `save`
   - `update`
   - `destroy`
4. 扩展动作直接使用业务名，例如：
   - `import`
   - `export`
   - `run`
   - `changeStatus`
   - `password`
   - `cache`
   - `menu`

不要把同一业务写成多套动词，例如同一模块同时出现 `save/create/add`。

补充说明：

- 历史权限标识一旦已入库并被前端引用，就不要只改其中一侧。
- 例如当前仓库里已经存在 `core:logs:Oper` 这种大小写敏感标识，若要规范化，必须同步修改控制器注解、权限节点和前端使用点。

## 5.4 菜单类型规范

当前项目菜单字典 `menu_type` 定义如下：

1. `1`：目录
2. `2`：菜单
3. `3`：按钮
4. `4`：外链

对应规范如下。

### 5.4.1 目录节点（type = 1）

用途：

- 作为菜单分组
- 不直接承载按钮权限
- 可以挂子菜单或隐藏权限节点

字段建议：

- `path`：一级目录用绝对路径，如 `/system`、`/tool`
- `code`：路由名称，必须唯一
- `component`：留空

### 5.4.2 页面菜单（type = 2）

用途：

- 真实页面路由
- 可挂按钮/API 权限节点

字段建议：

- `path`：
  - 一级子菜单以下使用相对路径，如 `user`、`role`
  - 顶层目录使用绝对路径
- `code`：页面路由名，必须唯一
- `component`：前端页面路径，如 `/system/user`

### 5.4.3 权限节点（type = 3）

这是最关键的“权限节点”类型。

用途：

- 绑定按钮权限
- 绑定接口权限
- 作为角色授权最小颗粒度

字段规范：

- `parent_id`：必须挂在某个页面菜单或隐藏目录下
- `name`：按钮名称或动作名称，如“添加”“删除”“导出”
- `slug`：必须填写，且必须与后端 `#[Permission]` 完全一致
- `path`：留空
- `component`：留空
- `icon`：通常留空

常见挂载方式：

1. 挂在页面菜单下
   - 例如 `用户管理` 下挂 `core:user:save`、`core:user:update`
2. 挂在隐藏目录下
   - 例如仓库里现有“附加权限”目录，用于挂上传图片、上传文件、资源列表、用户列表这类非侧边栏页面权限

建议：

- 任何会被前端 `v-permission` 使用的动作，都必须有对应 `type=3` 节点。
- 任何走 `CheckAuth` 的受控接口，也必须有对应 `type=3` 节点。
- 一个 `slug` 只对应一个权限节点，不要重复建。

### 5.4.4 外链节点（type = 4）

用途：

- 外部文档、第三方系统地址

字段规范：

- `link_url`：必须填写完整 URL
- 前端会转成 `/outside/Iframe` 路由，并将真实链接写入 `meta.link`

## 5.5 菜单字段填写规范

菜单管理页里常用字段建议按下列规则填写：

注意：

- 当前后端 `SystemMenuValidate` 只强校验了 `name` 与 `status`。
- `path`、`code`、`component`、`slug` 的正确性主要依赖前端表单约束和开发自律，批量导入、脚本写库、手工改库时更要谨慎。

### 5.5.1 `code`

含义：

- 前端路由名

要求：

1. 在同一系统内必须唯一。
2. 使用 PascalCase 或稳定英文名。
3. 不要与已有核心页面重名。

示例：

- `User`
- `Role`
- `SmsTag`

### 5.5.2 `path`

含义：

- 前端访问路径片段

要求：

1. 一级目录必须以 `/` 开头。
2. 子级页面建议使用相对路径。
3. 按钮权限节点不填。

示例：

- 一级目录：`/system`
- 二级页面：`user`
- 插件页面：`tag`

### 5.5.3 `component`

含义：

- `src/views` 下的页面路径

要求：

1. 只对 `type=2` 菜单必填。
2. 与真实文件一致。
3. 不带 `.vue`。

### 5.5.4 `is_iframe`

含义：

- 是否以内嵌方式打开外链

说明：

- 主要对外链场景有意义。

### 5.5.5 `is_keep_alive`

含义：

- 切换标签页是否缓存页面

建议：

- 复杂表单页、编辑页、报表页按需开启。
- 数据一致性要求高的列表页谨慎开启。

### 5.5.6 `is_hidden`

含义：

- 是否在左侧菜单中隐藏

适用场景：

- 详情页
- 中转页
- 附加权限目录

### 5.5.7 `is_fixed_tab`

含义：

- 是否固定在标签栏

适用场景：

- 常驻首页
- 高频工作台

### 5.5.8 `is_full_page`

含义：

- 是否脱离后台主框架全屏展示

适用场景：

- 特殊大屏
- 独立流程页

## 5.6 角色授权规范

角色与菜单/权限通过中间表 `sa_system_role_menu` 建立关系。

当前实现规则：

1. 菜单权限保存入口为 `SystemRoleController::menuPermission`。
2. `SystemRoleLogic::saveMenuPermission()` 会先清空旧关系，再批量写入新关系。
3. 用户按钮权限来自角色关联到的 `type=3` 菜单节点。
4. 用户菜单路由来自角色关联到的 `type in (1,2,4)` 菜单节点。

开发要求：

1. 新页面上线时，至少要补齐：
   - 页面菜单节点
   - 对应按钮/API 权限节点
2. 如果只是写了控制器注解，没有建菜单权限节点，角色无法授权，普通管理员也拿不到权限。
3. 如果只是建了按钮节点，没有在控制器上加 `#[Permission]`，后端接口不会形成权限保护。

## 5.7 超级管理员规则

当前项目中：

- 用户 `id = 1` 视为超级管理员

行为：

1. `CheckAuth` 直接放行
2. `/core/system/user` 返回 `buttons = ['*']`
3. 菜单返回全部菜单

因此：

- 开发联调不能只拿超级管理员验证权限，否则很容易漏掉普通角色的授权问题。

## 6. 数据权限与越权保护规范

除按钮权限外，当前项目还实现了部门和角色级别限制。

## 6.1 部门数据范围

`SystemUser`、`SystemDept` 等场景中，非超管默认只能操作当前部门及下级部门数据。

体现位置：

- `SystemUser::scopeAuth()`
- `SystemUserLogic::deptProtect()`

要求：

- 涉及用户、部门、组织树数据时，必须复用现有部门保护逻辑。
- 不允许在新接口里绕开 `auth()` 或 `deptProtect()` 直接全量查全库。

## 6.2 角色级别保护

当前实现中，角色 `level` 用于越权保护。

规则：

1. 当前用户不能操作级别大于等于自己最高角色级别的角色。
2. 给用户分配角色时，也不能分配高于自身级别的角色。

体现位置：

- `SystemRoleLogic::handleData()`
- `SystemRoleLogic::destroy()`
- `SystemUserLogic::roleProtect()`

开发要求：

- 新增“授权”“转交”“指定负责人”之类能力时，必须先确认是否要复用角色级别保护。

## 6.3 行级数据权限

当前项目已接入 `datascope` 的核心能力，但不是作为独立插件运行，而是合并进 SaiAdmin 核心逻辑层与角色管理。

落点如下：

1. 角色表 `sa_system_role` 使用 `data_scope` 字段定义数据范围。
2. 自定义数据范围通过 `sa_system_role_dept` 存角色与部门的关联。
3. 业务 Logic 继承 `BaseLogic` 后，可通过 `protected bool $scope = true;` 开启数据权限。
4. 开启后，`read/edit/destroy/getList/getAll` 会自动经过数据范围过滤。

### 6.3.1 数据范围枚举

本项目统一按以下枚举解释：

1. `1`：全部数据权限
2. `2`：自定义数据权限
3. `3`：本部门数据权限
4. `4`：本部门及以下数据权限
5. `5`：本人数据权限

注意：

- 历史 SQL 注释里如果出现过其他顺序，以本规范和当前代码实现为准。

### 6.3.2 过滤依据

当前默认过滤字段是业务表的 `created_by`。

这意味着：

1. 需要启用数据权限的业务表必须有 `created_by` 字段。
2. 新增数据时必须保证 `created_by` 能正确写入。
3. 如果业务是按其他责任人字段过滤，例如 `owner_id`、`leader_id`，则不能直接照搬默认逻辑。

### 6.3.3 多角色规则

当前项目按 `datascope` 插件原始逻辑处理多角色数据权限：

1. 遍历当前用户角色。
2. 取 `data_scope` 数值更大的那个角色，作为最终数据范围。
3. 如果最终是“自定义数据权限”，则按这个角色对应的 `sa_system_role_dept` 配置取部门。

也就是说，这里不是“多角色并集”，而是“按插件现有优先级取一个最终范围”。

### 6.3.4 开启方式

在业务 Logic 中显式开启：

```php
class ArticleLogic extends BaseLogic
{
    protected bool $scope = true;
}
```

建议：

1. 只对确实需要行级权限控制的业务开启。
2. 开启前先确认表中存在 `created_by`。
3. 如果你重写了 `read/edit/destroy`，优先复用父类方法，避免绕过数据权限。

### 6.3.5 角色管理接入要求

角色页中的“数据权限”能力依赖以下节点同时存在：

1. 后端接口：
   - `/core/role/getDeptByRole`
   - `/core/role/dataPermission`
2. 权限节点：
   - `core:role:data`
3. 菜单表按钮节点：
   - `type = 3`
   - `slug = core:role:data`

如果只接了后端逻辑，没有补菜单权限节点，前端普通角色看不到入口。

已安装实例可直接参考：

- `server/plugin/saiadmin/db/datascope-install.sql`

### 6.3.6 新业务至少需要哪些字段

如果一个新业务要支持当前项目这套“完整的数据权限体系”，至少要分三层理解字段要求。

#### 一类：数据权限强依赖字段

这是最少不能缺的字段：

1. `id`
   - 主键，所有基础 CRUD 都依赖它。
2. `created_by`
   - 当前 `datascope` 插件默认就是按这个字段做数据过滤。
   - 没有这个字段，开启 `protected bool $scope = true;` 后就无法正常工作。

#### 二类：强推荐的通用治理字段

虽然不是 `datascope` 过滤本身的硬依赖，但新业务如果不带这些字段，后续审计、排障、软删除、列表筛选会很难做。

建议至少补齐：

1. `updated_by`
   - 记录最后修改人。
2. `create_time`
   - 记录创建时间。
3. `update_time`
   - 记录更新时间。
4. `delete_time`
   - 支持软删除。
5. `status`
   - 支持启停、上下架、草稿/生效等状态控制。
6. `remark`
   - 便于保留补充说明。

也就是说，如果你问“一个新业务表最起码要长什么样”，本项目推荐最少是：

```text
id
created_by
updated_by
create_time
update_time
delete_time
status
remark
```

#### 三类：建议补充的业务归属字段

这些不是当前 `datascope` 插件的硬依赖，但如果业务后面要做更细的数据治理，建议一开始就留好。

1. `dept_id` 或 `belong_dept_id`
   - 表示数据归属部门。
   - 方便做部门统计、部门筛选、跨部门转移。
2. `owner_id` / `leader_id` / `manager_id`
   - 表示业务负责人。
   - 当未来不想按 `created_by` 过滤，而是按负责人过滤时，可以平滑改造。
3. `biz_code` / `code`
   - 业务编号，便于检索与对外引用。

#### 结论

按当前项目和 `datascope` 插件的实现，新业务如果要支持“完整数据权限体系”，最低要求是：

1. 必须有 `id`
2. 必须有 `created_by`
3. 强烈建议同时补齐：
   - `updated_by`
   - `create_time`
   - `update_time`
   - `delete_time`
   - `status`
   - `remark`

如果你希望后续还有扩展空间，再补：

1. `dept_id`
2. `owner_id` 或其他业务归属人字段

## 7. 缓存规范

菜单和权限都带缓存，修改后必须清理。

当前主要缓存：

1. `UserAuthCache`
   - 用户按钮权限缓存
2. `UserMenuCache`
   - 用户菜单缓存
3. `UserInfoCache`
   - 用户资料缓存
4. `ReflectionCache`
   - 权限注解和免登录反射缓存

要求：

1. 修改菜单后，必须清 `UserMenuCache`。
2. 修改角色菜单权限后，必须清角色相关按钮缓存和菜单缓存。
3. 修改用户角色、首页、密码、资料后，必须清用户信息/权限/菜单缓存。
4. 修改 `#[Permission]` 注解后，如遇权限不生效，应考虑清 `ReflectionCache`。

## 8. 新模块落地清单

新增一个标准业务模块时，建议按下面顺序完成：

1. 建表，字段风格对齐 SaiAdmin 基础字段。
2. 新建 Model，继承 `BaseModel`，补充搜索器与关联。
3. 新建 Validate，定义 `save/update` 场景。
4. 新建 Logic，继承 `BaseLogic`，封装查询、事务和越权控制。
5. 新建 Controller，继承 `BaseController`，补齐标准 CRUD 方法。
6. 给所有受控动作加 `#[Permission]` 注解。
7. 注册路由。
8. 新建前端 API 文件。
9. 新建前端页面：
   - `index.vue`
   - `modules/table-search.vue`
   - `modules/edit-dialog.vue`
10. 在菜单表新增：
   - 目录或页面菜单
   - 对应按钮/API 权限节点
11. 给角色分配新菜单与权限节点。
12. 用“非超级管理员账号”验证：
   - 页面是否可见
   - 按钮是否按权限显示
   - 接口是否按权限拦截

## 9. 本项目推荐的开发底线

1. 控制器只做参数、验证、返回，不写业务细节。
2. 权限必须前后端同名、菜单落库、角色可授。
3. 菜单是路由来源，权限节点是按钮/API 来源，两者都不能省。
4. 新增模块必须验证普通角色，不允许只拿超管验收。
5. 涉及组织、角色、用户操作，必须检查部门范围和角色级别保护。
6. 改菜单、角色、用户权限后，必须处理缓存。

## 10. 代码依据

本文主要依据以下实现整理：

- `server/plugin/saiadmin/basic/BaseController.php`
- `server/plugin/saiadmin/basic/think/BaseLogic.php`
- `server/plugin/saiadmin/basic/think/BaseModel.php`
- `server/plugin/saiadmin/app/middleware/CheckAuth.php`
- `server/plugin/saiadmin/app/cache/ReflectionCache.php`
- `server/plugin/saiadmin/app/cache/UserAuthCache.php`
- `server/plugin/saiadmin/app/cache/UserMenuCache.php`
- `server/plugin/saiadmin/app/controller/system/SystemMenuController.php`
- `server/plugin/saiadmin/app/controller/system/SystemRoleController.php`
- `server/plugin/saiadmin/app/controller/system/SystemUserController.php`
- `server/plugin/saiadmin/app/logic/system/SystemMenuLogic.php`
- `server/plugin/saiadmin/app/logic/system/SystemRoleLogic.php`
- `server/plugin/saiadmin/app/logic/system/SystemUserLogic.php`
- `server/plugin/saiadmin/utils/Helper.php`
- `server/plugin/saiadmin/config/route.php`
- `server/plugin/saiadmin/db/saiadmin-6.0.sql`
- `saiadmin-artd/src/directives/sai/permission.ts`
- `saiadmin-artd/src/router/core/MenuProcessor.ts`
- `saiadmin-artd/src/router/guards/beforeEach.ts`
- `saiadmin-artd/src/views/system/menu/index.vue`
- `saiadmin-artd/src/views/system/menu/modules/edit-dialog.vue`
- `saiadmin-artd/src/views/plugin/saisms/api/tag.ts`
