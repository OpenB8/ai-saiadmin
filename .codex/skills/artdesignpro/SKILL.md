---
name: Art Design Pro 开发指南
description: 基于 Art Design Pro 官方文档的 Vue 3 企业级中后台开发技能
---

# Art Design Pro 开发技能

> **生成时间**: 2026-02-08
> **官方文档**: https://www.artd.pro/
> **GitHub**: https://github.com/Daymychen/art-design-pro

---

## 一、技术栈

- **核心框架**: Vue 3、TypeScript、Vite
- **UI 组件库**: Element Plus
- **样式方案**: Tailwind CSS、Sass
- **代码质量**: ESLint、Prettier、Stylelint
- **工程化**: Husky、Lint-staged、cz-git
- **状态管理**: Pinia
- **图标库**: Iconify (Remix Icon)

---

## 二、项目结构

```
├── src
│   ├── api/                 # API 接口
│   ├── assets/              # 静态资源 (images/styles/svg)
│   ├── components/
│   │   ├── business/        # 业务组件
│   │   └── core/            # 核心组件 (banners/cards/charts/forms/tables...)
│   ├── config/              # 项目配置
│   ├── directives/          # Vue 自定义指令
│   ├── enums/               # 枚举定义
│   ├── hooks/               # Vue 3 Composable 函数
│   ├── locales/             # 国际化资源
│   ├── plugins/             # 插件配置
│   ├── router/
│   │   ├── routes/asyncRoutes.ts    # 动态路由
│   │   └── routes/staticRoutes.ts   # 静态路由
│   ├── store/               # Pinia 状态管理
│   ├── types/               # TypeScript 类型定义
│   ├── utils/               # 工具函数
│   └── views/               # 页面组件
├── .env                     # 通用环境变量
├── .env.development         # 开发环境变量
├── .env.production          # 生产环境变量
└── vite.config.ts           # Vite 配置
```

---

## 三、快速开始

### 环境要求

- Node.js 20.19.0+
- 推荐使用 pnpm

### 安装运行

```bash
# 克隆代码
git clone https://github.com/Daymychen/art-design-pro

# 安装依赖
pnpm install
# 备选方案
pnpm install --ignore-scripts

# 开发运行 (默认端口 3006)
pnpm dev

# 生产构建
pnpm build

# 精简版本 (清理演示数据)
pnpm clean:dev
```

---

## 四、路由和菜单配置

### 4.1 路由类型

| 类型     | 配置位置                            | 说明                              |
| -------- | ----------------------------------- | --------------------------------- |
| 静态路由 | `src/router/routes/staticRoutes.ts` | 无需权限的基础页面 (登录/404/500) |
| 动态路由 | `src/router/routes/asyncRoutes.ts`  | 需要权限控制的业务页面            |

### 4.2 权限模式配置

在 `.env` 文件中配置：

```env
# frontend: 前端控制模式 (roles 字段)
# backend: 后端控制模式 (接口返回菜单)
VITE_ACCESS_MODE = frontend
```

### 4.3 动态路由示例

```typescript
// src/router/routes/asyncRoutes.ts
export const asyncRoutes: MenuListType[] = [
  {
    name: "System",
    path: "/system",
    component: "/index/index",
    meta: {
      title: "系统管理",
      icon: "ri:user-3-line",
      keepAlive: false,
    },
    children: [
      {
        path: "user",
        name: "User",
        component: "/system/user",
        meta: {
          title: "用户管理",
          keepAlive: true,
          roles: ["R_SUPER", "R_ADMIN"], // 前端模式下的角色权限
        },
      },
    ],
  },
];
```

### 4.4 菜单 Meta 属性

| 属性         | 类型     | 说明                    |
| ------------ | -------- | ----------------------- |
| `title`      | string   | 路由标题                |
| `icon`       | string   | 路由图标 (Iconify 格式) |
| `keepAlive`  | boolean  | 是否缓存页面            |
| `isHide`     | boolean  | 是否在菜单中隐藏        |
| `isHideTab`  | boolean  | 是否在标签页中隐藏      |
| `roles`      | string[] | 角色权限 (前端模式)     |
| `authList`   | Array    | 操作权限列表            |
| `link`       | string   | 外部链接                |
| `isIframe`   | boolean  | 是否为 iframe 内嵌      |
| `fixedTab`   | boolean  | 是否固定标签页          |
| `isFullPage` | boolean  | 是否全屏页面            |
| `activePath` | string   | 手动指定激活菜单路径    |

### 4.5 新建页面流程

1. 在 `src/views/` 下创建页面组件:

```vue
<template>
  <div class="page-content">
    <h1>新页面</h1>
  </div>
</template>
```

2. 在 `asyncRoutes.ts` 中注册路由:

```typescript
{
  path: "/mypage/index",
  name: "MyPage",
  component: "/mypage/index",
  meta: {
    title: "我的页面",
    keepAlive: true,
  },
}
```

---

## 五、useTable 组合式函数

### 5.1 基础用法

```vue
<template>
  <ArtTable
    :loading="loading"
    :data="data"
    :columns="columns"
    :pagination="pagination"
    @pagination:size-change="handleSizeChange"
    @pagination:current-change="handleCurrentChange"
  />
</template>

<script setup lang="ts">
import { useTable } from "@/composables/useTable";
import { fetchGetUserList } from "@/api/system-manage";

const {
  data,
  loading,
  columns,
  pagination,
  handleSizeChange,
  handleCurrentChange,
} = useTable({
  core: {
    apiFn: fetchGetUserList,
    apiParams: { current: 1, size: 20 },
    columnsFactory: () => [
      { prop: "id", label: "ID" },
      { prop: "name", label: "姓名" },
      { prop: "email", label: "邮箱" },
    ],
  },
});
</script>
```

### 5.2 完整配置示例

```typescript
const {
  data,
  loading,
  error,
  pagination,
  searchParams,
  getData,
  refreshCreate,
  refreshUpdate,
  refreshRemove,
  clearCache,
} = useTable<UserListItem>({
  // 核心配置
  core: {
    apiFn: fetchGetUserList,
    apiParams: { current: 1, size: 20, name: "", status: "" },
    immediate: true,
    columnsFactory: () => [
      { prop: "name", label: "姓名", sortable: true },
      { prop: "status", label: "状态", useSlot: true },
    ],
  },
  // 数据转换
  transform: {
    dataTransformer: (records) =>
      records.map((item) => ({
        ...item,
        statusText: item.status === 1 ? "激活" : "禁用",
      })),
  },
  // 性能优化
  performance: {
    enableCache: true,
    cacheTime: 5 * 60 * 1000,
    debounceTime: 300,
  },
  // 生命周期钩子
  hooks: {
    onSuccess: (data) => console.log("加载成功:", data.length),
    onError: (error) => ElMessage.error(error.message),
  },
});
```

### 5.3 核心配置项

| 参数             | 类型     | 默认值 | 说明                   |
| ---------------- | -------- | ------ | ---------------------- |
| `apiFn`          | Function | -      | **必需**，API 请求函数 |
| `apiParams`      | Object   | {}     | 默认请求参数           |
| `immediate`      | Boolean  | true   | 是否立即加载数据       |
| `columnsFactory` | Function | -      | 列配置工厂函数         |

### 5.4 刷新策略

| 方法              | 使用场景 | 说明                   |
| ----------------- | -------- | ---------------------- |
| `refreshData()`   | 手动刷新 | 清空所有缓存，重新获取 |
| `refreshCreate()` | 新增后   | 回到第一页             |
| `refreshUpdate()` | 编辑后   | 保持当前页             |
| `refreshRemove()` | 删除后   | 智能处理页码           |

### 5.5 CRUD 示例

```typescript
// 新增
const handleAdd = async () => {
  await addUser(userData);
  refreshCreate();
};

// 编辑
const handleEdit = async () => {
  await updateUser(userData);
  refreshUpdate();
};

// 删除
const handleDelete = async () => {
  await deleteUser(userId);
  refreshRemove();
};
```

---

## 六、ArtSearchBar 搜索栏组件

### 6.1 基础用法

```vue
<template>
  <ArtSearchBar
    v-model="formData"
    :items="formItems"
    @search="handleSearch"
    @reset="handleReset"
  />
</template>

<script setup>
const formData = ref({ name: "", status: "" });

const formItems = [
  { label: "用户名", key: "name", type: "input", placeholder: "请输入用户名" },
  {
    label: "状态",
    key: "status",
    type: "select",
    props: {
      options: [
        { label: "启用", value: "1" },
        { label: "禁用", value: "0" },
      ],
    },
  },
];
</script>
```

### 6.2 支持的控件类型

| 类型            | 说明           |
| --------------- | -------------- |
| `input`         | 输入框         |
| `number`        | 数字输入框     |
| `select`        | 下拉选择器     |
| `cascader`      | 级联选择器     |
| `treeselect`    | 树选择器       |
| `datetime`      | 日期时间选择器 |
| `timepicker`    | 时间选择器     |
| `switch`        | 开关           |
| `radiogroup`    | 单选框组       |
| `checkboxgroup` | 复选框组       |
| `rate`          | 评分           |
| `slider`        | 滑块           |

### 6.3 Props 配置

| 参数            | 类型          | 默认值  | 说明       |
| --------------- | ------------- | ------- | ---------- |
| `modelValue`    | Object        | {}      | 表单数据   |
| `items`         | Array         | []      | 表单项配置 |
| `span`          | number        | 6       | 栅格数     |
| `gutter`        | number        | 12      | 栅格间隔   |
| `labelPosition` | string        | 'right' | 标签位置   |
| `labelWidth`    | string/number | '70px'  | 标签宽度   |

---

## 七、权限控制

### 7.1 按钮权限 - hasAuth 方法

```vue
<script setup>
import { useAuth } from "@/composables/useAuth";
const { hasAuth } = useAuth();
</script>

<template>
  <ElButton v-if="hasAuth('add')">添加</ElButton>
</template>
```

### 7.2 v-auth 指令

```vue
<ElButton v-auth="'add'">添加</ElButton>
<ElButton v-auth="'edit'">编辑</ElButton>
<ElButton v-auth="'delete'">删除</ElButton>
```

### 7.3 v-roles 指令

```vue
<el-button v-roles="['R_SUPER', 'R_ADMIN']">仅管理员可见</el-button>
<el-button v-roles="'R_ADMIN'">仅 ADMIN 可见</el-button>
```

---

## 八、主题配置

### 8.1 全局配置

配置文件: `src/config/index.ts`

```typescript
const appConfig: SystemConfig = {
  systemInfo: {
    name: "Art Design Pro", // 系统名称
  },
  systemMainColor: [
    "#5D87FF",
    "#B48DF3",
    "#1D84FF",
    "#60C041",
    "#38C0FC",
    "#F9901F",
    "#FF80C8",
  ],
};
```

### 8.2 主题切换

```typescript
import { useTheme } from "@/hooks/core/useTheme";
import { SystemThemeEnum } from "@/enums/appEnum";

const { switchThemeStyles } = useTheme();

switchThemeStyles(SystemThemeEnum.DARK); // 暗色
switchThemeStyles(SystemThemeEnum.LIGHT); // 亮色
switchThemeStyles(SystemThemeEnum.AUTO); // 跟随系统
```

### 8.3 CSS 变量

```css
/* 文字颜色 */
color: var(--art-gray-100);
color: var(--art-gray-900);

/* 主题色 */
color: var(--art-primary);
color: var(--art-success);
color: var(--art-warning);
color: var(--art-error);

/* 背景颜色 */
background-color: var(--default-bg-color);
background-color: var(--default-box-color);

/* 边框 */
border: 1px solid var(--default-border);
```

### 8.4 Tailwind 工具类

```html
<!-- 文字颜色 -->
<div class="text-g-900">深色文字</div>
<div class="text-g-500">中等文字</div>

<!-- 背景颜色 -->
<div class="bg-box">卡片背景</div>

<!-- Flex 快捷类 -->
<div class="flex-c">
  <!-- flex + items-center -->
  <div class="flex-cb">
    <!-- flex + items-center + justify-between -->
    <div class="flex-cc">
      <!-- flex + items-center + justify-center -->

      <!-- 过渡动画 -->
      <div class="tad-200">
        <!-- transition-all duration-200 -->
        <div class="tad-300">
          <!-- transition-all duration-300 -->

          <!-- 边框 -->
          <div class="border-full-d">完整边框</div>
          <div class="border-b-d">底部边框</div>
        </div>
      </div>
    </div>
  </div>
</div>
```

---

## 九、图标使用

### 9.1 ArtSvgIcon 组件

```vue
<template>
  <!-- 基础使用 -->
  <ArtSvgIcon icon="ri:home-line" />

  <!-- 自定义大小和颜色 -->
  <ArtSvgIcon icon="ri:user-line" class="text-2xl text-primary" />
</template>

<script setup>
import ArtSvgIcon from "@/components/core/base/art-svg-icon/index.vue";
</script>
```

### 9.2 图标库

- **推荐**: [Remix Icon](https://remixicon.com/)
- **Iconify**: [https://icon-sets.iconify.design/ri/](https://icon-sets.iconify.design/ri/)

---

## 十、API 接口规范

### 10.1 基础响应格式

```typescript
// src/types/common/response.ts
interface BaseResponse<T = unknown> {
  code: number;
  msg: string;
  data: T;
}
```

### 10.2 菜单接口返回格式

```typescript
{
  code: 200,
  msg: "success",
  data: [
    {
      name: "Dashboard",
      path: "/dashboard",
      component: "/index/index",
      meta: {
        title: "menus.dashboard.title",
        icon: "ri:pie-chart-line"
      },
      children: [...]
    }
  ]
}
```

### 10.3 分页接口配置

配置文件: `src/utils/table/tableConfig.ts`

```typescript
export const tableConfig = {
  recordFields: ["list", "data", "records", "items", "result", "rows"],
  totalFields: ["total", "count"],
  currentFields: ["current", "page", "pageNum"],
  sizeFields: ["size", "pageSize", "limit"],
  paginationKey: {
    current: "current",
    size: "size",
  },
};
```

---

## 十一、环境变量

| 变量                    | 说明                        |
| ----------------------- | --------------------------- |
| `VITE_VERSION`          | 版本号                      |
| `VITE_PORT`             | 端口号 (默认 3006)          |
| `VITE_BASE_URL`         | 网站地址前缀                |
| `VITE_ACCESS_MODE`      | 权限模式 (frontend/backend) |
| `VITE_WITH_CREDENTIALS` | 是否携带 Cookie             |

---

## 十二、常用脚本命令

| 命令                  | 说明                    |
| --------------------- | ----------------------- |
| `pnpm dev`            | 启动开发服务器          |
| `pnpm build`          | 生产构建                |
| `pnpm lint`           | ESLint 检查             |
| `pnpm fix`            | ESLint 自动修复         |
| `pnpm lint:prettier`  | Prettier 格式化         |
| `pnpm lint:stylelint` | Stylelint 检查          |
| `pnpm commit`         | 规范化提交 (Commitizen) |
| `pnpm clean:dev`      | 精简项目 (清理演示数据) |

---

## 十三、常见问题

### 页面切换空白

**原因**: 组件存在多个根元素
**解决**: 确保 template 只有单个根元素

### 菜单点击自动刷新

**原因**: Vite 依赖预构建优化
**解决**: 在 `vite.config.ts` 的 `optimizeDeps.include` 中添加依赖

### 路由配置错误

**调试**: 打开浏览器控制台 (F12) 查看错误信息

---

## 十四、提交规范

```bash
feat     # 新增功能
fix      # 修复缺陷
docs     # 文档变更
style    # 代码格式
refactor # 代码重构
perf     # 性能优化
test     # 测试相关
build    # 构建流程
ci       # CI 配置
revert   # 回滚 commit
chore    # 其他变更
```

提交流程:

```bash
git add .
pnpm commit
git push
```

---

## 十五、Element Plus 核心组件速查

> **官方文档**: https://element-plus.org/

### 15.1 Table 表格

#### 常用属性

| 属性           | 类型            | 默认值 | 说明                          |
| -------------- | --------------- | ------ | ----------------------------- |
| `data`         | array           | []     | 表格数据                      |
| `height`       | string/number   | —      | 固定表格高度                  |
| `max-height`   | string/number   | —      | 表格最大高度                  |
| `stripe`       | boolean         | false  | 斑马纹样式                    |
| `border`       | boolean         | false  | 纵向边框                      |
| `row-key`      | string/Function | —      | 行数据唯一标识，用于树形/展开 |
| `show-summary` | boolean         | false  | 显示合计行                    |
| `lazy`         | boolean         | false  | 树形数据懒加载                |

#### 表格列属性 (el-table-column)

| 属性       | 类型           | 说明                    |
| ---------- | -------------- | ----------------------- |
| `prop`     | string         | 字段名                  |
| `label`    | string         | 列标题                  |
| `width`    | string/number  | 列宽                    |
| `fixed`    | boolean/string | 固定列 ('left'/'right') |
| `sortable` | boolean/string | 排序 (true/'custom')    |
| `filters`  | Array          | 筛选选项                |
| `type`     | string         | selection/index/expand  |

#### 常用事件

| 事件               | 说明             |
| ------------------ | ---------------- |
| `select`           | 勾选行时触发     |
| `select-all`       | 全选时触发       |
| `selection-change` | 选择项变化时触发 |
| `sort-change`      | 排序变化时触发   |
| `row-click`        | 行点击时触发     |
| `expand-change`    | 展开行变化时触发 |

#### 常用方法 (通过 ref 调用)

| 方法                   | 说明           |
| ---------------------- | -------------- |
| `clearSelection()`     | 清空选择       |
| `toggleRowSelection()` | 切换行选中状态 |
| `toggleAllSelection()` | 切换全选状态   |
| `setCurrentRow()`      | 设置当前选中行 |

---

### 15.2 Form 表单

#### Form 属性

| 属性             | 类型    | 默认值  | 说明                       |
| ---------------- | ------- | ------- | -------------------------- |
| `model`          | object  | —       | **必填**，表单数据对象     |
| `rules`          | object  | —       | 校验规则                   |
| `inline`         | boolean | false   | 行内表单                   |
| `label-position` | string  | 'right' | 标签位置 (top/left/right)  |
| `label-width`    | string  | ''      | 标签宽度                   |
| `size`           | string  | —       | 尺寸 (large/default/small) |
| `disabled`       | boolean | false   | 禁用所有表单项             |

#### FormItem 属性

| 属性       | 类型         | 说明             |
| ---------- | ------------ | ---------------- |
| `prop`     | string       | 字段名，用于验证 |
| `label`    | string       | 标签文本         |
| `required` | boolean      | 是否必填         |
| `rules`    | array/object | 单项验证规则     |
| `error`    | string       | 自定义错误信息   |

#### 表单方法 (通过 ref 调用)

```typescript
const formRef = ref<FormInstance>();

// 验证表单
formRef.value?.validate((valid) => {
  if (valid) {
    /* 提交 */
  }
});

// 重置表单
formRef.value?.resetFields();

// 清除验证
formRef.value?.clearValidate();
```

#### 验证规则示例

```typescript
const rules = {
  name: [
    { required: true, message: "请输入名称", trigger: "blur" },
    { min: 2, max: 20, message: "长度 2-20 个字符", trigger: "blur" },
  ],
  email: [
    { required: true, message: "请输入邮箱", trigger: "blur" },
    { type: "email", message: "邮箱格式不正确", trigger: "blur" },
  ],
  age: [{ type: "number", message: "必须是数字", trigger: "blur" }],
};
```

---

### 15.3 Dialog 对话框

#### 常用属性

| 属性               | 类型     | 默认值 | 说明                       |
| ------------------ | -------- | ------ | -------------------------- |
| `v-model`          | boolean  | false  | 显示/隐藏                  |
| `title`            | string   | ''     | 标题                       |
| `width`            | string   | '50%'  | 宽度                       |
| `fullscreen`       | boolean  | false  | 全屏                       |
| `modal`            | boolean  | true   | 是否显示遮罩层             |
| `draggable`        | boolean  | false  | 可拖拽                     |
| `destroy-on-close` | boolean  | false  | 关闭时销毁内容             |
| `append-to-body`   | boolean  | false  | 追加到 body (嵌套必须开启) |
| `before-close`     | Function | —      | 关闭前回调                 |

#### 插槽

| 插槽名    | 说明       |
| --------- | ---------- |
| `default` | 内容区域   |
| `header`  | 自定义标题 |
| `footer`  | 底部按钮区 |

#### 基础用法

```vue
<el-dialog v-model="visible" title="标题" width="500px">
  <template #default>对话框内容</template>
  <template #footer>
    <el-button @click="visible = false">取消</el-button>
    <el-button type="primary" @click="handleConfirm">确定</el-button>
  </template>
</el-dialog>
```

---

### 15.4 Select 选择器

#### 常用属性

| 属性                    | 类型    | 默认值 | 说明             |
| ----------------------- | ------- | ------ | ---------------- |
| `v-model`               | any     | —      | 绑定值           |
| `multiple`              | boolean | false  | 多选             |
| `disabled`              | boolean | false  | 禁用             |
| `clearable`             | boolean | false  | 可清空           |
| `filterable`            | boolean | false  | 可搜索           |
| `remote`                | boolean | false  | 远程搜索         |
| `remote-method`         | Func    | —      | 远程搜索方法     |
| `collapse-tags`         | boolean | false  | 多选时折叠标签   |
| `collapse-tags-tooltip` | boolean | false  | 悬浮显示折叠标签 |
| `placeholder`           | string  | —      | 占位文本         |

#### Option 属性

| 属性       | 类型    | 说明     |
| ---------- | ------- | -------- |
| `value`    | any     | 选项值   |
| `label`    | string  | 选项标签 |
| `disabled` | boolean | 禁用     |

#### 基础用法

```vue
<el-select v-model="value" placeholder="请选择">
  <el-option
    v-for="item in options"
    :key="item.value"
    :label="item.label"
    :value="item.value"
  />
</el-select>
```

---

### 15.5 Input 输入框

#### 常用属性

| 属性              | 类型          | 默认值 | 说明                     |
| ----------------- | ------------- | ------ | ------------------------ |
| `v-model`         | string/number | —      | 绑定值                   |
| `type`            | string        | 'text' | 类型 (text/textarea/...) |
| `placeholder`     | string        | —      | 占位文本                 |
| `clearable`       | boolean       | false  | 可清空                   |
| `show-password`   | boolean       | false  | 密码框可切换显示         |
| `disabled`        | boolean       | false  | 禁用                     |
| `readonly`        | boolean       | false  | 只读                     |
| `maxlength`       | number        | —      | 最大长度                 |
| `show-word-limit` | boolean       | false  | 显示字数统计             |
| `prefix-icon`     | string/Comp   | —      | 前置图标                 |
| `suffix-icon`     | string/Comp   | —      | 后置图标                 |
| `autosize`        | boolean/obj   | false  | textarea 自适应高度      |

#### 插槽

| 插槽名    | 说明           |
| --------- | -------------- |
| `prefix`  | 输入框头部内容 |
| `suffix`  | 输入框尾部内容 |
| `prepend` | 前置元素       |
| `append`  | 后置元素       |

---

### 15.6 Pagination 分页

#### 常用属性

| 属性                   | 类型    | 默认值               | 说明                   |
| ---------------------- | ------- | -------------------- | ---------------------- |
| `v-model:current-page` | number  | —                    | 当前页                 |
| `v-model:page-size`    | number  | —                    | 每页条数               |
| `total`                | number  | —                    | 总条数                 |
| `page-sizes`           | array   | [10,20,30,40,50,100] | 每页显示个数选择器选项 |
| `layout`               | string  | —                    | 组件布局               |
| `background`           | boolean | false                | 按钮背景色             |
| `hide-on-single-page`  | boolean | false                | 只有一页时隐藏         |

#### Layout 布局值

- `sizes`: 每页条数选择器
- `prev`: 上一页按钮
- `pager`: 页码列表
- `next`: 下一页按钮
- `jumper`: 跳转输入框
- `->`: 右对齐
- `total`: 总条数

#### 基础用法

```vue
<el-pagination
  v-model:current-page="currentPage"
  v-model:page-size="pageSize"
  :total="total"
  :page-sizes="[10, 20, 50, 100]"
  layout="total, sizes, prev, pager, next, jumper"
  @size-change="handleSizeChange"
  @current-change="handleCurrentChange"
/>
```

---

### 15.7 MessageBox 弹框

#### 三种调用方式

```typescript
import { ElMessageBox } from "element-plus";

// Alert 警告框
ElMessageBox.alert("这是一条消息", "标题", {
  confirmButtonText: "确定",
});

// Confirm 确认框
ElMessageBox.confirm("确认删除?", "警告", {
  confirmButtonText: "确定",
  cancelButtonText: "取消",
  type: "warning",
})
  .then(() => {
    // 确认
  })
  .catch(() => {
    // 取消
  });

// Prompt 输入框
ElMessageBox.prompt("请输入名称", "提示", {
  confirmButtonText: "确定",
  cancelButtonText: "取消",
  inputPattern: /^.{1,20}$/,
  inputErrorMessage: "长度 1-20 个字符",
}).then(({ value }) => {
  console.log("输入值:", value);
});
```

#### 常用配置

| 配置                | 类型     | 说明         |
| ------------------- | -------- | ------------ |
| `title`             | string   | 标题         |
| `message`           | string   | 内容         |
| `type`              | string   | 图标类型     |
| `confirmButtonText` | string   | 确认按钮文本 |
| `cancelButtonText`  | string   | 取消按钮文本 |
| `center`            | boolean  | 内容居中     |
| `draggable`         | boolean  | 可拖拽       |
| `beforeClose`       | Function | 关闭前回调   |

---

### 15.8 Upload 上传

#### 常用属性

| 属性          | 类型    | 默认值 | 说明                    |
| ------------- | ------- | ------ | ----------------------- |
| `action`      | string  | —      | **必填**，上传地址      |
| `headers`     | object  | —      | 请求头                  |
| `multiple`    | boolean | false  | 多文件上传              |
| `data`        | object  | —      | 附加参数                |
| `name`        | string  | 'file' | 文件字段名              |
| `drag`        | boolean | false  | 拖拽上传                |
| `accept`      | string  | —      | 接受文件类型            |
| `limit`       | number  | —      | 最大上传数量            |
| `auto-upload` | boolean | true   | 自动上传                |
| `list-type`   | string  | 'text' | 列表类型 (picture-card) |
| `file-list`   | array   | []     | 已上传文件列表          |

#### 钩子函数

| 钩子            | 说明         |
| --------------- | ------------ |
| `before-upload` | 上传前回调   |
| `on-progress`   | 上传进度回调 |
| `on-success`    | 上传成功回调 |
| `on-error`      | 上传失败回调 |
| `on-exceed`     | 超出限制回调 |
| `on-remove`     | 移除文件回调 |

#### 基础用法

```vue
<el-upload
  action="/api/upload"
  :headers="{ Authorization: token }"
  :before-upload="beforeUpload"
  :on-success="handleSuccess"
  :limit="3"
  :on-exceed="handleExceed"
>
  <el-button type="primary">点击上传</el-button>
  <template #tip>
    <div class="el-upload__tip">只能上传 jpg/png 文件，且不超过 500KB</div>
  </template>
</el-upload>
```

---

### 15.9 TreeSelect 树选择器

> 结合了 el-tree 和 el-select 的功能

#### 常用属性

| 属性             | 类型     | 默认值 | 说明                               |
| ---------------- | -------- | ------ | ---------------------------------- |
| `v-model`        | any      | —      | 绑定值                             |
| `data`           | array    | []     | 树数据                             |
| `multiple`       | boolean  | false  | 多选                               |
| `show-checkbox`  | boolean  | false  | 显示复选框                         |
| `check-strictly` | boolean  | false  | 严格模式 (父子不联动)              |
| `filterable`     | boolean  | false  | 可搜索                             |
| `node-key`       | string   | —      | 节点唯一标识                       |
| `props`          | object   | —      | 配置选项 (label/children/disabled) |
| `lazy`           | boolean  | false  | 懒加载                             |
| `load`           | Function | —      | 懒加载回调                         |

#### 基础用法

```vue
<el-tree-select
  v-model="value"
  :data="treeData"
  :props="{ label: 'name', children: 'children' }"
  check-strictly
  placeholder="请选择"
/>
```

---

## 十六、常用 Element Plus 工具函数

### 16.1 ElMessage 消息提示

```typescript
import { ElMessage } from "element-plus";

ElMessage.success("操作成功");
ElMessage.warning("警告信息");
ElMessage.error("错误信息");
ElMessage.info("提示信息");
```

### 16.2 ElNotification 通知

```typescript
import { ElNotification } from "element-plus";

ElNotification({
  title: "成功",
  message: "这是一条成功的提示消息",
  type: "success",
  position: "top-right",
  duration: 3000,
});
```

### 16.3 ElLoading 加载

```typescript
import { ElLoading } from 'element-plus';

// 全屏加载
const loading = ElLoading.service({ fullscreen: true });
// 关闭
loading.close();

// 指令方式
<div v-loading="loading">内容</div>
```
