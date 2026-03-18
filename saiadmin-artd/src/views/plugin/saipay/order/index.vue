<template>
  <div class="art-full-height">
    <!-- 搜索面板 -->
    <TableSearch v-model="searchForm" @search="handleSearch" @reset="resetSearchParams" />

    <ElCard class="art-table-card" shadow="never">
      <!-- 表格头部 -->
      <ArtTableHeader v-model:columns="columnChecks" :loading="loading" @refresh="refreshData">
        <template #left>
          <ElSpace wrap>
            <ElButton v-permission="'saipay:order:save'" @click="showDialog('add')" v-ripple>
              <template #icon>
                <ArtSvgIcon icon="ri:add-fill" />
              </template>
              订单测试
            </ElButton>
            <ElButton
              v-permission="'saipay:order:destroy'"
              :disabled="selectedRows.length === 0"
              @click="deleteSelectedRows(api.delete, refreshData)"
              v-ripple
            >
              <template #icon>
                <ArtSvgIcon icon="ri:delete-bin-5-line" />
              </template>
              删除
            </ElButton>
          </ElSpace>
        </template>
      </ArtTableHeader>

      <!-- 表格 -->
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
        <!-- 下单时间|订单号 -->
        <template #order_no="{ row }">
          <div>
            <span>{{ row.create_time }}</span>
            <span class="px-2">|</span>
            <span>{{ row.order_no }}</span>
          </div>
        </template>
        <template #pay_status="{ row }">
          <ElTag v-if="row.pay_status === 1" type="success">已支付</ElTag>
          <ElTag v-else type="danger">未支付</ElTag>
        </template>

        <!-- 操作列 -->
        <template #operation="{ row }">
          <div class="flex gap-2">
            <SaButton
              v-if="row.pay_status == 2"
              type="primary"
              icon="ri:logout-circle-r-line"
              tool-tip="继续支付"
              @click="handlePay(row)"
            />
            <SaButton
              v-if="row.pay_status == 1"
              type="info"
              icon="ri:loop-left-ai-fill"
              tool-tip="刷新业务通知"
              @click="handleRefresh(row)"
            />
            <SaButton type="success" @click="showViewDialog('view', row)" />
            <SaButton
              v-permission="'saipay:order:destroy'"
              type="error"
              @click="deleteRow(row, api.delete, refreshData)"
            />
          </div>
        </template>
      </ArtTable>
    </ElCard>

    <!-- 编辑弹窗 -->
    <EditDialog
      v-model="dialogVisible"
      :dialog-type="dialogType"
      :data="dialogData"
      @success="refreshData"
    />

    <!-- 查看详情 -->
    <ViewDialog v-model="viewDialogVisible" :dialog-type="dialogType" :data="viewDialogData" />

    <!-- 支付弹窗 -->
    <PayDialog v-model="visible" :data="currentOrder" />
  </div>
</template>

<script setup lang="ts">
  import { useTable } from '@/hooks/core/useTable'
  import { useSaiAdmin } from '@/composables/useSaiAdmin'
  import api from '../api/order'
  import TableSearch from './modules/table-search.vue'
  import EditDialog from './modules/edit-dialog.vue'
  import ViewDialog from './modules/view-dialog.vue'
  import PayDialog from './modules/pay-dialog.vue'

  // 搜索表单
  const searchForm = ref({
    plugin: undefined,
    order_no: undefined,
    order_name: undefined,
    pay_method: undefined,
    pay_status: undefined,
    create_time: null
  })

  // 搜索处理
  const handleSearch = (params: Record<string, any>) => {
    Object.assign(searchParams, params)
    getData()
  }

  // 刷新业务通知处理
  const handleRefresh = async (row: any) => {
    await api.refresh({
      order_no: row.order_no
    })
    ElMessage.success('刷新业务通知成功')
  }

  const visible = ref(false)
  const currentOrder = ref({})

  // 支付处理
  const handlePay = (row: any) => {
    currentOrder.value = {
      order_no: row.order_no,
      order_price: row.order_price
    }
    visible.value = true
  }

  // 表格配置
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
      columnsFactory: () => [
        { type: 'selection' },
        { prop: 'order_no', label: '下单时间|订单号', useSlot: true, minWidth: 200 },
        { prop: 'order_name', label: '订单名称', width: 200 },
        { prop: 'order_price', label: '订单金额', width: 100 },
        { prop: 'pay_price', label: '支付金额', width: 100 },
        { prop: 'plugin', label: '应用名称', width: 100 },
        {
          prop: 'pay_method',
          label: '支付方式',
          saiType: 'dict',
          saiDict: 'saipay_method',
          width: 100
        },
        {
          prop: 'pay_status',
          label: '支付状态',
          width: 100,
          useSlot: true
        },
        { prop: 'operation', label: '操作', width: 140, fixed: 'right', useSlot: true }
      ]
    }
  })

  // 编辑配置
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

  // 查看详情
  const {
    showDialog: showViewDialog,
    dialogVisible: viewDialogVisible,
    dialogData: viewDialogData
  } = useSaiAdmin()
</script>
