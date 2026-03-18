<template>
  <sa-search-bar
    ref="searchBarRef"
    v-model="formData"
    label-width="100px"
    :showExpand="false"
    @reset="handleReset"
    @search="handleSearch"
    @expand="handleExpand"
  >
    <el-col v-bind="setSpan(6)">
      <el-form-item label="配置名称" prop="name">
        <el-input v-model="formData.name" placeholder="请输入配置名称" clearable />
      </el-form-item>
    </el-col>
    <el-col v-bind="setSpan(6)">
      <el-form-item label="平台类型" prop="type">
        <el-select
          v-model="formData.type"
          :options="optionData.aiList"
          placeholder="请选择平台类型"
          clearable
        />
      </el-form-item>
    </el-col>
  </sa-search-bar>
</template>

<script setup lang="ts">
  import api from '../../../api/config/config'

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
    aiList: <any[]>[]
  })

  // 初始化选项数据
  const initOptions = async () => {
    const data = await api.getAiList({})
    optionData.aiList = data
  }

  // 组件挂载时初始化选项数据
  onMounted(() => {
    initOptions()
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

  // 栅格占据的列数
  const setSpan = (span: number) => {
    return {
      span: span,
      xs: 24, // 手机：满宽显示
      sm: span >= 12 ? span : 12, // 平板：大于等于12保持，否则用半宽
      md: span >= 8 ? span : 8, // 中等屏幕：大于等于8保持，否则用三分之一宽
      lg: span,
      xl: span
    }
  }
</script>
