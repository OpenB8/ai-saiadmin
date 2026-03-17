<template>
  <el-dialog v-model="visible" title="短信发送测试" width="500px" @close="handleClose">
    <el-form ref="formRef" :model="formData" :rules="rules" label-width="100px">
      <el-form-item label="标签名称">
        <el-input v-model="formData.tag_name" disabled />
      </el-form-item>
      <el-form-item label="网关">
        <el-input v-model="formData.gateway" disabled />
      </el-form-item>
      <el-form-item label="手机号" prop="mobile">
        <el-input v-model="formData.mobile" placeholder="请输入测试手机号" />
      </el-form-item>
    </el-form>
    <template #footer>
      <el-button @click="handleClose">取消</el-button>
      <el-button type="primary" :loading="loading" @click="handleSubmit">发送测试</el-button>
    </template>
  </el-dialog>
</template>

<script setup lang="ts">
  import api from '../../api/tag'
  import { ElMessage } from 'element-plus'
  import type { FormInstance, FormRules } from 'element-plus'

  interface Props {
    modelValue: boolean
    data?: Record<string, any>
  }
  const props = withDefaults(defineProps<Props>(), {
    modelValue: false,
    data: undefined
  })
  const emit = defineEmits(['update:modelValue'])

  const formRef = ref<FormInstance>()
  const loading = ref(false)
  const visible = computed({
    get: () => props.modelValue,
    set: (val) => emit('update:modelValue', val)
  })

  const formData = reactive({
    tag_name: '',
    gateway: '',
    mobile: ''
  })

  const rules = reactive<FormRules>({
    mobile: [{ required: true, message: '请输入手机号', trigger: 'blur' }]
  })

  watch(
    () => props.modelValue,
    (newVal) => {
      if (newVal && props.data) {
        formData.tag_name = props.data.tag_name
        formData.gateway = props.data.gateway
        formData.mobile = ''
      }
    }
  )

  const handleClose = () => {
    visible.value = false
    formRef.value?.resetFields()
  }

  const handleSubmit = async () => {
    if (!formRef.value) return
    try {
      await formRef.value.validate()
      loading.value = true
      const res = await api.testTag({
        tag_name: formData.tag_name,
        gateway: formData.gateway,
        mobile: formData.mobile
      })
      if (res.status === 'success') {
        ElMessage.success('发送成功')
      } else {
        ElMessage.warning('发送失败: ' + (res.response || '未知错误'))
      }
      handleClose()
    } catch (e) {
      console.error(e)
    } finally {
      loading.value = false
    }
  }
</script>
