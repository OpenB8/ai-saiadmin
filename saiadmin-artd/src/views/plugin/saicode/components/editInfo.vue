<template>
  <el-drawer
    v-model="visible"
    :title="`编辑生成信息 - ${record?.table_comment}`"
    size="100%"
    destroy-on-close
    @close="handleClose"
  >
    <div v-loading="loading" element-loading-text="加载数据中...">
      <el-form ref="formRef" :model="form">
        <el-tabs v-model="activeTab">
          <!-- 配置信息 Tab -->
          <el-tab-pane label="配置信息" name="base_config">
            <el-divider content-position="left">基础信息</el-divider>
            <el-row :gutter="24">
              <el-col :span="8">
                <el-form-item label="表名称" prop="table_name" label-width="100px">
                  <el-input v-model="form.table_name" disabled />
                  <div class="text-xs text-gray-400 mt-1">
                    数据库表的名称，自动读取数据库表名称
                  </div>
                </el-form-item>
              </el-col>
              <el-col :span="8">
                <el-form-item
                  label="表描述"
                  prop="table_comment"
                  label-width="100px"
                  :rules="[{ required: true, message: '表描述必填' }]"
                >
                  <el-input v-model="form.table_comment" />
                  <div class="text-xs text-gray-400 mt-1"> 表的描述，自动读取数据库表注释 </div>
                </el-form-item>
              </el-col>
              <el-col :span="8">
                <el-form-item
                  label="实体类"
                  prop="class_name"
                  label-width="100px"
                  :rules="[{ required: true, message: '实体类必填' }]"
                >
                  <el-input v-model="form.class_name" />
                  <div class="text-xs text-gray-400 mt-1"> 生成的实体类名称，可以修改去掉前缀 </div>
                </el-form-item>
              </el-col>
            </el-row>

            <el-row :gutter="24">
              <el-col :span="8">
                <el-form-item
                  label="业务名称"
                  prop="business_name"
                  label-width="100px"
                  :rules="[{ required: true, message: '业务名称必填' }]"
                >
                  <el-input v-model="form.business_name" />
                  <div class="text-xs text-gray-400 mt-1"> 英文、业务名称、同一个分组包下唯一 </div>
                </el-form-item>
              </el-col>
              <el-col :span="8">
                <el-form-item label="数据源" prop="source" label-width="100px">
                  <el-select v-model="form.source" placeholder="请选择数据源" style="width: 100%">
                    <el-option
                      v-for="item in dataSourceList"
                      :key="item.value"
                      :label="item.label"
                      :value="item.value"
                    />
                  </el-select>
                  <div class="text-xs text-gray-400 mt-1"> 数据库配置文件中配置的数据源 </div>
                </el-form-item>
              </el-col>
              <el-col :span="8">
                <el-form-item label="备注信息" prop="remark" label-width="100px">
                  <el-input v-model="form.remark" />
                </el-form-item>
              </el-col>
            </el-row>

            <el-divider content-position="left">生成信息</el-divider>
            <el-row :gutter="24">
              <el-col :xs="24" :md="8" :xl="8">
                <el-form-item
                  label="应用类型"
                  prop="template"
                  label-width="100px"
                  :rules="[{ required: true, message: '应用类型必选' }]"
                >
                  <el-select
                    v-model="form.template"
                    placeholder="请选择生成模板"
                    style="width: 100%"
                    clearable
                  >
                    <el-option label="webman应用[app]" value="app" />
                    <el-option label="webman插件[plugin]" value="plugin" />
                  </el-select>
                  <div class="text-xs text-gray-400 mt-1"
                    >默认app模板,生成文件放app目录下，plugin应用需要先手动初始化</div
                  >
                </el-form-item>
              </el-col>
              <el-col :xs="24" :md="8" :xl="8">
                <el-form-item
                  label="应用名称"
                  prop="namespace"
                  label-width="100px"
                  :rules="[{ required: true, message: '应用名称必填' }]"
                >
                  <el-input v-model="form.namespace" />
                  <div class="text-xs text-gray-400 mt-1">
                    plugin插件名称, 或者app下应用名称, 禁止使用saiadmin
                  </div>
                </el-form-item>
              </el-col>
              <el-col :xs="24" :md="8" :xl="8">
                <el-form-item
                  label="包名"
                  prop="package_name"
                  label-width="100px"
                  :rules="[{ required: true, message: '包名必填' }]"
                >
                  <el-input v-model="form.package_name" placeholder="请输入包名" clearable />
                  <div class="text-xs text-gray-400 mt-1">
                    指定控制器文件所在控制器目录的二级目录名，如：system
                  </div>
                </el-form-item>
              </el-col>
            </el-row>
            <el-row :gutter="24">
              <el-col :xs="24" :md="8" :xl="8">
                <el-form-item
                  label="生成类型"
                  prop="tpl_category"
                  label-width="100px"
                  :rules="[{ required: true, message: '生成类型必填' }]"
                >
                  <el-select
                    v-model="form.tpl_category"
                    placeholder="请选择所属模块"
                    style="width: 100%"
                    clearable
                  >
                    <el-option label="单表CRUD" value="single" />
                    <el-option label="树表CRUD" value="tree" />
                  </el-select>
                  <div class="text-xs text-gray-400 mt-1">
                    单表须有主键，树表须指定id、parent_id、name等字段
                  </div>
                </el-form-item>
              </el-col>
              <el-col :xs="24" :md="8" :xl="8">
                <el-form-item
                  label="生成路径"
                  prop="generate_path"
                  label-width="100px"
                  :rules="[{ required: true, message: '生成路径必填' }]"
                >
                  <el-input v-model="form.generate_path" />
                  <div class="text-xs text-gray-400 mt-1">
                    前端根目录文件夹名称，必须与后端根目录同级
                  </div>
                </el-form-item>
              </el-col>
              <el-col :xs="24" :md="8" :xl="8">
                <el-form-item label="模型类型" prop="stub" label-width="100px">
                  <div class="flex-col">
                    <el-radio-group v-model="form.stub">
                      <el-radio value="think">ThinkOrm</el-radio>
                      <el-radio value="eloquent">EloquentORM</el-radio>
                    </el-radio-group>
                    <div class="text-xs text-gray-400 mt-1">生成不同驱动模型的代码</div>
                  </div>
                </el-form-item>
              </el-col>
            </el-row>
            <el-row :gutter="24">
              <el-col :xs="24" :md="8" :xl="8">
                <el-form-item label="所属菜单" prop="belong_menu_id" label-width="100px">
                  <el-cascader
                    v-model="form.belong_menu_id"
                    :options="menus"
                    :props="{
                      expandTrigger: 'hover',
                      checkStrictly: true,
                      value: 'id',
                      label: 'label'
                    }"
                    style="width: 100%"
                    placeholder="生成功能所属菜单"
                    clearable
                  />
                  <div class="text-xs text-gray-400 mt-1">
                    默认为工具菜单栏目下的子菜单。不选择则为顶级菜单栏目
                  </div>
                </el-form-item>
              </el-col>
              <el-col :xs="24" :md="8" :xl="8">
                <el-form-item
                  label="菜单名称"
                  prop="menu_name"
                  label-width="100px"
                  :rules="[{ required: true, message: '菜单名称必选' }]"
                >
                  <el-input v-model="form.menu_name" placeholder="请输入菜单名称" clearable />
                  <div class="text-xs text-gray-400 mt-1">
                    显示在菜单栏目上的菜单名称、以及代码中的业务功能名称
                  </div>
                </el-form-item>
              </el-col>
            </el-row>
            <el-row :gutter="24">
              <el-col :span="8">
                <el-form-item label="表单样式" prop="component_type" label-width="100px">
                  <div class="flex-col">
                    <el-radio-group v-model="form.component_type">
                      <el-radio-button :value="1">模态框</el-radio-button>
                      <el-radio-button :value="2">抽屉</el-radio-button>
                    </el-radio-group>
                    <div class="text-xs text-gray-400 mt-1">设置新增和修改组件显示方式</div>
                  </div>
                </el-form-item>
              </el-col>
              <el-col :span="8">
                <el-form-item label="表单宽度" prop="form_width" label-width="100px">
                  <div class="flex-col">
                    <el-input-number v-model="form.form_width" :min="200" :max="10000" />
                    <div class="text-xs text-gray-400 mt-1">表单组件的宽度，单位为px</div>
                  </div>
                </el-form-item>
              </el-col>
              <el-col :span="8">
                <el-form-item label="表单全屏" prop="is_full" label-width="100px">
                  <div class="flex-col">
                    <el-radio-group v-model="form.is_full">
                      <el-radio :value="1">否</el-radio>
                      <el-radio :value="2">是</el-radio>
                    </el-radio-group>
                    <div class="text-xs text-gray-400 mt-1">编辑表单是否全屏</div>
                  </div>
                </el-form-item>
              </el-col>
            </el-row>

            <!-- 树表配置 -->
            <template v-if="form.tpl_category === 'tree'">
              <el-divider content-position="left">树表配置</el-divider>
              <el-row :gutter="24">
                <el-col :xs="24" :md="8" :xl="8">
                  <el-form-item label="树主ID" prop="tree_id" label-width="100px">
                    <el-select
                      v-model="formOptions.tree_id"
                      placeholder="请选择树表的主ID"
                      style="width: 100%"
                      clearable
                      filterable
                    >
                      <el-option
                        v-for="(item, index) in form.columns"
                        :key="index"
                        :label="`${item.column_name} - ${item.column_comment}`"
                        :value="item.column_name"
                      />
                    </el-select>
                    <div class="text-xs text-gray-400 mt-1">指定树表的主要ID，一般为主键</div>
                  </el-form-item>
                </el-col>
                <el-col :xs="24" :md="8" :xl="8">
                  <el-form-item label="树父ID" prop="tree_parent_id" label-width="100px">
                    <el-select
                      v-model="formOptions.tree_parent_id"
                      placeholder="请选择树表的父ID"
                      style="width: 100%"
                      clearable
                      filterable
                    >
                      <el-option
                        v-for="(item, index) in form.columns"
                        :key="index"
                        :label="`${item.column_name} - ${item.column_comment}`"
                        :value="item.column_name"
                      />
                    </el-select>
                    <div class="text-xs text-gray-400 mt-1">指定树表的父ID，比如：parent_id</div>
                  </el-form-item>
                </el-col>
                <el-col :xs="24" :md="8" :xl="8">
                  <el-form-item label="树名称" prop="tree_name" label-width="100px">
                    <el-select
                      v-model="formOptions.tree_name"
                      placeholder="请选择树表的名称字段"
                      style="width: 100%"
                      clearable
                      filterable
                    >
                      <el-option
                        v-for="(item, index) in form.columns"
                        :key="index"
                        :label="`${item.column_name} - ${item.column_comment}`"
                        :value="item.column_name"
                      />
                    </el-select>
                    <div class="text-xs text-gray-400 mt-1">指定树显示的名称字段，比如：name</div>
                  </el-form-item>
                </el-col>
              </el-row>
            </template>
          </el-tab-pane>

          <!-- 字段配置 Tab -->
          <el-tab-pane label="字段配置" name="field_config">
            <el-table
              ref="columnTableRef"
              :data="form.columns"
              max-height="750"
              row-key="id"
              border
            >
              <el-table-column width="60" align="center">
                <template #header>
                  <ArtSvgIcon icon="ri:drag-move-2-line" />
                </template>
                <template #default>
                  <div class="drag-handle cursor-move">
                    <ArtSvgIcon icon="ri:drag-move-2-line" />
                  </div>
                </template>
              </el-table-column>
              <el-table-column
                prop="column_name"
                label="字段名称"
                width="200"
                show-overflow-tooltip
              />
              <el-table-column prop="column_comment" label="字段描述" width="200">
                <template #default="{ row }">
                  <el-input v-model="row.column_comment" clearable />
                </template>
              </el-table-column>
              <el-table-column prop="table_field" label="关联显示" width="300">
                <template #default="{ row }">
                  <el-input
                    v-model="row.table_field"
                    placeholder="如: category.category_name"
                    clearable
                  />
                </template>
              </el-table-column>
              <el-table-column prop="column_width" label="列表宽度" width="200">
                <template #default="{ row }">
                  <el-input-number v-model="row.column_width" controls-position="right" />
                </template>
              </el-table-column>
              <el-table-column prop="column_type" label="物理类型" width="150" />
              <el-table-column prop="is_edit" label="查看" width="100" align="center">
                <template #header>
                  <div class="flex-c justify-center items-center gap-1">
                    <span>查看</span>
                    <el-checkbox @change="(val) => handlerAll(val, 'edit')" />
                  </div>
                </template>
                <template #default="{ row }">
                  <el-checkbox v-model="row.is_edit" />
                </template>
              </el-table-column>
              <el-table-column prop="is_list" label="列表" width="100" align="center">
                <template #header>
                  <div class="flex-c justify-center items-center gap-1">
                    <span>列表</span>
                    <el-checkbox @change="(val) => handlerAll(val, 'list')" />
                  </div>
                </template>
                <template #default="{ row }">
                  <el-checkbox v-model="row.is_list" />
                </template>
              </el-table-column>
              <el-table-column prop="is_sort" label="排序" width="100" align="center">
                <template #header>
                  <div class="flex-c justify-center items-center gap-1">
                    <span>排序</span>
                    <el-checkbox @change="(val) => handlerAll(val, 'sort')" />
                  </div>
                </template>
                <template #default="{ row }">
                  <el-checkbox v-model="row.is_sort" />
                </template>
              </el-table-column>
              <el-table-column prop="is_insert" label="表单" width="100" align="center">
                <template #header>
                  <div class="flex-c justify-center items-center gap-1">
                    <span>表单</span>
                    <el-checkbox @change="(val) => handlerAll(val, 'insert')" />
                  </div>
                </template>
                <template #default="{ row }">
                  <el-checkbox v-model="row.is_insert" />
                </template>
              </el-table-column>
              <el-table-column prop="is_query" label="查询" width="100" align="center">
                <template #header>
                  <div class="flex-c justify-center items-center gap-1">
                    <span>查询</span>
                    <el-checkbox @change="(val) => handlerAll(val, 'query')" />
                  </div>
                </template>
                <template #default="{ row }">
                  <el-checkbox v-model="row.is_query" />
                </template>
              </el-table-column>
            </el-table>
          </el-tab-pane>

          <!-- 菜单功能 Tab -->
          <el-tab-pane label="菜单功能" name="menu_config">
            <el-alert type="info" :closable="false"
              >选择不同的功能，将会生成对应功能的代码</el-alert
            >
            <div class="mt-4">
              <el-checkbox-group v-model="form.generate_menus" class="flex flex-col">
                <el-checkbox
                  v-for="(menu, index) in vars.menuList"
                  :key="index"
                  :label="menu.value"
                >
                  {{ menu.name + ' - ' + menu.comment }}
                </el-checkbox>
              </el-checkbox-group>
            </div>
          </el-tab-pane>

          <!-- 关联配置 Tab -->
          <el-tab-pane label="关联配置" name="relation_config">
            <el-alert type="info" :closable="false">
              模型关联支持：一对一、一对多、一对一（反向）、多对多。
            </el-alert>

            <el-button type="primary" class="mt-4 mb-4" @click="addRelation">
              <template #icon>
                <ArtSvgIcon icon="ri:add-line" />
              </template>
              新增关联
            </el-button>

            <div v-for="(item, index) in formOptions.relations" :key="index">
              <el-divider content-position="left">
                {{ item.name ? item.name : '定义新关联' }}
                <el-link type="danger" class="ml-5" @click="delRelation(index)">
                  <ArtSvgIcon icon="ri:delete-bin-line" class="mr-1" />
                  删除定义
                </el-link>
              </el-divider>
              <el-row :gutter="24">
                <el-col :span="8">
                  <el-form-item label="关联类型" label-width="100px">
                    <el-select
                      v-model="item.type"
                      placeholder="请选择关联类型"
                      clearable
                      filterable
                    >
                      <el-option
                        v-for="types in vars.relationsType"
                        :key="types.value"
                        :label="types.name"
                        :value="types.value"
                      />
                    </el-select>
                    <div class="text-xs text-gray-400 mt-1">指定关联类型</div>
                  </el-form-item>
                </el-col>
                <el-col :span="8">
                  <el-form-item label="关联名称" label-width="100px">
                    <el-input v-model="item.name" placeholder="设置关联名称" clearable />
                    <div class="text-xs text-gray-400 mt-1">属性名称，代码中with调用的名称</div>
                  </el-form-item>
                </el-col>

                <el-col :span="8">
                  <el-form-item label="关联模型" label-width="100px">
                    <el-input v-model="item.model" placeholder="设置关联模型" clearable />
                    <div class="text-xs text-gray-400 mt-1">选择要关联的实体模型</div>
                  </el-form-item>
                </el-col>
                <el-col :span="8">
                  <el-form-item
                    :label="
                      item.type === 'belongsTo'
                        ? '外键'
                        : item.type === 'belongsToMany'
                          ? '外键'
                          : '当前模型主键'
                    "
                    label-width="100px"
                  >
                    <el-input v-model="item.localKey" placeholder="设置键名" clearable />
                    <div class="text-xs text-gray-400 mt-1">
                      {{
                        item.type === 'belongsTo'
                          ? '关联模型_id'
                          : item.type === 'belongsToMany'
                            ? '关联模型_id'
                            : '当前模型主键'
                      }}
                    </div>
                  </el-form-item>
                </el-col>

                <el-col v-show="item.type === 'belongsToMany'" :span="8">
                  <el-form-item label="中间模型" label-width="100px">
                    <el-input v-model="item.table" placeholder="请输入中间模型" clearable />
                    <div class="text-xs text-gray-400 mt-1">多对多关联的中间模型</div>
                  </el-form-item>
                </el-col>
                <el-col :span="8">
                  <el-form-item
                    :label="item.type === 'belongsTo' ? '关联主键' : '外键'"
                    label-width="100px"
                  >
                    <el-input v-model="item.foreignKey" placeholder="设置键名" clearable />
                    <div class="text-xs text-gray-400 mt-1">
                      {{ item.type === 'belongsTo' ? '关联模型主键' : '当前模型_id' }}
                    </div>
                  </el-form-item>
                </el-col>
              </el-row>
            </div>
          </el-tab-pane>
        </el-tabs>
      </el-form>
    </div>

    <template #footer>
      <el-button @click="handleClose">取消</el-button>
      <el-button type="primary" :loading="submitLoading" @click="save">保存</el-button>
    </template>
  </el-drawer>
</template>

<script setup lang="ts">
  import { ref, computed, watch, nextTick } from 'vue'
  import { ElMessage } from 'element-plus'
  import type { CheckboxValueType, FormInstance } from 'element-plus'
  import { useDraggable } from 'vue-draggable-plus'

  // 接口导入
  import api from '../api/table'
  import menuApi from '@/api/system/menu'

  // 导入变量
  import * as vars from '../ts/vars'

  interface Props {
    modelValue: boolean
    data?: Record<string, any>
  }

  interface Emits {
    (e: 'update:modelValue', value: boolean): void
    (e: 'success'): void
  }

  const props = withDefaults(defineProps<Props>(), {
    modelValue: false,
    data: undefined
  })

  const emit = defineEmits<Emits>()

  const record = ref<any>({})
  const loading = ref(true)
  const submitLoading = ref(false)
  const activeTab = ref('base_config')
  const formRef = ref<FormInstance>()
  const columnTableRef = ref()

  const form = ref<any>({
    generate_menus: [
      'save',
      'update',
      'read',
      'delete',
      'recycle',
      'recovery',
      'realDestroy',
      'changeStatus'
    ],
    columns: []
  })

  // form扩展组
  const formOptions = ref<any>({
    relations: []
  })
  // 菜单列表
  const menus = ref<any[]>([])
  // 数据源
  const dataSourceList = ref<{ label: string; value: string }[]>([])

  /**
   * 弹窗显示状态双向绑定
   */
  const visible = computed({
    get: () => props.modelValue,
    set: (value) => emit('update:modelValue', value)
  })

  /**
   * 监听弹窗打开，初始化表单数据
   */
  watch(
    () => props.modelValue,
    (newVal) => {
      if (newVal) {
        initPage()
      }
    }
  )

  /**
   * 初始化页面数据
   */
  const initPage = async () => {
    loading.value = true
    // 获取数据源
    const source = await api.getDataSource({})
    dataSourceList.value = source.map((item: any) => ({
      label: item,
      value: item
    }))
    const response = await api.readTable(props.data?.id)
    record.value = response
    initForm()
    loading.value = false
  }

  /**
   * 保存
   */
  const save = async () => {
    if (form.value.namespace === 'saiadmin') {
      ElMessage.error('应用名称不能为saiadmin')
      return
    }

    const validResult = await formRef.value?.validate().catch((err) => err)
    if (validResult !== true) {
      return
    }

    submitLoading.value = true
    try {
      form.value.options = formOptions.value
      // API expects update(id, data) or similar. Check api/table.ts
      await api.update(form.value.id, form.value)
      ElMessage.success('更新成功')
      emit('success')
      handleClose()
    } finally {
      submitLoading.value = false
    }
  }

  /**
   * 全选 / 全不选
   */
  const handlerAll = (value: CheckboxValueType, type: string) => {
    form.value.columns.forEach((item: any) => {
      item['is_' + type] = value
    })
  }

  /**
   * 新增关联定义
   */
  const addRelation = () => {
    formOptions.value.relations.push({
      name: '',
      type: 'hasOne',
      model: '',
      foreignKey: '',
      localKey: '',
      table: ''
    })
  }

  /**
   * 删除关联定义
   */
  const delRelation = (idx: number | string) => {
    formOptions.value.relations.splice(idx, 1)
  }

  /**
   * 初始化数据
   */
  const initForm = () => {
    // 设置form数据
    for (const name in record.value) {
      if (name === 'generate_menus') {
        form.value[name] = record.value[name] ? record.value[name].split(',') : []
      } else {
        form.value[name] = record.value[name]
      }
    }

    if (record.value.options && record.value.options.relations) {
      formOptions.value.relations = record.value.options.relations
    } else {
      formOptions.value.relations = []
    }

    if (record.value.tpl_category === 'tree') {
      formOptions.value.tree_id = record.value.options.tree_id
      formOptions.value.tree_name = record.value.options.tree_name
      formOptions.value.tree_parent_id = record.value.options.tree_parent_id
    }

    // 请求表字段
    api
      .getTableColumns({ table_id: record.value.id, orderField: 'list_sort', orderType: 'asc' })
      .then((data: any) => {
        form.value.columns = []
        data.forEach((item: any, index: number) => {
          item.is_required = item.is_required === 2 ? true : false
          item.is_insert = item.is_insert === 2 ? true : false
          item.is_edit = item.is_edit === 2 ? true : false
          item.is_list = item.is_list === 2 ? true : false
          item.is_query = item.is_query === 2 ? true : false
          item.is_sort = item.is_sort === 2 ? true : false
          // 确保 list_sort 字段有初始值
          item.list_sort = item.list_sort ?? index
          form.value.columns.push(item)
        })
        // 初始化拖动排序
        nextTick(() => {
          initColumnSortable()
        })
      })

    // 请求菜单列表
    menuApi.list({ tree: true, menu: true }).then((data: any) => {
      menus.value = data
      menus.value.unshift({ id: 0, value: 0, label: '顶级菜单' })
    })
  }

  /**
   * 初始化列表字段拖动排序
   */
  const initColumnSortable = () => {
    const tableEl = columnTableRef.value?.$el
    if (!tableEl) return
    const tbody = tableEl.querySelector('.el-table__body-wrapper tbody')
    if (!tbody) return

    useDraggable(tbody, form.value.columns, {
      animation: 200,
      handle: '.drag-handle',
      onEnd: () => {
        // useDraggable 已自动同步数组顺序，只需更新 list_sort 字段
        form.value.columns.forEach((col: any, idx: number) => {
          col.list_sort = idx
        })
      }
    })
  }

  /**
   * 关闭弹窗
   */
  const handleClose = () => {
    visible.value = false
  }
</script>

<style scoped>
  :deep(.el-select-dropdown__item) {
    width: 100%;
  }

  .drag-handle {
    display: flex;
    align-items: center;
    justify-content: center;
    color: #909399;
    transition: color 0.2s;
  }

  .drag-handle:hover {
    color: #409eff;
  }

  .cursor-move {
    cursor: move;
  }
</style>
