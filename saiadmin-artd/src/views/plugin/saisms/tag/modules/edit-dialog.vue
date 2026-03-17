<template>
  <el-dialog
    v-model="visible"
    :title="dialogType === 'add' ? '新增短信标签' : '编辑短信标签'"
    width="600px"
    @close="handleClose"
  >
    <el-form ref="formRef" :model="formData" :rules="rules" label-width="100px">
      <el-form-item label="标签名称" prop="tag_name">
        <el-input v-model="formData.tag_name" placeholder="请输入标签名称" />
      </el-form-item>
      <el-form-item label="选择网关" prop="gateway">
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
      <el-form-item label="模板编号" prop="template_id">
        <el-input v-model="formData.template_id" placeholder="请输入模板编号" />
      </el-form-item>
      <el-form-item label="发送内容" prop="content">
        <el-input
          v-model="formData.content"
          type="textarea"
          :rows="4"
          placeholder="请输入发送内容"
        />
      </el-form-item>
    </el-form>
    <template #footer>
      <el-button @click="handleClose">取消</el-button>
      <el-button type="primary" @click="handleSubmit">提交</el-button>
    </template>
  </el-dialog>
</template>

<script setup lang="ts">
  import api from '../../api/tag'
  import configApi from '../../api/config'
  import { ElMessage } from 'element-plus'
  import type { FormInstance, FormRules } from 'element-plus'

  interface Props {
    modelValue: boolean
    dialogType: string
    data?: Record<string, any>
  }
  const props = withDefaults(defineProps<Props>(), {
    modelValue: false,
    dialogType: 'add',
    data: undefined
  })
  const emit = defineEmits(['update:modelValue', 'success'])

  const formRef = ref<FormInstance>()
  const visible = computed({
    get: () => props.modelValue,
    set: (val) => emit('update:modelValue', val)
  })

  const optionData = reactive({
    gateways: [] as any[]
  })

  const initialFormData = {
    id: undefined,
    tag_name: '',
    gateway: '',
    template_id: '',
    content: ''
  }
  const formData = reactive({ ...initialFormData })

  const rules = reactive<FormRules>({
    tag_name: [{ required: true, message: '标签名称必填', trigger: 'blur' }],
    gateway: [{ required: true, message: '请选择网关', trigger: 'change' }]
  })

  watch(
    () => props.modelValue,
    (newVal) => {
      if (newVal) initPage()
    }
  )

  const initPage = async () => {
    Object.assign(formData, initialFormData)
    // 加载网关列表
    const res = await configApi.list({ saiType: 'all' })
    optionData.gateways = res.data || res
    if (props.data) {
      await nextTick()
      Object.assign(formData, props.data)
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
        ElMessage.success('添加成功')
      } else {
        await api.update(formData)
        ElMessage.success('修改成功')
      }
      emit('success')
      handleClose()
    } catch (e) {
      console.error(e)
    }
  }
</script>
