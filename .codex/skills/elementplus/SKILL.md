---
name: Element Plus 组件速查手册
description: Element Plus UI 组件库核心 API 速查，涵盖表格、表单、对话框、选择器等常用组件
---

# Element Plus 组件速查手册

> **官方文档**: https://element-plus.org/
> **生成时间**: 2026-02-08

---

## 一、Table 表格

### 1.1 Table 属性

| 属性                    | 类型            | 默认值 | 说明                          |
| ----------------------- | --------------- | ------ | ----------------------------- |
| `data`                  | array           | []     | 表格数据                      |
| `height`                | string/number   | —      | 固定表格高度                  |
| `max-height`            | string/number   | —      | 表格最大高度                  |
| `stripe`                | boolean         | false  | 斑马纹样式                    |
| `border`                | boolean         | false  | 纵向边框                      |
| `row-key`               | string/Function | —      | 行数据唯一标识，用于树形/展开 |
| `show-summary`          | boolean         | false  | 显示合计行                    |
| `lazy`                  | boolean         | false  | 树形数据懒加载                |
| `default-expand-all`    | boolean         | false  | 默认展开所有行                |
| `highlight-current-row` | boolean         | false  | 高亮当前行                    |

### 1.2 Table-column 属性

| 属性                    | 类型           | 说明                         |
| ----------------------- | -------------- | ---------------------------- |
| `prop`                  | string         | 字段名                       |
| `label`                 | string         | 列标题                       |
| `width`                 | string/number  | 列宽                         |
| `min-width`             | string/number  | 最小列宽                     |
| `fixed`                 | boolean/string | 固定列 ('left'/'right')      |
| `sortable`              | boolean/string | 排序 (true/'custom')         |
| `filters`               | Array          | 筛选选项                     |
| `type`                  | string         | selection/index/expand       |
| `formatter`             | Function       | 格式化函数                   |
| `align`                 | string         | 对齐方式 (left/center/right) |
| `show-overflow-tooltip` | boolean        | 内容过长时显示 tooltip       |

### 1.3 Table 事件

| 事件               | 说明             |
| ------------------ | ---------------- |
| `select`           | 勾选行时触发     |
| `select-all`       | 全选时触发       |
| `selection-change` | 选择项变化时触发 |
| `sort-change`      | 排序变化时触发   |
| `filter-change`    | 筛选变化时触发   |
| `row-click`        | 行点击时触发     |
| `row-dblclick`     | 行双击时触发     |
| `expand-change`    | 展开行变化时触发 |
| `current-change`   | 当前行变化时触发 |

### 1.4 Table 方法

| 方法                   | 说明           |
| ---------------------- | -------------- |
| `clearSelection()`     | 清空选择       |
| `toggleRowSelection()` | 切换行选中状态 |
| `toggleAllSelection()` | 切换全选状态   |
| `setCurrentRow()`      | 设置当前选中行 |
| `clearSort()`          | 清空排序       |
| `clearFilter()`        | 清空筛选       |
| `doLayout()`           | 重新布局       |
| `sort()`               | 手动排序       |

### 1.5 基础用法

```vue
<template>
  <el-table :data="tableData" border stripe>
    <el-table-column type="selection" width="55" />
    <el-table-column prop="name" label="姓名" />
    <el-table-column prop="age" label="年龄" sortable />
    <el-table-column prop="address" label="地址" show-overflow-tooltip />
    <el-table-column label="操作" fixed="right" width="180">
      <template #default="{ row }">
        <el-button size="small" @click="handleEdit(row)">编辑</el-button>
        <el-button size="small" type="danger" @click="handleDelete(row)"
          >删除</el-button
        >
      </template>
    </el-table-column>
  </el-table>
</template>
```

---

## 二、Form 表单

### 2.1 Form 属性

| 属性              | 类型    | 默认值  | 说明                         |
| ----------------- | ------- | ------- | ---------------------------- |
| `model`           | object  | —       | **必填**，表单数据对象       |
| `rules`           | object  | —       | 校验规则                     |
| `inline`          | boolean | false   | 行内表单                     |
| `label-position`  | string  | 'right' | 标签位置 (top/left/right)    |
| `label-width`     | string  | ''      | 标签宽度                     |
| `label-suffix`    | string  | ''      | 标签后缀                     |
| `size`            | string  | —       | 尺寸 (large/default/small)   |
| `disabled`        | boolean | false   | 禁用所有表单项               |
| `show-message`    | boolean | true    | 显示校验错误信息             |
| `inline-message`  | boolean | false   | 行内显示错误信息             |
| `status-icon`     | boolean | false   | 显示校验状态图标             |
| `scroll-to-error` | boolean | false   | 校验失败时滚动到第一个错误项 |

### 2.2 FormItem 属性

| 属性           | 类型         | 说明             |
| -------------- | ------------ | ---------------- |
| `prop`         | string       | 字段名，用于验证 |
| `label`        | string       | 标签文本         |
| `label-width`  | string       | 标签宽度         |
| `required`     | boolean      | 是否必填         |
| `rules`        | array/object | 单项验证规则     |
| `error`        | string       | 自定义错误信息   |
| `show-message` | boolean      | 是否显示错误信息 |

### 2.3 Form 方法

```typescript
const formRef = ref<FormInstance>();

// 验证整个表单
formRef.value?.validate((valid, fields) => {
  if (valid) {
    // 提交表单
  } else {
    console.log("验证失败:", fields);
  }
});

// 验证部分字段
formRef.value?.validateField(["name", "email"]);

// 重置表单
formRef.value?.resetFields();

// 清除验证
formRef.value?.clearValidate();
formRef.value?.clearValidate(["name"]); // 清除指定字段
```

### 2.4 验证规则

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
  age: [
    { type: "number", message: "必须是数字", trigger: "blur" },
    {
      type: "number",
      min: 1,
      max: 120,
      message: "年龄范围 1-120",
      trigger: "blur",
    },
  ],
  phone: [
    { required: true, message: "请输入手机号", trigger: "blur" },
    { pattern: /^1[3-9]\d{9}$/, message: "手机号格式不正确", trigger: "blur" },
  ],
  // 自定义校验
  password: [
    { required: true, message: "请输入密码", trigger: "blur" },
    {
      validator: (rule, value, callback) => {
        if (value.length < 6) {
          callback(new Error("密码至少 6 位"));
        } else {
          callback();
        }
      },
      trigger: "blur",
    },
  ],
};
```

### 2.5 基础用法

```vue
<template>
  <el-form ref="formRef" :model="form" :rules="rules" label-width="100px">
    <el-form-item label="用户名" prop="name">
      <el-input v-model="form.name" />
    </el-form-item>
    <el-form-item label="邮箱" prop="email">
      <el-input v-model="form.email" />
    </el-form-item>
    <el-form-item>
      <el-button type="primary" @click="submitForm">提交</el-button>
      <el-button @click="resetForm">重置</el-button>
    </el-form-item>
  </el-form>
</template>
```

---

## 三、Dialog 对话框

### 3.1 属性

| 属性                    | 类型     | 默认值 | 说明                       |
| ----------------------- | -------- | ------ | -------------------------- |
| `v-model`               | boolean  | false  | 显示/隐藏                  |
| `title`                 | string   | ''     | 标题                       |
| `width`                 | string   | '50%'  | 宽度                       |
| `top`                   | string   | '15vh' | 距离顶部距离               |
| `fullscreen`            | boolean  | false  | 全屏                       |
| `modal`                 | boolean  | true   | 是否显示遮罩层             |
| `close-on-click-modal`  | boolean  | true   | 点击遮罩关闭               |
| `close-on-press-escape` | boolean  | true   | ESC 关闭                   |
| `show-close`            | boolean  | true   | 显示关闭按钮               |
| `draggable`             | boolean  | false  | 可拖拽                     |
| `center`                | boolean  | false  | 标题和底部居中             |
| `align-center`          | boolean  | false  | 整体居中                   |
| `destroy-on-close`      | boolean  | false  | 关闭时销毁内容             |
| `append-to-body`        | boolean  | false  | 追加到 body (嵌套必须开启) |
| `before-close`          | Function | —      | 关闭前回调                 |

### 3.2 插槽

| 插槽名    | 说明       |
| --------- | ---------- |
| `default` | 内容区域   |
| `header`  | 自定义标题 |
| `footer`  | 底部按钮区 |

### 3.3 事件

| 事件     | 说明             |
| -------- | ---------------- |
| `open`   | 对话框打开时触发 |
| `opened` | 打开动画结束触发 |
| `close`  | 对话框关闭时触发 |
| `closed` | 关闭动画结束触发 |

### 3.4 基础用法

```vue
<template>
  <el-button @click="visible = true">打开对话框</el-button>

  <el-dialog v-model="visible" title="标题" width="500px" destroy-on-close>
    <span>对话框内容</span>
    <template #footer>
      <el-button @click="visible = false">取消</el-button>
      <el-button type="primary" @click="handleConfirm">确定</el-button>
    </template>
  </el-dialog>
</template>

<script setup>
const visible = ref(false);
</script>
```

---

## 四、Select 选择器

### 4.1 属性

| 属性                    | 类型     | 默认值 | 说明                   |
| ----------------------- | -------- | ------ | ---------------------- |
| `v-model`               | any      | —      | 绑定值                 |
| `multiple`              | boolean  | false  | 多选                   |
| `disabled`              | boolean  | false  | 禁用                   |
| `clearable`             | boolean  | false  | 可清空                 |
| `filterable`            | boolean  | false  | 可搜索                 |
| `allow-create`          | boolean  | false  | 允许创建新条目         |
| `remote`                | boolean  | false  | 远程搜索               |
| `remote-method`         | Function | —      | 远程搜索方法           |
| `loading`               | boolean  | false  | 加载状态               |
| `collapse-tags`         | boolean  | false  | 多选时折叠标签         |
| `collapse-tags-tooltip` | boolean  | false  | 悬浮显示折叠标签       |
| `max-collapse-tags`     | number   | 1      | 最多显示标签数         |
| `multiple-limit`        | number   | 0      | 多选最大数量 (0不限制) |
| `placeholder`           | string   | —      | 占位文本               |
| `no-data-text`          | string   | —      | 无数据时文本           |
| `no-match-text`         | string   | —      | 搜索无匹配时文本       |

### 4.2 Option 属性

| 属性       | 类型    | 说明     |
| ---------- | ------- | -------- |
| `value`    | any     | 选项值   |
| `label`    | string  | 选项标签 |
| `disabled` | boolean | 禁用     |

### 4.3 事件

| 事件             | 说明            |
| ---------------- | --------------- |
| `change`         | 选中值变化      |
| `visible-change` | 下拉框显示/隐藏 |
| `remove-tag`     | 多选移除标签    |
| `clear`          | 清空            |
| `blur`           | 失焦            |
| `focus`          | 获焦            |

### 4.4 基础用法

```vue
<template>
  <!-- 基础选择 -->
  <el-select v-model="value" placeholder="请选择" clearable>
    <el-option
      v-for="item in options"
      :key="item.value"
      :label="item.label"
      :value="item.value"
    />
  </el-select>

  <!-- 多选 -->
  <el-select v-model="multiValue" multiple collapse-tags>
    <el-option
      v-for="item in options"
      :key="item.value"
      :label="item.label"
      :value="item.value"
    />
  </el-select>

  <!-- 分组 -->
  <el-select v-model="value">
    <el-option-group
      v-for="group in groupOptions"
      :key="group.label"
      :label="group.label"
    >
      <el-option
        v-for="item in group.options"
        :key="item.value"
        :label="item.label"
        :value="item.value"
      />
    </el-option-group>
  </el-select>

  <!-- 远程搜索 -->
  <el-select
    v-model="value"
    filterable
    remote
    :remote-method="remoteSearch"
    :loading="loading"
  >
    <el-option
      v-for="item in options"
      :key="item.value"
      :label="item.label"
      :value="item.value"
    />
  </el-select>
</template>
```

---

## 五、Input 输入框

### 5.1 属性

| 属性              | 类型          | 默认值 | 说明                                     |
| ----------------- | ------------- | ------ | ---------------------------------------- |
| `v-model`         | string/number | —      | 绑定值                                   |
| `type`            | string        | 'text' | 类型 (text/textarea/password/number/...) |
| `placeholder`     | string        | —      | 占位文本                                 |
| `clearable`       | boolean       | false  | 可清空                                   |
| `show-password`   | boolean       | false  | 密码框可切换显示                         |
| `disabled`        | boolean       | false  | 禁用                                     |
| `readonly`        | boolean       | false  | 只读                                     |
| `maxlength`       | number        | —      | 最大长度                                 |
| `minlength`       | number        | —      | 最小长度                                 |
| `show-word-limit` | boolean       | false  | 显示字数统计                             |
| `prefix-icon`     | string/Comp   | —      | 前置图标                                 |
| `suffix-icon`     | string/Comp   | —      | 后置图标                                 |
| `rows`            | number        | 2      | textarea 行数                            |
| `autosize`        | boolean/obj   | false  | textarea 自适应高度                      |
| `resize`          | string        | —      | 缩放 (none/both/horizontal/vertical)     |
| `size`            | string        | —      | 尺寸 (large/default/small)               |

### 5.2 插槽

| 插槽名    | 说明           |
| --------- | -------------- |
| `prefix`  | 输入框头部内容 |
| `suffix`  | 输入框尾部内容 |
| `prepend` | 前置元素       |
| `append`  | 后置元素       |

### 5.3 事件

| 事件     | 说明         |
| -------- | ------------ |
| `input`  | 输入时触发   |
| `change` | 值变化时触发 |
| `blur`   | 失焦         |
| `focus`  | 获焦         |
| `clear`  | 清空         |

### 5.4 方法

| 方法       | 说明     |
| ---------- | -------- |
| `focus()`  | 获取焦点 |
| `blur()`   | 失去焦点 |
| `select()` | 选中文本 |
| `clear()`  | 清空内容 |

### 5.5 基础用法

```vue
<template>
  <!-- 基础输入 -->
  <el-input v-model="input" placeholder="请输入" clearable />

  <!-- 密码框 -->
  <el-input v-model="password" type="password" show-password />

  <!-- 带图标 -->
  <el-input v-model="input" prefix-icon="Search" suffix-icon="Calendar" />

  <!-- 复合输入框 -->
  <el-input v-model="url">
    <template #prepend>https://</template>
    <template #append>.com</template>
  </el-input>

  <!-- 文本域 -->
  <el-input
    v-model="textarea"
    type="textarea"
    :rows="4"
    maxlength="200"
    show-word-limit
  />

  <!-- 自适应高度 -->
  <el-input
    v-model="textarea"
    type="textarea"
    :autosize="{ minRows: 2, maxRows: 6 }"
  />
</template>
```

---

## 六、Pagination 分页

### 6.1 属性

| 属性                   | 类型    | 默认值               | 说明           |
| ---------------------- | ------- | -------------------- | -------------- |
| `v-model:current-page` | number  | —                    | 当前页         |
| `v-model:page-size`    | number  | —                    | 每页条数       |
| `total`                | number  | —                    | 总条数         |
| `page-sizes`           | array   | [10,20,30,40,50,100] | 每页条数选项   |
| `pager-count`          | number  | 7                    | 页码按钮数量   |
| `layout`               | string  | —                    | 组件布局       |
| `background`           | boolean | false                | 按钮背景色     |
| `small`                | boolean | false                | 小尺寸         |
| `disabled`             | boolean | false                | 禁用           |
| `hide-on-single-page`  | boolean | false                | 只有一页时隐藏 |
| `prev-text`            | string  | —                    | 上一页按钮文本 |
| `next-text`            | string  | —                    | 下一页按钮文本 |

### 6.2 Layout 布局值

- `sizes`: 每页条数选择器
- `prev`: 上一页按钮
- `pager`: 页码列表
- `next`: 下一页按钮
- `jumper`: 跳转输入框
- `->`: 右对齐分隔符
- `total`: 总条数
- `slot`: 自定义插槽

### 6.3 事件

| 事件             | 说明           |
| ---------------- | -------------- |
| `size-change`    | 每页条数变化   |
| `current-change` | 当前页变化     |
| `prev-click`     | 上一页按钮点击 |
| `next-click`     | 下一页按钮点击 |

### 6.4 基础用法

```vue
<template>
  <el-pagination
    v-model:current-page="currentPage"
    v-model:page-size="pageSize"
    :total="total"
    :page-sizes="[10, 20, 50, 100]"
    layout="total, sizes, prev, pager, next, jumper"
    background
    @size-change="handleSizeChange"
    @current-change="handleCurrentChange"
  />
</template>

<script setup>
const currentPage = ref(1);
const pageSize = ref(20);
const total = ref(100);

const handleSizeChange = (size) => {
  console.log("每页条数:", size);
};

const handleCurrentChange = (page) => {
  console.log("当前页:", page);
};
</script>
```

---

## 七、MessageBox 弹框

### 7.1 三种调用方式

```typescript
import { ElMessageBox } from "element-plus";

// Alert 警告框 (只有确定按钮)
ElMessageBox.alert("这是一条消息", "标题", {
  confirmButtonText: "确定",
  callback: (action) => {
    console.log(action); // 'confirm'
  },
});

// Confirm 确认框 (确定 + 取消)
ElMessageBox.confirm("确认删除该数据?", "警告", {
  confirmButtonText: "确定",
  cancelButtonText: "取消",
  type: "warning",
})
  .then(() => {
    ElMessage.success("删除成功");
  })
  .catch(() => {
    ElMessage.info("已取消");
  });

// Prompt 输入框
ElMessageBox.prompt("请输入名称", "提示", {
  confirmButtonText: "确定",
  cancelButtonText: "取消",
  inputPattern: /^.{1,20}$/,
  inputErrorMessage: "长度 1-20 个字符",
  inputPlaceholder: "请输入名称",
}).then(({ value }) => {
  console.log("输入值:", value);
});
```

### 7.2 配置项

| 配置                        | 类型      | 说明                                  |
| --------------------------- | --------- | ------------------------------------- |
| `title`                     | string    | 标题                                  |
| `message`                   | string    | 内容 (支持 VNode)                     |
| `type`                      | string    | 图标类型 (success/warning/info/error) |
| `icon`                      | Component | 自定义图标                            |
| `confirmButtonText`         | string    | 确认按钮文本                          |
| `cancelButtonText`          | string    | 取消按钮文本                          |
| `showCancelButton`          | boolean   | 显示取消按钮                          |
| `showConfirmButton`         | boolean   | 显示确认按钮                          |
| `center`                    | boolean   | 内容居中                              |
| `draggable`                 | boolean   | 可拖拽                                |
| `closeOnClickModal`         | boolean   | 点击遮罩关闭                          |
| `closeOnPressEscape`        | boolean   | ESC 关闭                              |
| `beforeClose`               | Function  | 关闭前回调                            |
| `distinguishCancelAndClose` | boolean   | 区分取消和关闭                        |

### 7.3 Prompt 特有配置

| 配置                | 类型     | 说明           |
| ------------------- | -------- | -------------- |
| `inputPlaceholder`  | string   | 输入框占位符   |
| `inputType`         | string   | 输入框类型     |
| `inputValue`        | string   | 输入框初始值   |
| `inputPattern`      | RegExp   | 输入校验正则   |
| `inputValidator`    | Function | 自定义校验函数 |
| `inputErrorMessage` | string   | 校验失败提示   |

---

## 八、Upload 上传

### 8.1 属性

| 属性               | 类型    | 默认值 | 说明                                 |
| ------------------ | ------- | ------ | ------------------------------------ |
| `action`           | string  | —      | **必填**，上传地址                   |
| `headers`          | object  | —      | 请求头                               |
| `method`           | string  | 'post' | 请求方法                             |
| `multiple`         | boolean | false  | 多文件上传                           |
| `data`             | object  | —      | 附加参数                             |
| `name`             | string  | 'file' | 文件字段名                           |
| `with-credentials` | boolean | false  | 发送 cookie                          |
| `drag`             | boolean | false  | 拖拽上传                             |
| `accept`           | string  | —      | 接受文件类型                         |
| `limit`            | number  | —      | 最大上传数量                         |
| `auto-upload`      | boolean | true   | 自动上传                             |
| `show-file-list`   | boolean | true   | 显示文件列表                         |
| `list-type`        | string  | 'text' | 列表类型 (text/picture/picture-card) |
| `file-list`        | array   | []     | 已上传文件列表                       |
| `disabled`         | boolean | false  | 禁用                                 |

### 8.2 钩子函数

| 钩子            | 参数                          | 说明                    |
| --------------- | ----------------------------- | ----------------------- |
| `before-upload` | (rawFile)                     | 上传前，返回 false 阻止 |
| `on-progress`   | (event, uploadFile, files)    | 上传进度                |
| `on-success`    | (response, uploadFile, files) | 上传成功                |
| `on-error`      | (error, uploadFile, files)    | 上传失败                |
| `on-exceed`     | (files, uploadFiles)          | 超出限制                |
| `on-change`     | (uploadFile, uploadFiles)     | 文件状态变化            |
| `on-remove`     | (uploadFile, uploadFiles)     | 移除文件                |
| `on-preview`    | (uploadFile)                  | 点击文件                |
| `before-remove` | (uploadFile, uploadFiles)     | 移除前，返回 false 阻止 |

### 8.3 方法

| 方法             | 说明         |
| ---------------- | ------------ |
| `abort()`        | 取消上传     |
| `submit()`       | 手动上传     |
| `clearFiles()`   | 清空文件列表 |
| `handleStart()`  | 手动选择文件 |
| `handleRemove()` | 手动移除文件 |

### 8.4 基础用法

```vue
<template>
  <!-- 按钮上传 -->
  <el-upload
    action="/api/upload"
    :headers="{ Authorization: token }"
    :before-upload="beforeUpload"
    :on-success="handleSuccess"
    :on-error="handleError"
    :limit="3"
    :on-exceed="handleExceed"
  >
    <el-button type="primary">点击上传</el-button>
    <template #tip>
      <div class="el-upload__tip">只能上传 jpg/png 文件，且不超过 500KB</div>
    </template>
  </el-upload>

  <!-- 照片墙 -->
  <el-upload
    action="/api/upload"
    list-type="picture-card"
    :on-preview="handlePreview"
    :on-remove="handleRemove"
  >
    <el-icon><Plus /></el-icon>
  </el-upload>

  <!-- 拖拽上传 -->
  <el-upload action="/api/upload" drag multiple>
    <el-icon class="el-icon--upload"><upload-filled /></el-icon>
    <div class="el-upload__text">将文件拖到此处，或<em>点击上传</em></div>
  </el-upload>
</template>

<script setup>
const beforeUpload = (file) => {
  const isJPG = file.type === "image/jpeg" || file.type === "image/png";
  const isLt2M = file.size / 1024 / 1024 < 2;
  if (!isJPG) {
    ElMessage.error("只能上传 JPG/PNG 格式!");
  }
  if (!isLt2M) {
    ElMessage.error("文件大小不能超过 2MB!");
  }
  return isJPG && isLt2M;
};
</script>
```

---

## 九、TreeSelect 树选择器

### 9.1 属性

| 属性                    | 类型     | 默认值 | 说明                  |
| ----------------------- | -------- | ------ | --------------------- |
| `v-model`               | any      | —      | 绑定值                |
| `data`                  | array    | []     | 树数据                |
| `multiple`              | boolean  | false  | 多选                  |
| `show-checkbox`         | boolean  | false  | 显示复选框            |
| `check-strictly`        | boolean  | false  | 严格模式 (父子不联动) |
| `check-on-click-node`   | boolean  | false  | 点击节点选中          |
| `filterable`            | boolean  | false  | 可搜索                |
| `filter-method`         | Function | —      | 自定义筛选方法        |
| `node-key`              | string   | —      | 节点唯一标识          |
| `props`                 | object   | —      | 配置选项              |
| `lazy`                  | boolean  | false  | 懒加载                |
| `load`                  | Function | —      | 懒加载回调            |
| `default-expand-all`    | boolean  | false  | 默认展开全部          |
| `default-expanded-keys` | array    | []     | 默认展开节点          |
| `clearable`             | boolean  | false  | 可清空                |
| `placeholder`           | string   | —      | 占位文本              |

### 9.2 Props 配置

```typescript
const props = {
  label: "name", // 节点标签字段
  children: "children", // 子节点字段
  disabled: "disabled", // 禁用字段
  isLeaf: "leaf", // 叶子节点字段 (懒加载)
};
```

### 9.3 基础用法

```vue
<template>
  <el-tree-select
    v-model="value"
    :data="treeData"
    :props="{ label: 'name', children: 'children' }"
    node-key="id"
    check-strictly
    clearable
    placeholder="请选择部门"
  />

  <!-- 多选 + 复选框 -->
  <el-tree-select
    v-model="values"
    :data="treeData"
    multiple
    show-checkbox
    collapse-tags
    collapse-tags-tooltip
  />

  <!-- 懒加载 -->
  <el-tree-select
    v-model="value"
    :data="treeData"
    lazy
    :load="loadNode"
    :props="{ label: 'name', isLeaf: 'leaf' }"
  />
</template>

<script setup>
const treeData = [
  {
    id: 1,
    name: "总公司",
    children: [
      { id: 11, name: "技术部" },
      { id: 12, name: "市场部" },
    ],
  },
];

const loadNode = (node, resolve) => {
  if (node.level === 0) {
    return resolve([{ id: 1, name: "根节点" }]);
  }
  // 异步加载子节点
  setTimeout(() => {
    resolve([
      { id: node.data.id * 10 + 1, name: "子节点1", leaf: true },
      { id: node.data.id * 10 + 2, name: "子节点2", leaf: true },
    ]);
  }, 500);
};
</script>
```

---

## 十、常用工具函数

### 10.1 ElMessage 消息提示

```typescript
import { ElMessage } from "element-plus";

// 四种类型
ElMessage.success("操作成功");
ElMessage.warning("警告信息");
ElMessage.error("错误信息");
ElMessage.info("提示信息");

// 配置项
ElMessage({
  message: "这是一条消息",
  type: "success",
  duration: 3000, // 显示时间，0 为不自动关闭
  showClose: true, // 显示关闭按钮
  center: true, // 文字居中
  grouping: true, // 合并相同消息
});

// 关闭所有
ElMessage.closeAll();
```

### 10.2 ElNotification 通知

```typescript
import { ElNotification } from "element-plus";

ElNotification({
  title: "成功",
  message: "这是一条成功的提示消息",
  type: "success", // success/warning/info/error
  position: "top-right", // top-right/top-left/bottom-right/bottom-left
  duration: 3000, // 显示时间
  showClose: true, // 显示关闭按钮
  offset: 0, // 偏移量
});

// 快捷方式
ElNotification.success({ title: "成功", message: "操作成功" });
ElNotification.error({ title: "错误", message: "操作失败" });

// 关闭所有
ElNotification.closeAll();
```

### 10.3 ElLoading 加载

```typescript
import { ElLoading } from 'element-plus';

// 全屏加载
const loading = ElLoading.service({
  fullscreen: true,
  lock: true,
  text: '加载中...',
  background: 'rgba(0, 0, 0, 0.7)',
});

// 关闭
loading.close();

// 指令方式
<div v-loading="loading" element-loading-text="加载中...">内容</div>
```

---

## 十一、DatePicker 日期选择器

### 11.1 属性

| 属性                | 类型              | 默认值 | 说明             |
| ------------------- | ----------------- | ------ | ---------------- |
| `v-model`           | Date/string/array | —      | 绑定值           |
| `type`              | string            | 'date' | 类型             |
| `format`            | string            | —      | 显示格式         |
| `value-format`      | string            | —      | 绑定值格式       |
| `readonly`          | boolean           | false  | 只读             |
| `disabled`          | boolean           | false  | 禁用             |
| `editable`          | boolean           | true   | 可编辑           |
| `clearable`         | boolean           | true   | 可清空           |
| `placeholder`       | string            | —      | 占位文本         |
| `start-placeholder` | string            | —      | 范围选择开始占位 |
| `end-placeholder`   | string            | —      | 范围选择结束占位 |
| `range-separator`   | string            | '-'    | 范围分隔符       |
| `disabled-date`     | Function          | —      | 禁用日期函数     |
| `shortcuts`         | array             | []     | 快捷选项         |

### 11.2 类型

| type 值         | 说明             |
| --------------- | ---------------- |
| `year`          | 年份选择         |
| `month`         | 月份选择         |
| `date`          | 日期选择         |
| `dates`         | 多日期选择       |
| `week`          | 周选择           |
| `datetime`      | 日期时间选择     |
| `datetimerange` | 日期时间范围选择 |
| `daterange`     | 日期范围选择     |
| `monthrange`    | 月份范围选择     |

### 11.3 基础用法

```vue
<template>
  <!-- 日期选择 -->
  <el-date-picker
    v-model="date"
    type="date"
    placeholder="选择日期"
    format="YYYY-MM-DD"
    value-format="YYYY-MM-DD"
  />

  <!-- 日期时间 -->
  <el-date-picker
    v-model="datetime"
    type="datetime"
    placeholder="选择日期时间"
    value-format="YYYY-MM-DD HH:mm:ss"
  />

  <!-- 日期范围 -->
  <el-date-picker
    v-model="dateRange"
    type="daterange"
    start-placeholder="开始日期"
    end-placeholder="结束日期"
    value-format="YYYY-MM-DD"
    :shortcuts="shortcuts"
  />
</template>

<script setup>
const shortcuts = [
  {
    text: "最近一周",
    value: () => {
      const end = new Date();
      const start = new Date();
      start.setTime(start.getTime() - 3600 * 1000 * 24 * 7);
      return [start, end];
    },
  },
  {
    text: "最近一个月",
    value: () => {
      const end = new Date();
      const start = new Date();
      start.setMonth(start.getMonth() - 1);
      return [start, end];
    },
  },
];
</script>
```

---

## 十二、Cascader 级联选择器

### 12.1 属性

| 属性              | 类型     | 默认值 | 说明            |
| ----------------- | -------- | ------ | --------------- |
| `v-model`         | array    | —      | 绑定值          |
| `options`         | array    | —      | 选项数据        |
| `props`           | object   | —      | 配置选项        |
| `clearable`       | boolean  | false  | 可清空          |
| `show-all-levels` | boolean  | true   | 显示完整路径    |
| `collapse-tags`   | boolean  | false  | 折叠标签 (多选) |
| `separator`       | string   | ' / '  | 分隔符          |
| `filterable`      | boolean  | false  | 可搜索          |
| `filter-method`   | Function | —      | 自定义筛选方法  |
| `disabled`        | boolean  | false  | 禁用            |
| `placeholder`     | string   | —      | 占位文本        |

### 12.2 Props 配置

```typescript
const props = {
  value: "id", // 值字段
  label: "name", // 标签字段
  children: "children", // 子节点字段
  disabled: "disabled", // 禁用字段
  leaf: "leaf", // 叶子节点字段 (懒加载)
  multiple: false, // 多选
  checkStrictly: false, // 可选任意级 (父子不关联)
  emitPath: true, // 返回完整路径
  lazy: false, // 懒加载
  lazyLoad: null, // 懒加载函数
};
```

### 12.3 基础用法

```vue
<template>
  <!-- 基础使用 -->
  <el-cascader
    v-model="value"
    :options="options"
    :props="{ label: 'name', value: 'id' }"
    placeholder="请选择"
    clearable
  />

  <!-- 可选任意级 -->
  <el-cascader
    v-model="value"
    :options="options"
    :props="{ checkStrictly: true }"
  />

  <!-- 多选 -->
  <el-cascader
    v-model="values"
    :options="options"
    :props="{ multiple: true }"
    collapse-tags
  />

  <!-- 动态加载 -->
  <el-cascader v-model="value" :props="lazyProps" />
</template>

<script setup>
const lazyProps = {
  lazy: true,
  lazyLoad(node, resolve) {
    const { level } = node;
    setTimeout(() => {
      const nodes = Array.from({ length: 5 }).map((_, i) => ({
        value: `${level}-${i}`,
        label: `选项 ${level}-${i}`,
        leaf: level >= 2,
      }));
      resolve(nodes);
    }, 500);
  },
};
</script>
```

---

## 十三、Tree 树形控件

### 13.1 属性

| 属性                    | 类型     | 默认值 | 说明             |
| ----------------------- | -------- | ------ | ---------------- |
| `data`                  | array    | []     | 树数据           |
| `node-key`              | string   | —      | 节点唯一标识     |
| `props`                 | object   | —      | 配置选项         |
| `show-checkbox`         | boolean  | false  | 显示复选框       |
| `check-strictly`        | boolean  | false  | 父子不关联       |
| `default-expand-all`    | boolean  | false  | 默认展开全部     |
| `expand-on-click-node`  | boolean  | true   | 点击节点展开     |
| `check-on-click-node`   | boolean  | false  | 点击节点选中     |
| `default-expanded-keys` | array    | []     | 默认展开节点     |
| `default-checked-keys`  | array    | []     | 默认选中节点     |
| `current-node-key`      | any      | —      | 当前选中节点     |
| `highlight-current`     | boolean  | false  | 高亮当前节点     |
| `accordion`             | boolean  | false  | 手风琴模式       |
| `lazy`                  | boolean  | false  | 懒加载           |
| `load`                  | Function | —      | 懒加载函数       |
| `draggable`             | boolean  | false  | 可拖拽           |
| `allow-drag`            | Function | —      | 判断节点能否拖拽 |
| `allow-drop`            | Function | —      | 判断节点能否放置 |
| `filter-node-method`    | Function | —      | 筛选方法         |

### 13.2 方法

| 方法                    | 说明             |
| ----------------------- | ---------------- |
| `filter(value)`         | 筛选节点         |
| `getCheckedNodes()`     | 获取选中节点     |
| `getCheckedKeys()`      | 获取选中节点 key |
| `setCheckedNodes()`     | 设置选中节点     |
| `setCheckedKeys()`      | 设置选中节点 key |
| `getHalfCheckedNodes()` | 获取半选节点     |
| `getCurrentNode()`      | 获取当前节点     |
| `getCurrentKey()`       | 获取当前节点 key |
| `setCurrentNode()`      | 设置当前节点     |
| `setCurrentKey()`       | 设置当前节点 key |
| `getNode()`             | 获取节点         |
| `remove()`              | 删除节点         |
| `append()`              | 追加子节点       |
| `insertBefore()`        | 前插节点         |
| `insertAfter()`         | 后插节点         |

### 13.3 基础用法

```vue
<template>
  <!-- 搜索过滤 -->
  <el-input v-model="filterText" placeholder="输入关键字过滤" />

  <el-tree
    ref="treeRef"
    :data="treeData"
    :props="{ label: 'name', children: 'children' }"
    node-key="id"
    default-expand-all
    show-checkbox
    highlight-current
    :filter-node-method="filterNode"
    @node-click="handleNodeClick"
    @check="handleCheck"
  >
    <template #default="{ node, data }">
      <span class="custom-tree-node">
        <span>{{ node.label }}</span>
        <span>
          <el-button size="small" @click.stop="append(data)">添加</el-button>
          <el-button size="small" @click.stop="remove(node, data)"
            >删除</el-button
          >
        </span>
      </span>
    </template>
  </el-tree>
</template>

<script setup>
const treeRef = ref();
const filterText = ref("");

watch(filterText, (val) => {
  treeRef.value?.filter(val);
});

const filterNode = (value, data) => {
  if (!value) return true;
  return data.name.includes(value);
};
</script>
```
