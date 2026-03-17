<template>
  <div>
    <el-drawer
      v-model="visible"
      size="100%"
      destroy-on-close
      :show-close="false"
      class="form-design-dialog"
    >
      <template #header>
        <div class="flex justify-between items-center">
          <div class="flex items-center gap-4">
            <span class="text-lg font-bold">表单设计</span>
            <el-divider direction="vertical" />
            <span class="text-sm text-gray-500">拖拽字段调整顺序，点击字段设置属性</span>
          </div>
          <div>
            <el-button @click="handleClose">关闭</el-button>
            <el-button type="primary" @click="handleSave">保存设计</el-button>
          </div>
        </div>
      </template>

      <div class="design-page h-[calc(100vh-100px)] flex">
        <!-- 左侧设计区域 -->
        <div class="design-left flex-1">
          <el-card header="设计区域" shadow="never" class="h-full flex flex-col">
            <div class="flex-1 h-[calc(100vh-220px)] overflow-y-auto p-4">
              <div
                :style="{
                  border: '1px dashed #dcdfe6',
                  padding: '20px',
                  margin: '0 auto',
                  width: formConfig.is_full ? '100%' : formConfig.form_width + 'px',
                  backgroundColor: '#fff',
                  minHeight: '100%'
                }"
                class="shadow-sm"
              >
                <el-form ref="formRef" :model="formData" label-width="auto">
                  <VueDraggable
                    v-model="displayFormColumn"
                    :animation="200"
                    class="el-row flex flex-wrap"
                    style="margin-left: -8px; margin-right: -8px"
                    @end="handleDragEnd"
                  >
                    <el-col
                      v-for="item in displayFormColumn"
                      :key="item.id"
                      :span="item.span ?? formConfig.form_span"
                      :data-id="item.id"
                      class="cursor-move transition-all duration-200"
                      :class="{
                        'ring-2 ring-primary ring-opacity-50 bg-primary-light-9':
                          checkItem.id === item.id
                      }"
                      @click.stop="handleFocus(item)"
                    >
                      <div class="p-2 relative group hover:bg-gray-50 rounded">
                        <!-- 遮罩层，防止表单控件交互干扰拖拽 -->
                        <div class="absolute inset-0 z-10" />

                        <el-form-item
                          :label="item.column_comment"
                          :required="item.is_required == 2"
                          class="!mb-0"
                        >
                          <!-- 密码框 -->
                          <el-input
                            v-if="item.view_type === 'password'"
                            type="password"
                            placeholder="请输入密码"
                            readonly
                          />

                          <!-- 文本域 -->
                          <el-input
                            v-else-if="item.view_type === 'textarea'"
                            type="textarea"
                            placeholder="请输入内容"
                            readonly
                          />

                          <!-- 数字输入 -->
                          <el-input-number
                            v-else-if="item.view_type === 'inputNumber'"
                            v-model="item.default_value"
                            :min="item.options.min"
                            :max="item.options.max"
                            :step="item.options.step"
                            readonly
                          />

                          <!-- 标签输入 -->
                          <el-select
                            v-else-if="item.view_type === 'inputTag'"
                            multiple
                            allow-create
                            filterable
                            default-first-option
                            placeholder="请输入标签"
                            readonly
                          />

                          <!-- 开关 -->
                          <SaSwitch v-else-if="item.view_type === 'switch'" />

                          <!-- 滑块 -->
                          <el-slider
                            v-else-if="item.view_type === 'slider'"
                            v-model="item.default_value"
                            :min="item.options.min"
                            :max="item.options.max"
                            :step="item.options.step"
                          />

                          <!-- 下拉选择 -->
                          <el-select v-else-if="item.view_type === 'select'" placeholder="请选择">
                            <template v-if="item.option && item.option[item.column_name]">
                              <el-option
                                v-for="opt in item.option[item.column_name]"
                                :key="opt[item.options.field_value || 'value']"
                                :label="opt[item.options.field_label || 'label']"
                                :value="opt[item.options.field_value || 'value']"
                              />
                            </template>
                          </el-select>

                          <!-- 字典选择 -->
                          <SaSelect
                            v-else-if="item.view_type === 'saSelect'"
                            :dict="item.dict_type"
                            placeholder="请选择"
                          />

                          <!-- 树形选择 -->
                          <el-tree-select
                            v-else-if="item.view_type === 'treeSelect'"
                            :data="(item.option && item.option[item.column_name]) || []"
                            :props="{
                              label: item.options.field_label || 'label',
                              value: item.options.field_value || 'value'
                            }"
                            placeholder="请选择"
                          />

                          <!-- 单选框 -->
                          <SaRadio
                            v-else-if="item.view_type === 'radio'"
                            v-model="item.default_value"
                            value-type="string"
                            :dict="item.dict_type"
                          />

                          <!-- 复选框 -->
                          <SaCheckbox
                            v-else-if="item.view_type === 'checkbox'"
                            :dict="item.dict_type"
                          />

                          <!-- 日期选择 -->
                          <el-date-picker
                            v-else-if="item.view_type === 'date'"
                            :type="item.options.mode || 'date'"
                            placeholder="请选择日期"
                            class="!w-full"
                          />

                          <!-- 时间选择 -->
                          <el-time-picker
                            v-else-if="item.view_type === 'time'"
                            placeholder="请选择时间"
                            class="!w-full"
                          />

                          <!-- 评分 -->
                          <el-rate v-else-if="item.view_type === 'rate'" allow-half />

                          <!-- 级联选择 -->
                          <el-cascader
                            v-else-if="item.view_type === 'cascader'"
                            :options="(item.option && item.option[item.column_name]) || []"
                            :props="{
                              label: item.options.field_label || 'label',
                              value: item.options.field_value || 'value',
                              checkStrictly: item.options.check_strictly
                            }"
                            placeholder="请选择"
                            class="!w-full"
                          />

                          <!-- 用户选择 -->
                          <SaUser v-else-if="item.view_type === 'userSelect'" :isEcho="true" />

                          <!-- 编辑器 -->
                          <div
                            v-else-if="item.view_type === 'editor'"
                            class="border border-gray-200 p-2 text-gray-400 w-full"
                            :style="{ height: item.options.height + 'px' || '200px' }"
                          >
                            富文本编辑器占位区
                          </div>

                          <!-- 上传 -->
                          <div
                            v-else-if="
                              ['uploadImage', 'imagePicker', 'uploadFile', 'chunkUpload'].includes(
                                item.view_type
                              )
                            "
                            class="border border-dashed border-gray-300 p-4 text-center rounded text-gray-400"
                          >
                            <ArtSvgIcon icon="ri:upload-cloud-2-line" class="text-2xl" />
                            <div class="mt-1">上传组件</div>
                          </div>

                          <!-- 默认输入框 -->
                          <el-input v-else placeholder="请输入" readonly />
                        </el-form-item>
                      </div>
                    </el-col>
                  </VueDraggable>
                </el-form>
              </div>
            </div>
          </el-card>
        </div>

        <!-- 右侧配置区域 -->
        <div class="design-right w-[420px] ml-2">
          <el-card header="配置区域" shadow="never" class="h-full flex flex-col">
            <el-scrollbar class="flex-1">
              <el-form label-width="80px" label-position="left">
                <el-form-item label="表单宽度">
                  <el-input-number v-model="formConfig.form_width" :min="300" :max="10000" />
                </el-form-item>
                <el-form-item label="是否全屏">
                  <el-radio-group v-model="formConfig.is_full">
                    <el-radio :value="true">是</el-radio>
                    <el-radio :value="false">否</el-radio>
                  </el-radio-group>
                </el-form-item>
                <el-form-item label="表单类型">
                  <el-radio-group v-model="formConfig.component_type">
                    <el-radio :value="1">Modal</el-radio>
                    <el-radio :value="2">Drawer</el-radio>
                  </el-radio-group>
                </el-form-item>
                <el-form-item label="默认栅格">
                  <el-radio-group v-model="formConfig.form_span">
                    <el-radio :value="24">1列</el-radio>
                    <el-radio :value="12">2列</el-radio>
                    <el-radio :value="8">3列</el-radio>
                    <el-radio :value="6">4列</el-radio>
                  </el-radio-group>
                </el-form-item>
              </el-form>

              <el-tabs v-model="activeTab" class="mt-4">
                <el-tab-pane label="基础配置" name="1">
                  <div class="tab-div">
                    <template v-if="checkItem.id">
                      <el-divider content-position="left">表单选项</el-divider>
                      <el-form label-width="80px" label-position="left">
                        <el-form-item label="行宽">
                          <el-radio-group v-model="checkItem.span">
                            <el-radio :value="24">1</el-radio>
                            <el-radio :value="12">1/2</el-radio>
                            <el-radio :value="8">1/3</el-radio>
                            <el-radio :value="6">1/4</el-radio>
                          </el-radio-group>
                        </el-form-item>
                        <el-form-item label="标题">
                          <el-input v-model="checkItem.column_comment" />
                        </el-form-item>
                        <el-form-item label="字段">
                          <el-input v-model="checkItem.column_name" disabled />
                        </el-form-item>
                        <el-form-item label="默认值">
                          <el-input-number
                            v-if="checkItem.view_type === 'inputNumber'"
                            v-model="checkItem.default_value"
                          />
                          <el-input v-else v-model="checkItem.default_value" />
                        </el-form-item>
                        <el-form-item label="必填">
                          <el-radio-group v-model="checkItem.is_required">
                            <el-radio :value="2">是</el-radio>
                            <el-radio :value="1">否</el-radio>
                          </el-radio-group>
                        </el-form-item>

                        <el-divider content-position="left">控件选项</el-divider>
                        <el-alert
                          v-if="
                            options.tree_parent_id &&
                            checkItem.column_name === options.tree_parent_id
                          "
                          title="树表上级自动处理树形下拉框,无需配置"
                          type="warning"
                          :closable="false"
                          style="margin-bottom: 10px"
                        />

                        <el-form-item label="控件类型">
                          <el-select v-model="checkItem.view_type" @change="handleTypeChange">
                            <el-option
                              v-for="item in vars.viewComponent"
                              :key="item.value"
                              :label="item.label"
                              :value="item.value"
                            />
                          </el-select>
                        </el-form-item>

                        <!-- Number/Slider Options -->
                        <template
                          v-if="['inputNumber', 'slider', 'rate'].includes(checkItem.view_type)"
                        >
                          <el-form-item label="最小值">
                            <el-input-number v-model="checkItem.options.min" />
                          </el-form-item>
                          <el-form-item label="最大值">
                            <el-input-number v-model="checkItem.options.max" />
                          </el-form-item>
                          <el-form-item label="步长">
                            <el-input-number v-model="checkItem.options.step" />
                          </el-form-item>
                        </template>

                        <!-- Date Options -->
                        <template v-if="['date'].includes(checkItem.view_type)">
                          <el-form-item label="模式">
                            <el-radio-group v-model="checkItem.options.mode">
                              <el-radio value="date">日期</el-radio>
                              <el-radio value="datetime">日期时间</el-radio>
                            </el-radio-group>
                          </el-form-item>
                        </template>

                        <!-- Cascader Options -->
                        <template v-if="['cascader'].includes(checkItem.view_type)">
                          <el-form-item label="严选模式">
                            <el-radio-group v-model="checkItem.options.check_strictly">
                              <el-radio :value="true">是</el-radio>
                              <el-radio :value="false">否</el-radio>
                            </el-radio-group>
                          </el-form-item>
                        </template>

                        <!-- Remote Load Options -->
                        <template
                          v-if="['select', 'cascader', 'treeSelect'].includes(checkItem.view_type)"
                        >
                          <div class="mb-2">
                            <el-button type="primary" plain class="w-full" @click="handleModal">
                              <template #icon><ArtSvgIcon icon="ri:database-2-line" /></template>
                              模型选择
                            </el-button>
                          </div>
                          <el-form-item label="Label字段">
                            <el-input v-model="checkItem.options.field_label" />
                          </el-form-item>
                          <el-form-item label="Value字段">
                            <el-input v-model="checkItem.options.field_value" />
                          </el-form-item>
                          <el-form-item label="请求地址">
                            <el-input v-model="checkItem.options.url" />
                          </el-form-item>
                        </template>

                        <!-- Dict Options -->
                        <template
                          v-if="['saSelect', 'radio', 'checkbox'].includes(checkItem.view_type)"
                        >
                          <el-form-item label="字典">
                            <el-select
                              v-model="checkItem.dict_type"
                              placeholder="选择数据字典"
                              clearable
                              filterable
                            >
                              <el-option
                                v-for="(d, key) in dictStore.dictList"
                                :key="key"
                                :label="key"
                                :value="key"
                              />
                            </el-select>
                          </el-form-item>
                        </template>

                        <!-- Upload Options -->
                        <template
                          v-if="
                            ['uploadImage', 'imagePicker', 'uploadFile', 'chunkUpload'].includes(
                              checkItem.view_type
                            )
                          "
                        >
                          <el-form-item label="是否多选">
                            <el-radio-group v-model="checkItem.options.multiple">
                              <el-radio :value="true">是</el-radio>
                              <el-radio :value="false">否</el-radio>
                            </el-radio-group>
                          </el-form-item>
                          <el-form-item label="数量限制">
                            <el-input-number v-model="checkItem.options.limit" :min="1" />
                          </el-form-item>
                        </template>

                        <!-- Editor Options -->
                        <template v-if="['editor'].includes(checkItem.view_type)">
                          <el-form-item label="高度">
                            <el-input-number v-model="checkItem.options.height" />
                          </el-form-item>
                        </template>
                      </el-form>
                    </template>
                    <el-empty v-else description="请选择左侧字段设置属性" />
                  </div>
                </el-tab-pane>
                <el-tab-pane label="字段操作" name="2">
                  <div class="tab-div flex flex-col gap-2">
                    <div
                      v-for="item in formColumn"
                      :key="item.id"
                      class="flex justify-between items-center p-2 border border-gray-200 rounded hover:bg-gray-50"
                    >
                      <span
                        class="text-sm truncate mr-2"
                        :title="item.column_comment + '(' + item.column_name + ')'"
                      >
                        {{ item.column_comment }}({{ item.column_name }})
                      </span>
                      <el-checkbox v-model="item.insert">显示</el-checkbox>
                    </div>
                  </div>
                </el-tab-pane>
              </el-tabs>
            </el-scrollbar>
          </el-card>
        </div>
      </div>

      <table-modal ref="tableRef" @choose="handleChooseTable" />
    </el-drawer>
  </div>
</template>

<script setup lang="ts">
  import { ref, reactive, computed } from 'vue'
  import { ElMessage, ElMessageBox } from 'element-plus'
  import * as vars from '../ts/vars'
  import api from '../api/table'
  import { useDictStore } from '@/store/modules/dict'
  import tableModal from './table.vue'
  import { VueDraggable } from 'vue-draggable-plus'

  const props = defineProps({
    modelValue: {
      type: Boolean,
      default: false
    },
    data: {
      type: Object,
      default: () => ({})
    }
  })

  const emit = defineEmits(['update:modelValue', 'success'])

  const dictStore = useDictStore()

  const formData = ref({})
  // formColumn 存储所有字段（包括隐藏的）
  const formColumn = ref<any[]>([])
  // displayFormColumn 只存储 insert=true 的字段，用于拖拽
  const displayFormColumn = ref<any[]>([])
  const options = ref<any>({})
  const tableRef = ref()
  const activeTab = ref('1')

  const formConfig = reactive({
    id: undefined,
    form_width: 600,
    is_full: false,
    component_type: 1,
    form_span: 24
  })

  const checkItem = ref<any>({})

  const visible = computed({
    get: () => props.modelValue,
    set: (val) => emit('update:modelValue', val)
  })

  const initOpen = async (data: any) => {
    if (!data || !data.id) return
    const id = data.id
    const table = await api.readTable(id)

    if (table.namespace == '') {
      ElMessage.error('请先编辑配置应用名称')
      return false
    }

    formConfig.id = id
    formConfig.form_width = table.form_width
    formConfig.is_full = table.is_full == 2 ? true : false
    formConfig.component_type = table.component_type
    formConfig.form_span = table.span

    options.value = table.options || {}

    const response = await api.getTableColumns({
      table_id: id,
      orderBy: 'form_sort',
      orderType: 'asc'
    })

    const cols = response.map((item: any, index: number) => {
      // Initialize defaults if missing
      item.options = item.options || {}
      if (
        item.view_type === 'inputNumber' ||
        item.view_type === 'slider' ||
        item.view_type === 'rate'
      ) {
        const obj = { min: 1, max: 100, step: 1 }
        item.options = Object.assign(obj, item.options)
        if (item.default_value) {
          item.default_value = Number.parseInt(item.default_value)
        }
      }
      if (['select', 'cascader', 'treeSelect'].includes(item.view_type)) {
        const obj = { check_strictly: false, field_label: 'label', field_value: 'value', url: '' }
        item.options = Object.assign(obj, item.options)
      }
      if (['uploadImage', 'imagePicker', 'uploadFile', 'chunkUpload'].includes(item.view_type)) {
        const obj = { multiple: false, limit: 1 }
        item.options = Object.assign(obj, item.options)
      }
      if (item.view_type === 'editor') {
        const obj = { height: 400 }
        item.options = Object.assign(obj, item.options)
      }
      if (item.view_type === 'date') {
        const obj = { mode: 'date' }
        item.options = Object.assign(obj, item.options)
      }
      item.insert = item.is_insert == 2 ? true : false
      item.edit = item.is_edit == 2 ? true : false
      item.form_sort = item.form_sort ?? index
      return item
    })

    // 按 form_sort 排序后存储
    formColumn.value = cols.sort((a: any, b: any) => (a.form_sort ?? 0) - (b.form_sort ?? 0))
    // 过滤出 insert=true 的字段用于显示和拖拽
    displayFormColumn.value = formColumn.value.filter((item: any) => item.insert)

    // Select first item if available
    if (displayFormColumn.value.length > 0) {
      checkItem.value = displayFormColumn.value[0]
    }
  }

  // Watch for visible change to init
  import { watch } from 'vue'
  watch(
    () => visible.value,
    (val) => {
      if (val && props.data) {
        initOpen(props.data)
      }
    }
  )

  // 监听 formColumn 中 insert 属性的变化，同步更新 displayFormColumn
  watch(
    () => formColumn.value.map((item: any) => item.insert),
    () => {
      // 过滤出 insert=true 的字段，并保持原有排序
      const newDisplayColumns = formColumn.value.filter((item: any) => item.insert)
      // 保留已存在字段的顺序，新添加的字段放到末尾
      const existingIds = displayFormColumn.value.map((item: any) => item.id)
      const existingColumns = displayFormColumn.value.filter((item: any) => item.insert)
      const newColumns = newDisplayColumns.filter((item: any) => !existingIds.includes(item.id))
      displayFormColumn.value = [...existingColumns, ...newColumns]

      // 如果当前选中的字段被隐藏了，自动选择第一个显示的字段
      if (checkItem.value.id && !checkItem.value.insert) {
        if (displayFormColumn.value.length > 0) {
          checkItem.value = displayFormColumn.value[0]
        } else {
          checkItem.value = {}
        }
      }
    },
    { deep: true }
  )

  const handleFocus = (element: any) => {
    checkItem.value = element
    // Ensure options object exists
    if (!element.options) element.options = {}
  }

  const handleDragEnd = () => {
    // 拖拽结束后，根据 displayFormColumn 的新顺序更新 form_sort
    displayFormColumn.value.forEach((col: any, idx: number) => {
      col.form_sort = (idx + 1) * 100
    })
    // 同步更新 formColumn（将隐藏字段的 form_sort 设置为更大的值，保持在末尾）
    let maxSort = displayFormColumn.value.length * 100
    formColumn.value.forEach((col: any) => {
      if (!col.insert) {
        maxSort += 100
        col.form_sort = maxSort
      }
    })
    // 重新排序 formColumn
    formColumn.value.sort((a: any, b: any) => (a.form_sort ?? 0) - (b.form_sort ?? 0))
  }

  const handleTypeChange = async (val: string) => {
    // Reset options based on new type
    if (val === 'inputNumber' || val === 'slider' || val === 'rate') {
      checkItem.value.options = { min: 1, max: 100, step: 1 }
    } else if (['select', 'cascader', 'treeSelect'].includes(val)) {
      checkItem.value.options = {
        check_strictly: false,
        field_label: 'label',
        field_value: 'value',
        url: ''
      }
    } else if (['uploadImage', 'uploadFile', 'imagePicker', 'chunkUpload'].includes(val)) {
      checkItem.value.options = { multiple: false, limit: 1 }
    } else if (['editor'].includes(val)) {
      checkItem.value.options = { height: 400 }
    } else if (val === 'date') {
      checkItem.value.options = { mode: 'date' }
    } else {
      checkItem.value.options = {}
    }
  }

  const handleModal = async () => {
    tableRef.value?.open()
  }

  const handleChooseTable = async (config: any) => {
    if (checkItem.value.options) {
      checkItem.value.options.field_label = config.field_label
      checkItem.value.options.field_value = config.field_value
      checkItem.value.options.url = config.url
    }
  }

  const handleSave = async () => {
    await api.saveDesign({ table: formConfig, columns: formColumn.value })
    ElMessage.success('保存成功')
    emit('success')
    visible.value = false
  }

  const handleClose = async () => {
    ElMessageBox.confirm(`确定要放弃修改并关闭吗？`, '关闭弹窗', {
      confirmButtonText: '确定',
      cancelButtonText: '取消',
      type: 'error'
    }).then(() => {
      visible.value = false
    })
  }

  defineExpose({ open: initOpen })
</script>
<style lang="scss" scoped>
  .design-left {
    :deep(.el-card__body) {
      padding: 5px;
    }
  }
  .design-right {
    :deep(.el-form-item) {
      margin-bottom: 8px;
    }

    .tab-div {
      max-height: 480px !important;
      padding-left: 10px;
      padding-right: 10px;
      padding-bottom: 10px;
      overflow-y: scroll;
    }
  }
</style>
