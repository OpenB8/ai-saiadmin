<template>
  <div>
    <el-drawer
      v-model="visible"
      size="100%"
      destroy-on-close
      :show-close="false"
      class="search-design-dialog"
    >
      <template #header>
        <div class="flex justify-between items-center">
          <div class="flex items-center gap-4">
            <span class="text-lg font-bold">搜索设计</span>
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
          <el-card header="搜索区域预览" shadow="never" class="h-full flex flex-col">
            <div class="flex-1 h-[calc(100vh-220px)] overflow-y-auto p-4">
              <div
                :style="{
                  border: '1px dashed #dcdfe6',
                  padding: '20px',
                  margin: '0 auto',
                  backgroundColor: '#fff',
                  minHeight: '200px'
                }"
                class="shadow-sm"
              >
                <el-form ref="formRef" :model="formData" label-width="100px">
                  <el-row :gutter="16">
                    <VueDraggable
                      v-model="displaySearchColumn"
                      :animation="200"
                      class="flex flex-wrap w-full"
                      @end="handleDragEnd"
                    >
                      <el-col
                        v-for="item in displaySearchColumn"
                        :key="item.id"
                        :span="item.query_span ?? 6"
                        :data-id="item.id"
                        class="cursor-move transition-all duration-200 mb-4"
                        :class="{
                          'ring-2 ring-primary ring-opacity-50 bg-primary-light-9':
                            checkItem.id === item.id
                        }"
                        @click.stop="handleFocus(item)"
                      >
                        <div class="p-2 relative group hover:bg-gray-50 rounded">
                          <!-- 遮罩层 -->
                          <div class="absolute inset-0 z-10" />

                          <el-form-item :label="item.column_comment" class="!mb-0">
                            <!-- 下拉选择 -->
                            <el-select
                              v-if="item.query_component === 'select'"
                              placeholder="请选择"
                              class="!w-full"
                            />

                            <!-- 字典选择 -->
                            <SaSelect
                              v-else-if="
                                item.query_component === 'saSelect' ||
                                item.query_component === 'radio'
                              "
                              :dict="item.query_dict"
                              placeholder="请选择"
                              class="!w-full"
                            />

                            <!-- 日期范围选择 -->
                            <el-date-picker
                              v-else-if="
                                item.query_component === 'date' && item.query_type === 'between'
                              "
                              type="daterange"
                              start-placeholder="开始日期"
                              end-placeholder="结束日期"
                              class="!w-full"
                            />

                            <!-- 日期选择 -->
                            <el-date-picker
                              v-else-if="item.query_component === 'date'"
                              :type="item.query_options?.mode || 'date'"
                              placeholder="请选择日期"
                              class="!w-full"
                            />

                            <!-- 级联选择 -->
                            <el-cascader
                              v-else-if="item.query_component === 'cascader'"
                              placeholder="请选择"
                              class="!w-full"
                            />

                            <!-- 默认输入框 -->
                            <el-input v-else placeholder="请输入" readonly />
                          </el-form-item>
                        </div>
                      </el-col>
                    </VueDraggable>
                  </el-row>
                </el-form>

                <el-empty
                  v-if="displaySearchColumn.length === 0"
                  description="暂无搜索字段，请在右侧勾选"
                />
              </div>
            </div>
          </el-card>
        </div>

        <!-- 右侧配置区域 -->
        <div class="design-right w-[420px] ml-2">
          <el-card header="配置区域" shadow="never" class="h-full flex flex-col">
            <el-scrollbar class="flex-1">
              <el-tabs v-model="activeTab" class="mt-2">
                <el-tab-pane label="基础配置" name="1">
                  <div class="tab-div">
                    <template v-if="checkItem.id">
                      <el-divider content-position="left">搜索选项</el-divider>
                      <el-form label-width="80px" label-position="left">
                        <el-form-item label="栅格">
                          <el-radio-group v-model="checkItem.query_span">
                            <el-radio :value="24">1</el-radio>
                            <el-radio :value="6">1/4</el-radio>
                            <el-radio :value="8">1/3</el-radio>
                            <el-radio :value="12">1/2</el-radio>
                          </el-radio-group>
                        </el-form-item>
                        <el-form-item label="标题">
                          <el-input v-model="checkItem.column_comment" />
                        </el-form-item>
                        <el-form-item label="字段">
                          <el-input v-model="checkItem.column_name" disabled />
                        </el-form-item>
                        <el-form-item label="查询方式">
                          <el-select v-model="checkItem.query_type">
                            <el-option
                              v-for="item in vars.queryType"
                              :key="item.value"
                              :label="item.label"
                              :value="item.value"
                            />
                          </el-select>
                        </el-form-item>

                        <el-divider content-position="left">控件选项</el-divider>
                        <el-form-item label="控件类型">
                          <el-select v-model="checkItem.query_component">
                            <el-option
                              v-for="item in vars.searchComponent"
                              :key="item.value"
                              :label="item.label"
                              :value="item.value"
                            />
                          </el-select>
                        </el-form-item>

                        <!-- 字典选项 -->
                        <template v-if="['saSelect', 'radio'].includes(checkItem.query_component)">
                          <el-form-item label="字典">
                            <el-select
                              v-model="checkItem.query_dict"
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

                        <!-- 日期选项 -->
                        <template v-if="checkItem.query_component === 'date'">
                          <el-form-item label="日期类型">
                            <el-select v-model="checkItem.query_options.mode">
                              <el-option label="日期" value="date" />
                              <el-option label="日期时间" value="datetime" />
                            </el-select>
                          </el-form-item>
                        </template>

                        <!-- 级联选项 -->
                        <template v-if="['cascader'].includes(checkItem.query_component)">
                          <el-form-item label="严选模式">
                            <el-radio-group v-model="checkItem.query_options.check_strictly">
                              <el-radio :value="true">是</el-radio>
                              <el-radio :value="false">否</el-radio>
                            </el-radio-group>
                          </el-form-item>
                        </template>

                        <!-- 数据下拉/树形/级联选项 -->
                        <template
                          v-if="
                            ['select', 'cascader', 'treeSelect'].includes(checkItem.query_component)
                          "
                        >
                          <div class="mb-2">
                            <el-button type="primary" plain class="w-full" @click="handleModal">
                              <template #icon><ArtSvgIcon icon="ri:database-2-line" /></template>
                              模型选择
                            </el-button>
                          </div>
                          <el-form-item label="Label字段">
                            <el-input v-model="checkItem.query_options.field_label" />
                          </el-form-item>
                          <el-form-item label="Value字段">
                            <el-input v-model="checkItem.query_options.field_value" />
                          </el-form-item>
                          <el-form-item label="请求地址">
                            <el-input v-model="checkItem.query_options.url" />
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
                      v-for="item in searchColumn"
                      :key="item.id"
                      class="flex justify-between items-center p-2 border border-gray-200 rounded hover:bg-gray-50"
                    >
                      <span
                        class="text-sm truncate mr-2"
                        :title="item.column_comment + '(' + item.column_name + ')'"
                      >
                        {{ item.column_comment }}({{ item.column_name }})
                      </span>
                      <el-checkbox v-model="item.is_query">搜索</el-checkbox>
                    </div>
                  </div>
                </el-tab-pane>
              </el-tabs>
            </el-scrollbar>
          </el-card>
        </div>
      </div>
    </el-drawer>

    <!-- 模型选择弹窗 -->
    <ChooseTable ref="tableRef" @choose="handleChooseTable" />
  </div>
</template>

<script setup lang="ts">
  import { ref, computed } from 'vue'
  import { ElMessage, ElMessageBox } from 'element-plus'
  import * as vars from '../ts/vars'
  import api from '../api/table'
  import { useDictStore } from '@/store/modules/dict'
  import { VueDraggable } from 'vue-draggable-plus'
  import ChooseTable from './table.vue'

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
  // searchColumn 存储所有字段
  const searchColumn = ref<any[]>([])
  // displaySearchColumn 只存储 is_query=true 的字段，用于拖拽
  const displaySearchColumn = ref<any[]>([])
  const options = ref<any>({})
  const activeTab = ref('1')

  const checkItem = ref<any>({})
  const tableRef = ref()

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

    options.value = table.options || {}

    const response = await api.getTableColumns({
      table_id: id,
      orderBy: 'query_sort',
      orderType: 'asc'
    })

    const cols = response.map((item: any, index: number) => {
      // Initialize defaults if missing
      item.query_options = item.query_options || {}
      if (item.query_component === 'date') {
        const obj = { mode: 'date' }
        item.query_options = Object.assign(obj, item.query_options)
      }
      if (['select', 'cascader', 'treeSelect'].includes(item.query_component)) {
        const obj = { check_strictly: false, field_label: 'label', field_value: 'value', url: '' }
        item.query_options = Object.assign(obj, item.query_options)
      }
      item.is_query = item.is_query == 2 ? true : false
      item.query_span = item.query_span ?? 6
      item.query_sort = item.query_sort ?? index
      return item
    })

    // 按 query_sort 排序后存储
    searchColumn.value = cols.sort((a: any, b: any) => (a.query_sort ?? 0) - (b.query_sort ?? 0))
    // 过滤出 is_query=true 的字段用于显示和拖拽
    displaySearchColumn.value = searchColumn.value.filter((item: any) => item.is_query)

    // Select first item if available
    if (displaySearchColumn.value.length > 0) {
      checkItem.value = displaySearchColumn.value[0]
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

  // 监听 searchColumn 中 is_query 属性的变化，同步更新 displaySearchColumn
  watch(
    () => searchColumn.value.map((item: any) => item.is_query),
    () => {
      // 过滤出 is_query=true 的字段，并保持原有排序
      const newDisplayColumns = searchColumn.value.filter((item: any) => item.is_query)
      // 保留已存在字段的顺序，新添加的字段放到末尾
      const existingIds = displaySearchColumn.value.map((item: any) => item.id)
      const existingColumns = displaySearchColumn.value.filter((item: any) => item.is_query)
      const newColumns = newDisplayColumns.filter((item: any) => !existingIds.includes(item.id))
      displaySearchColumn.value = [...existingColumns, ...newColumns]

      // 如果当前选中的字段被隐藏了，自动选择第一个显示的字段
      if (checkItem.value.id && !checkItem.value.is_query) {
        if (displaySearchColumn.value.length > 0) {
          checkItem.value = displaySearchColumn.value[0]
        } else {
          checkItem.value = {}
        }
      }
    },
    { deep: true }
  )

  const handleFocus = (element: any) => {
    checkItem.value = element
    // Ensure query_options object exists
    if (!element.query_options) element.query_options = {}
  }

  const handleDragEnd = () => {
    // 拖拽结束后，根据 displaySearchColumn 的新顺序更新 query_sort
    displaySearchColumn.value.forEach((col, idx) => {
      col.query_sort = (idx + 1) * 100
    })
    // 同步更新 searchColumn（将隐藏字段的 query_sort 设置为更大的值）
    let maxSort = displaySearchColumn.value.length * 100
    searchColumn.value.forEach((col) => {
      if (!col.is_query) {
        maxSort += 100
        col.query_sort = maxSort
      }
    })
    // 重新排序 searchColumn
    searchColumn.value.sort((a, b) => (a.query_sort ?? 0) - (b.query_sort ?? 0))
  }

  // 打开模型选择弹窗
  const handleModal = () => {
    tableRef.value?.open()
  }

  // 选择模型确认
  const handleChooseTable = (config: any) => {
    if (checkItem.value.query_options) {
      checkItem.value.query_options.field_label = config.field_label
      checkItem.value.query_options.field_value = config.field_value
      checkItem.value.query_options.url = config.url
    }
  }

  const handleSave = async () => {
    await api.saveSearchDesign({ table_id: props.data?.id, columns: searchColumn.value })
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
      max-height: 720px !important;
      padding-left: 10px;
      padding-right: 10px;
      padding-bottom: 10px;
      overflow-y: auto;
    }
  }
</style>
