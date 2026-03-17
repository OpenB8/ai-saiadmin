<template>
  <el-dialog
    v-model="visible"
    title="选择数据模型"
    fullscreen
    destroy-on-close
    append-to-body
    @close="handleClose"
  >
    <div class="flex h-[calc(100vh-140px)] gap-4">
      <div class="flex-1 flex flex-col h-full overflow-hidden">
        <!-- Search -->
        <div class="mb-4">
          <el-input
            v-model="searchForm.table_name"
            placeholder="请输入数据表名称"
            style="width: 300px"
            @keyup.enter="handleSearch"
            clearable
            @clear="handleSearch"
          >
            <template #append>
              <el-button @click="handleSearch">
                <ArtSvgIcon icon="ri:search-line" />
              </el-button>
            </template>
          </el-input>
        </div>

        <!-- Table -->
        <div class="flex-1 bg-white h-full relative">
          <ArtTable
            ref="tableRef"
            rowKey="id"
            :loading="loading"
            :data="data"
            :columns="columns"
            :pagination="pagination"
            highlight-current-row
            @pagination:size-change="handleSizeChange"
            @pagination:current-change="handleCurrentChange"
            @current-change="handleRowSelect"
          >
            <template #tpl_category="{ row }">
              <el-tag v-if="row.tpl_category === 'single'" type="success">单表CRUD</el-tag>
              <el-tag v-else type="danger">树表CRUD</el-tag>
            </template>
          </ArtTable>
        </div>
      </div>

      <div class="w-[400px] pl-4 flex flex-col">
        <el-card header="配置区域" shadow="never" class="flex-1">
          <el-form label-position="top">
            <el-form-item label="字段Label">
              <el-select
                v-model="config.field_label"
                filterable
                placeholder="请选择"
                class="w-full"
                :disabled="!optionData.length"
              >
                <el-option
                  v-for="item in optionData"
                  :key="item.column_name"
                  :label="`${item.column_comment} - ${item.column_name}`"
                  :value="item.column_name"
                />
              </el-select>
            </el-form-item>
            <el-form-item label="字段Value">
              <el-select
                v-model="config.field_value"
                filterable
                placeholder="请选择"
                class="w-full"
                :disabled="!optionData.length"
              >
                <el-option
                  v-for="item in optionData"
                  :key="item.column_name"
                  :label="`${item.column_comment} - ${item.column_name}`"
                  :value="item.column_name"
                />
              </el-select>
            </el-form-item>
            <el-form-item label="请求地址">
              <el-input v-model="config.url" placeholder="自动生成或手动输入" />
            </el-form-item>
          </el-form>
          <div class="mt-8 flex justify-end gap-3">
            <el-button @click="handleReset">重置</el-button>
            <el-button type="primary" @click="handleChoose">确定</el-button>
          </div>
        </el-card>
      </div>
    </div>
  </el-dialog>
</template>

<script setup lang="ts">
  import { ref, reactive } from 'vue'
  import { useTable } from '@/hooks/core/useTable'
  import api from '../api/table'

  const emit = defineEmits(['choose'])

  const visible = ref(false)
  const optionData = ref<any[]>([])

  const config = reactive({
    field_label: '',
    field_value: '',
    url: ''
  })

  // 搜索表单
  const searchForm = ref({
    table_name: ''
  })

  // 搜索处理
  const handleSearch = () => {
    Object.assign(searchParams, searchForm.value)
    getData()
  }

  // Table Logic
  const {
    columns,
    data,
    loading,
    pagination,
    getData,
    searchParams,
    handleSizeChange,
    handleCurrentChange
  } = useTable({
    core: {
      apiFn: api.getPageList,
      columnsFactory: () => [
        { prop: 'table_name', label: '表名称', minWidth: 150, align: 'left' },
        { prop: 'table_comment', label: '表描述', minWidth: 150, align: 'left' },
        { prop: 'tpl_category', label: '类型', width: 120, useSlot: true },
        { prop: 'template', label: '应用类型', width: 120 },
        { prop: 'namespace', label: '应用名称', width: 120 },
        { prop: 'package_name', label: '包名称', width: 120 },
        { prop: 'class_name', label: '类名称', minWidth: 150 },
        { prop: 'source', label: '数据源', width: 100 }
      ]
    }
  })

  // 单选行
  const handleRowSelect = async (row: any) => {
    if (!row) {
      config.field_label = ''
      config.field_value = ''
      config.url = ''
      optionData.value = []
      return
    }

    config.field_label = ''
    config.field_value = ''

    const resp = await api.getTableColumns({ table_id: row.id })
    optionData.value = resp.data || resp

    let path = ''
    if (row.template === 'plugin') {
      path = '/app/' + row.namespace + '/admin/' + row.package_name + '/' + row.class_name
    } else {
      path = '/' + row.namespace + '/' + row.package_name + '/' + row.class_name
    }
    path = path + '/index'
    if (row.tpl_category !== 'tree') {
      path = path + '?saiType=all'
    }
    config.url = path
  }

  const handleReset = () => {
    config.field_label = ''
    config.field_value = ''
    config.url = ''
    optionData.value = []
  }

  const handleChoose = () => {
    emit('choose', { ...config, columns: optionData.value })
    visible.value = false
  }

  const open = () => {
    visible.value = true
    // optionally refresh data when opening
    // refreshData()
  }

  const handleClose = () => {
    visible.value = false
  }

  defineExpose({ open })
</script>
