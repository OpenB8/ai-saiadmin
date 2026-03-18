<template>
  <div class="art-full-height">
    <!-- 搜索面板 -->
    <TableSearch v-model="searchForm" @search="handleSearch" @reset="resetSearchParams" />

    <ElCard class="art-table-card" shadow="never">
      <!-- 表格头部 -->
      <ArtTableHeader v-model:columns="columnChecks" :loading="loading" @refresh="refreshData" />

      <!-- 表格 -->
      <ArtTable
        ref="tableRef"
        rowKey="id"
        :loading="loading"
        :data="data"
        :columns="columns"
        :pagination="pagination"
        @sort-change="handleSortChange"
        @pagination:size-change="handleSizeChange"
        @pagination:current-change="handleCurrentChange"
      />
    </ElCard>
  </div>
</template>

<script setup lang="ts">
  import { useTable } from '@/hooks/core/useTable'
  import api from '../../api/member/rel'
  import TableSearch from './modules/table-search.vue'

  // 搜索表单
  const searchForm = ref({
    username: undefined,
    platform_id: undefined,
    platform_openid: undefined,
    orderType: 'desc'
  })

  // 搜索处理
  const handleSearch = (params: Record<string, any>) => {
    Object.assign(searchParams, params)
    getData()
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
      apiParams: {
        ...searchForm.value
      },
      columnsFactory: () => [
        { prop: 'member_id', label: '会员ID', width: 100 },
        { prop: 'username', label: '会员账号', width: 120 },
        { prop: 'platform_name', label: '平台名称', width: 100 },
        { prop: 'platform_openid', label: '唯一标识' },
        {
          prop: 'is_bind',
          label: '是否绑定',
          saiType: 'dict',
          saiDict: 'yes_or_no',
          width: 100
        },
        { prop: 'bind_time', label: '绑定时间', width: 180 },
        { prop: 'unbind_time', label: '解绑时间', width: 180 }
      ]
    }
  })
</script>
