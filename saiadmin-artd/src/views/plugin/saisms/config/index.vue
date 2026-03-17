<template>
  <div class="art-full-height">
    <!-- Search -->
    <TableSearch v-model="searchForm" @search="handleSearch" @reset="resetSearchParams" />

    <ElCard class="art-table-card" shadow="never">
      <!-- Table Header -->
      <ArtTableHeader v-model:columns="columnChecks" :loading="loading" @refresh="refreshData">
        <template #left>
          <ElSpace wrap>
            <ElButton v-permission="'saisms:config:save'" @click="showDialog('add')" v-ripple>
              <template #icon><ArtSvgIcon icon="ri:add-fill" /></template>
              新增
            </ElButton>
            <ElButton
              v-permission="'saisms:config:destroy'"
              :disabled="selectedRows.length === 0"
              @click="deleteSelectedRows(api.delete, refreshData)"
              v-ripple
            >
              <template #icon><ArtSvgIcon icon="ri:delete-bin-5-line" /></template>
              删除
            </ElButton>
          </ElSpace>
        </template>
      </ArtTableHeader>

      <!-- Table -->
      <ArtTable
        ref="tableRef"
        rowKey="id"
        :loading="loading"
        :data="data"
        :columns="columns"
        :pagination="pagination"
        @sort-change="handleSortChange"
        @selection-change="handleSelectionChange"
        @pagination:size-change="handleSizeChange"
        @pagination:current-change="handleCurrentChange"
      >
        <template #status="{ row }">
          <el-switch
            v-model="row.status"
            :active-value="1"
            :inactive-value="2"
            inline-prompt
            active-text="启用"
            inactive-text="禁用"
            :before-change="() => handleStatusChange(row)"
          />
        </template>
        <template #operation="{ row }">
          <div class="flex gap-2">
            <SaButton
              v-permission="'saisms:config:update'"
              type="secondary"
              @click="showDialog('edit', row)"
            />
            <SaButton
              v-permission="'saisms:config:destroy'"
              type="error"
              @click="deleteRow(row, api.delete, refreshData)"
            />
          </div>
        </template>
      </ArtTable>
    </ElCard>

    <EditDialog
      v-model="dialogVisible"
      :dialog-type="dialogType"
      :data="dialogData"
      @success="refreshData"
    />
  </div>
</template>

<script setup lang="ts">
  import { useTable } from '@/hooks/core/useTable'
  import { useSaiAdmin } from '@/composables/useSaiAdmin'
  import { checkAuth } from '@/utils/tool'
  import api from '../api/config'
  import TableSearch from './modules/table-search.vue'
  import EditDialog from './modules/edit-dialog.vue'

  const searchForm = ref({
    config_name: undefined
  })

  const handleSearch = (params: Record<string, any>) => {
    Object.assign(searchParams, params)
    getData()
  }

  const {
    columns,
    columnChecks,
    data,
    loading,
    getData,
    searchParams,
    pagination,
    resetSearchParams,
    handleSortChange,
    handleSizeChange,
    handleCurrentChange,
    refreshData
  } = useTable({
    core: {
      apiFn: api.list,
      apiParams: { ...searchForm.value },
      columnsFactory: () => [
        { type: 'selection' },
        { prop: 'id', label: 'ID', width: 120 },
        { prop: 'gateway', label: '网关标识' },
        { prop: 'config_name', label: '网关名称' },
        { prop: 'sort', label: '排序' },
        { prop: 'status', label: '状态', useSlot: true },
        { prop: 'operation', label: '操作', width: 120, fixed: 'right', useSlot: true }
      ]
    }
  })

  const {
    dialogType,
    dialogVisible,
    dialogData,
    showDialog,
    deleteRow,
    deleteSelectedRows,
    handleSelectionChange,
    selectedRows
  } = useSaiAdmin()

  const handleStatusChange = async (row: any): Promise<boolean> => {
    if (!checkAuth('saisms:config:changeStatus')) {
      ElMessage.error('您没有权限执行此操作')
      return false
    }
    try {
      await api.changeStatus({ id: row.id, status: row.status === 1 ? 2 : 1 })
      ElMessage.success('操作成功')
      refreshData()
      return true
    } catch {
      return false
    }
  }
</script>
