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
    <el-col v-bind="setSpan(6)">
      <el-form-item label="订单名称" prop="order_name">
        <el-input v-model="formData.order_name" placeholder="请输入订单名称" clearable />
      </el-form-item>
    </el-col>
    <el-col v-bind="setSpan(6)">
      <el-form-item label="订单号" prop="order_no">
        <el-input v-model="formData.order_no" placeholder="请输入订单号" clearable />
      </el-form-item>
    </el-col>
    <el-col v-bind="setSpan(6)">
      <el-form-item label="支付状态" prop="pay_status">
        <el-select v-model="formData.pay_status" placeholder="请选择支付状态" clearable>
          <el-option label="已支付" :value="1" />
          <el-option label="未支付" :value="2" />
        </el-select>
      </el-form-item>
    </el-col>
    <el-col v-bind="setSpan(6)" v-show="isExpanded">
      <el-form-item label="支付方式" prop="pay_method">
        <sa-select
          v-model="formData.pay_method"
          dict="saipay_method"
          placeholder="请选择支付方式"
          clearable
        />
      </el-form-item>
    </el-col>
    <el-col v-bind="setSpan(6)" v-show="isExpanded">
      <el-form-item label="应用名称" prop="plugin">
        <el-input v-model="formData.plugin" placeholder="请输入插件应用名称" clearable />
      </el-form-item>
    </el-col>
    <el-col v-bind="setSpan(12)" v-show="isExpanded">
      <el-form-item label="下单时间" prop="create_time">
        <el-date-picker
          v-model="formData.create_time"
          type="datetimerange"
          range-separator="至"
          start-placeholder="开始时间"
          end-placeholder="结束时间"
          value-format="YYYY-MM-DD HH:mm:ss"
          clearable
        />
      </el-form-item>
    </el-col>
  </sa-search-bar>
</template>

<script setup lang="ts">
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

  // 初始化选项数据
  const initOptions = async () => {}

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
