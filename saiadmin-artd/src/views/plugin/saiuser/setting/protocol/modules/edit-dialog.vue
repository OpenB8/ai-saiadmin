<template>
  <el-dialog
    v-model="visible"
    :title="dialogType === 'add' ? '新增使用协议' : '编辑使用协议'"
    width="800px"
    align-center
    :close-on-click-modal="false"
    @close="handleClose"
  >
    <el-form ref="formRef" :model="formData" :rules="rules" label-width="100px">
      <el-row :gutter="20">
        <el-col :span="12">
          <el-form-item label="协议类型" prop="protocol_type">
            <sa-select
              v-model="formData.protocol_type"
              dict="app_protocol"
              placeholder="请选择协议类型"
            />
          </el-form-item>
        </el-col>
        <el-col :span="12">
          <el-form-item label="状态" prop="status">
            <sa-radio v-model="formData.status" dict="data_status" />
          </el-form-item>
        </el-col>
        <el-col :span="24">
          <el-form-item label="协议标题" prop="title">
            <el-input v-model="formData.title" placeholder="请输入协议标题" />
          </el-form-item>
        </el-col>
        <el-col :span="24">
          <el-form-item label="协议内容" prop="content">
            <sa-editor v-model="formData.content" height="400px" />
          </el-form-item>
        </el-col>
      </el-row>
    </el-form>
    <template #footer>
      <el-button @click="handleClose">取消</el-button>
      <el-button type="primary" @click="handleSubmit">提交</el-button>
    </template>
  </el-dialog>
</template>

<script setup lang="ts">
  import api from '../../../api/setting/protocol'
  import { ElMessage } from 'element-plus'
  import type { FormInstance, FormRules } from 'element-plus'

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
    dialogType: 'add',
    data: undefined
  })
  const emit = defineEmits<Emits>()
  const formRef = ref<FormInstance>()
  const visible = computed({
    get: () => props.modelValue,
    set: (value) => emit('update:modelValue', value)
  })
  const rules = reactive<FormRules>({
    protocol_type: [{ required: true, message: '协议类型必需填写', trigger: 'blur' }],
    title: [{ required: true, message: '协议标题必需填写', trigger: 'blur' }]
  })
  const initialFormData = {
    protocol_type: '',
    status: 1,
    title: '',
    content: '',
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
    if (props.data) {
      await nextTick()
      initForm()
    }
  }
  const initForm = () => {
    if (props.data) {
      for (const key in formData) {
        if (props.data[key] != null && props.data[key] != undefined)
          (formData as any)[key] = props.data[key]
      }
    }
  }
  const handleClose = () => {
    visible.value = false
    formRef.value?.resetFields()
  }
  const handleSubmit = async () => {
    if (!formRef.value) return
    try {
      await formRef.value.validate()
      if (props.dialogType === 'add') {
        await api.save(formData)
        ElMessage.success('新增成功')
      } else {
        await api.update(formData)
        ElMessage.success('修改成功')
      }
      emit('success')
      handleClose()
    } catch (error) {
      console.log('表单验证失败:', error)
    }
  }
</script>
