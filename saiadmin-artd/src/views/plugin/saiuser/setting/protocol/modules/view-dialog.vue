<template>
  <el-drawer v-model="visible" size="70%" title="查看详情" :footer="false">
    <el-descriptions :column="1" label-width="100px" border>
      <el-descriptions-item label="协议类型"
        ><sa-dict :value="formData?.protocol_type" dict="app_protocol" render="span"
      /></el-descriptions-item>
      <el-descriptions-item label="协议标题"
        ><div v-text="formData?.title"></div
      ></el-descriptions-item>
      <el-descriptions-item label="状态"
        ><sa-dict :value="formData?.status" dict="data_status" render="span"
      /></el-descriptions-item>
      <el-descriptions-item label="协议内容"
        ><div v-html="formData?.content"></div
      ></el-descriptions-item>
      <el-descriptions-item label="创建时间"
        ><div v-text="formData?.create_time"></div
      ></el-descriptions-item>
      <el-descriptions-item label="更新时间"
        ><div v-text="formData?.update_time"></div
      ></el-descriptions-item>
    </el-descriptions>
  </el-drawer>
</template>

<script setup lang="ts">
  import api from '../../../api/setting/protocol'
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
    protocol_type: '',
    status: 1,
    title: '',
    content: '',
    create_time: '',
    update_time: '',
    id: null
  }
  const formData = reactive({ ...initialFormData })
  watch(
    () => props.modelValue,
    (newVal) => {
      if (newVal) initPage()
    }
  )
  const initPage = async () => {
    Object.assign(formData, initialFormData)
    if (props.data?.id) {
      const resp = await api.read(props.data.id)
      const data = resp.data || resp
      for (const key in formData) {
        if (data[key] != null && data[key] != undefined) (formData as any)[key] = data[key]
      }
    }
  }
</script>
