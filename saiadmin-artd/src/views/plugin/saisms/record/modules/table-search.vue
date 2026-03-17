<template>
  <sa-search-bar
    ref="searchBarRef"
    v-model="formData"
    label-width="100px"
    :showExpand="true"
    @reset="handleReset"
    @search="handleSearch"
    @expand="handleExpand"
  >
    <!-- 网关 -->
    <el-col v-bind="setSpan(6)">
      <el-form-item label="网关" prop="gateway">
        <el-select
          v-model="formData.gateway"
          placeholder="请选择网关"
          clearable
          style="width: 100%"
        >
          <el-option
            v-for="item in optionData.gateways"
            :key="item.gateway"
            :label="item.config_name"
            :value="item.gateway"
          />
        </el-select>
      </el-form-item>
    </el-col>

    <!-- 发送状态 -->
    <el-col v-bind="setSpan(6)">
      <el-form-item label="发送状态" prop="status">
        <el-select
          v-model="formData.status"
          placeholder="请选择发送状态"
          clearable
          style="width: 100%"
        >
          <el-option label="成功" value="success" />
          <el-option label="失败" value="failure" />
          <el-option label="调用失败" value="unsend" />
        </el-select>
      </el-form-item>
    </el-col>

    <!-- 手机号码 -->
    <el-col v-bind="setSpan(6)">
      <el-form-item label="手机号码" prop="mobile">
        <el-input v-model="formData.mobile" placeholder="请输入手机号码" clearable />
      </el-form-item>
    </el-col>

    <!-- 发送时间 (展开后显示) -->
    <template v-if="isExpanded">
      <el-col v-bind="setSpan(12)">
        <el-form-item label="发送时间" prop="create_time">
          <el-date-picker
            v-model="formData.create_time"
            type="datetimerange"
            range-separator="至"
            start-placeholder="开始时间"
            end-placeholder="结束时间"
            format="YYYY-MM-DD HH:mm:ss"
            value-format="YYYY-MM-DD HH:mm:ss"
            style="width: 100%"
          />
        </el-form-item>
      </el-col>
    </template>
  </sa-search-bar>
</template>

<script setup lang="ts">
  import configApi from '../../api/config'

  interface Props {
    modelValue: Record<string, any>
  }
  interface Emits {
    (e: 'update:modelValue', value: Record<string, any>): void
    (e: 'search', params: Record<string, any>): void
    (e: 'reset'): void
  }
  const props = defineProps<Props>()
  const emit = defineEmits<Emits>()

  // 展开/收起
  const isExpanded = ref<boolean>(false)

  // 表单数据双向绑定
  const searchBarRef = ref()
  const formData = computed({
    get: () => props.modelValue,
    set: (val) => emit('update:modelValue', val)
  })

  // 选项数据
  const optionData = reactive({
    gateways: [] as any[]
  })

  onMounted(async () => {
    const res = await configApi.list({ saiType: 'all' })
    optionData.gateways = res.data || res
  })

  // 重置
  function handleReset() {
    searchBarRef.value?.ref.resetFields()
    emit('reset')
  }

  // 搜索
  async function handleSearch() {
    emit('search', formData.value)
  }

  // 展开/收起
  function handleExpand(expanded: boolean) {
    isExpanded.value = expanded
  }

  // 栅格占据的列数 (响应式布局)
  const setSpan = (span: number) => {
    return {
      span: span,
      xs: 24,
      sm: span >= 12 ? span : 12,
      md: span >= 8 ? span : 8,
      lg: span,
      xl: span
    }
  }
</script>
