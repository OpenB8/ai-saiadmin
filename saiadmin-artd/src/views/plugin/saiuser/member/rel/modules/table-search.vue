<template>
  <sa-search-bar
    ref="searchBarRef"
    v-model="formData"
    label-width="100px"
    :showExpand="false"
    @reset="handleReset"
    @search="handleSearch"
  >
    <el-col v-bind="setSpan(6)">
      <el-form-item label="会员账号" prop="username">
        <el-input v-model="formData.username" placeholder="请输入会员账号" clearable />
      </el-form-item>
    </el-col>
    <el-col v-bind="setSpan(6)">
      <el-form-item label="登录平台" prop="platform_id">
        <el-select
          v-model="formData.platform_id"
          placeholder="请选择登录平台"
          clearable
        >
          <el-option
            v-for="item in optionData.platform_id"
            :key="item.id"
            :label="item.platform_name"
            :value="item.id"
          />
        </el-select>
      </el-form-item>
    </el-col>
    <el-col v-bind="setSpan(6)">
      <el-form-item label="唯一标识" prop="platform_openid">
        <el-input v-model="formData.platform_openid" placeholder="请输入唯一标识" clearable />
      </el-form-item>
    </el-col>
  </sa-search-bar>
</template>

<script setup lang="ts">
  import platformApi from '../../../api/setting/platform'

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

  const searchBarRef = ref()
  const formData = computed({
    get: () => props.modelValue,
    set: (val) => emit('update:modelValue', val)
  })

  const optionData = reactive({
    platform_id: <any[]>[],
  })

  const initOptions = async () => {
    const resp = await platformApi.list({ saiType: 'all' })
    optionData.platform_id = resp.data || resp
  }

  onMounted(() => {
    initOptions()
  })

  function handleReset() {
    searchBarRef.value?.ref.resetFields()
    emit('reset')
  }

  async function handleSearch() {
    emit('search', formData.value)
  }

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
