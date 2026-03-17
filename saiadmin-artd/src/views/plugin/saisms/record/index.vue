<template>
  <div class="art-full-height">
    <!-- Search -->
    <TableSearch v-model="searchForm" @search="handleSearch" @reset="resetSearchParams" />

    <ElCard class="art-table-card" shadow="never">
      <!-- Table Header -->
      <ArtTableHeader v-model:columns="columnChecks" :loading="loading" @refresh="refreshData">
        <template #left>
          <ElSpace wrap>
            <ElButton
              v-permission="'saisms:record:destroy'"
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
          <el-tag v-if="row.status === 'success'" type="success">成功</el-tag>
          <el-tag v-else-if="row.status === 'failure'" type="danger">失败</el-tag>
          <el-tag v-else-if="row.status === 'unsend'" type="warning">调用失败</el-tag>
        </template>
        <template #operation="{ row }">
          <div class="flex gap-2">
            <SaButton
              v-permission="'saisms:record:destroy'"
              type="error"
              @click="deleteRow(row, api.delete, refreshData)"
            />
          </div>
        </template>
      </ArtTable>
    </ElCard>
  </div>
</template>

<script setup lang="ts">
  import { useTable } from '@/hooks/core/useTable'
  import { useSaiAdmin } from '@/composables/useSaiAdmin'
  import api from '../api/record'
  import TableSearch from './modules/table-search.vue'

  const searchForm = ref({
    gateway: undefined,
    mobile: undefined,
    status: undefined,
    create_time: undefined,
    orderField: 'create_time',
    orderType: 'desc'
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
        { prop: 'create_time', label: '发送时间', width: 180 },
        { prop: 'gateway', label: '网关', width: 120 },
        { prop: 'mobile', label: '手机号码', width: 140 },
        { prop: 'code', label: '验证码', width: 100 },
        { prop: 'status', label: '发送状态', width: 100, useSlot: true },
        { prop: 'is_verify', label: '是否验证', width: 100, saiType: 'dict', saiDict: 'yes_or_no' },
        { prop: 'response', label: '发送结果' },
        { prop: 'operation', label: '操作', width: 80, fixed: 'right', useSlot: true }
      ]
    }
  })

  const { deleteRow, deleteSelectedRows, handleSelectionChange, selectedRows } = useSaiAdmin()
</script>
