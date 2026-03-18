<template>
  <div class="art-full-height">
    <TableSearch v-model="searchForm" @search="handleSearch" @reset="resetSearchParams" />
    <ElCard class="art-table-card" shadow="never">
      <ArtTableHeader v-model:columns="columnChecks" :loading="loading" @refresh="refreshData">
        <template #left>
          <ElSpace wrap>
            <ElButton v-permission="'saiuser:setting:platform:save'" @click="showDialog('add')" v-ripple>
              <template #icon><ArtSvgIcon icon="ri:add-fill" /></template>新增
            </ElButton>
            <ElButton v-permission="'saiuser:setting:platform:destroy'" :disabled="selectedRows.length === 0" @click="deleteSelectedRows(api.delete, refreshData)" v-ripple>
              <template #icon><ArtSvgIcon icon="ri:delete-bin-5-line" /></template>删除
            </ElButton>
          </ElSpace>
        </template>
      </ArtTableHeader>
      <ArtTable ref="tableRef" rowKey="id" :loading="loading" :data="data" :columns="columns" :pagination="pagination" @sort-change="handleSortChange" @selection-change="handleSelectionChange" @pagination:size-change="handleSizeChange" @pagination:current-change="handleCurrentChange">
        <template #operation="{ row }">
          <div class="flex gap-2">
            <SaButton type="success" @click="showViewDialog('view', row)" />
            <SaButton v-permission="'saiuser:setting:platform:update'" type="secondary" @click="showDialog('edit', row)" />
            <SaButton v-permission="'saiuser:setting:platform:destroy'" type="error" @click="deleteRow(row, api.delete, refreshData)" />
          </div>
        </template>
      </ArtTable>
    </ElCard>
    <EditDialog v-model="dialogVisible" :dialog-type="dialogType" :data="dialogData" @success="refreshData" />
    <ViewDialog v-model="viewDialogVisible" :dialog-type="dialogType" :data="viewDialogData" />
  </div>
</template>

<script setup lang="ts">
  import { useTable } from '@/hooks/core/useTable'
  import { useSaiAdmin } from '@/composables/useSaiAdmin'
  import api from '../../api/setting/platform'
  import TableSearch from './modules/table-search.vue'
  import EditDialog from './modules/edit-dialog.vue'
  import ViewDialog from './modules/view-dialog.vue'

  const searchForm = ref({ platform_name: undefined })
  const handleSearch = (params: Record<string, any>) => { Object.assign(searchParams, params); getData() }

  const { columns, columnChecks, data, loading, getData, searchParams, pagination, resetSearchParams, handleSortChange, handleSizeChange, handleCurrentChange, refreshData } = useTable({
    core: {
      apiFn: api.list,
      columnsFactory: () => [
        { type: 'selection' },
        { prop: 'id', label: '编号', width: 80 },
        { prop: 'platform_name', label: '平台名称' },
        { prop: 'platform_code', label: '平台标识' },
        { prop: 'status', label: '状态', saiType: 'dict', saiDict: 'data_status', width: 100 },
        { prop: 'create_time', label: '创建时间', width: 180 },
        { prop: 'update_time', label: '更新时间', width: 180 },
        { prop: 'operation', label: '操作', width: 140, fixed: 'right', useSlot: true }
      ]
    }
  })

  const { dialogType, dialogVisible, dialogData, showDialog, deleteRow, deleteSelectedRows, handleSelectionChange, selectedRows } = useSaiAdmin()
  const { showDialog: showViewDialog, dialogVisible: viewDialogVisible, dialogData: viewDialogData } = useSaiAdmin()
</script>
