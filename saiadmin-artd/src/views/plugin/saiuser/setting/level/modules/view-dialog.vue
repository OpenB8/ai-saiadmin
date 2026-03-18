<template>
  <el-drawer v-model="visible" size="50%" title="查看详情" :footer="false">
    <el-descriptions :column="1" label-width="100px" border>
      <el-descriptions-item label="等级图标">
        <img :src="formData?.level_icon" style="width: 100px" />
      </el-descriptions-item>
      <el-descriptions-item label="等级名称">
        <div v-text="formData?.level_name"></div>
      </el-descriptions-item>
      <el-descriptions-item label="等级标识">
        <div v-text="formData?.level_code"></div>
      </el-descriptions-item>
      <el-descriptions-item label="升级积分">
        <div v-text="formData?.min_points"></div>
      </el-descriptions-item>
      <el-descriptions-item label="上限积分">
        <div v-text="formData?.max_points"></div>
      </el-descriptions-item>
      <el-descriptions-item label="等级权益">
        <div v-text="formData?.privileges"></div>
      </el-descriptions-item>
      <el-descriptions-item label="排序">
        <div v-text="formData?.sort"></div>
      </el-descriptions-item>
      <el-descriptions-item label="状态">
        <sa-dict :value="formData?.status" dict="data_status" render="span" />
      </el-descriptions-item>
    </el-descriptions>
  </el-drawer>
</template>

<script setup lang="ts">
  import api from '../../../api/setting/level'

  interface Props {
    modelValue: boolean
    dialogType: string
    data?: Record<string, any>
  }

  interface Emits {
    (e: 'update:modelValue', value: boolean): void
    (e: 'success'): void
  }

  const props = withDefaults(defineProps<Props>(), {
    modelValue: false,
    dialogType: 'view',
    data: undefined
  })

  const emit = defineEmits<Emits>()

  const visible = computed({
    get: () => props.modelValue,
    set: (value) => emit('update:modelValue', value)
  })

  const initialFormData = {
    level_name: '',
    level_code: '',
    min_points: null,
    max_points: null,
    level_icon: '',
    privileges: '',
    sort: 100,
    status: 1,
    id: null,
  }

  const formData = reactive({ ...initialFormData })

  watch(
    () => props.modelValue,
    (newVal) => {
      if (newVal) {
        initPage()
      }
    }
  )

  const initPage = async () => {
    Object.assign(formData, initialFormData)
    if (props.data && props.data.id) {
      const resp = await api.read(props.data.id)
      const data = resp.data || resp
      for (const key in formData) {
        if (data[key] != null && data[key] != undefined) {
          ;(formData as any)[key] = data[key]
        }
      }
    }
  }
</script>
