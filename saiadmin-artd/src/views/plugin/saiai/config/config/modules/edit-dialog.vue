<template>
  <el-dialog
    v-model="visible"
    :title="dialogType === 'add' ? '新增AI配置' : '编辑AI配置'"
    width="600px"
    align-center
    :close-on-click-modal="false"
    @close="handleClose"
  >
    <el-form ref="formRef" :model="formData" :rules="rules" label-width="120px">
      <el-row :gutter="20">
        <el-col :span="24">
          <el-form-item label="配置名称" prop="name">
            <el-input v-model="formData.name" placeholder="请输入配置名称" />
          </el-form-item>
        </el-col>
        <el-col :span="24">
          <el-form-item label="平台类型" prop="type">
            <el-select
              v-model="formData.type"
              :options="optionData.aiList"
              placeholder="请选择平台类型"
              clearable
              @change="handleTypeChange"
            />
          </el-form-item>
        </el-col>
        <el-col :span="24">
          <el-form-item label="模型名称" prop="model">
            <el-select
              v-model="formData.model"
              :options="optionData.modelList"
              placeholder="请选择模型名称"
              clearable
            />
          </el-form-item>
        </el-col>
        <el-col :span="24">
          <el-form-item label="API Key" prop="ai_key">
            <el-input
              v-model="formData.ai_key"
              type="textarea"
              :rows="2"
              placeholder="请输入API Key"
            />
          </el-form-item>
        </el-col>
        <el-col :span="24">
          <el-form-item label="是否默认" prop="is_default">
            <sa-radio v-model="formData.is_default" dict="yes_or_no" />
          </el-form-item>
        </el-col>
        <el-col :span="24">
          <el-form-item label="状态" prop="status">
            <sa-radio v-model="formData.status" dict="data_status" />
          </el-form-item>
        </el-col>
        <el-col :span="24">
          <el-form-item label="备注" prop="remark">
            <el-input v-model="formData.remark" type="textarea" placeholder="请输入备注" />
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
  import api from '../../../api/config/config'
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
  const optionData = reactive({
    aiList: <any[]>[],
    modelList: <any[]>[]
  })

  /**
   * 弹窗显示状态双向绑定
   */
  const visible = computed({
    get: () => props.modelValue,
    set: (value) => emit('update:modelValue', value)
  })

  /**
   * 表单验证规则
   */
  const rules = reactive<FormRules>({
    name: [{ required: true, message: '配置名称必需填写', trigger: 'blur' }],
    type: [{ required: true, message: '平台类型必需填写', trigger: 'blur' }],
    model: [{ required: true, message: '模型名称必需填写', trigger: 'blur' }],
    ai_key: [{ required: true, message: 'API Key必需填写', trigger: 'blur' }],
    is_default: [{ required: true, message: '是否默认必需填写', trigger: 'blur' }]
  })

  /**
   * 初始数据
   */
  const initialFormData = {
    name: '',
    type: '',
    model: '',
    ai_key: '',
    is_default: 2,
    status: 1,
    remark: '',
    id: null
  }

  /**
   * 表单数据
   */
  const formData = reactive({ ...initialFormData })

  /**
   * 监听弹窗打开，初始化表单数据
   */
  watch(
    () => props.modelValue,
    (newVal) => {
      if (newVal) {
        initPage()
      }
    }
  )

  /**
   * 初始化页面数据
   */
  const initPage = async () => {
    // 先重置为初始值
    Object.assign(formData, initialFormData)
    const data = await api.getAiList({})
    optionData.aiList = data
    // 如果有数据，则填充数据
    if (props.data) {
      await nextTick()
      initForm()
    }
  }

  /**
   * 初始化表单数据
   */
  const initForm = () => {
    if (props.data) {
      for (const key in formData) {
        if (props.data[key] != null && props.data[key] != undefined) {
          ;(formData as any)[key] = props.data[key]
        }
      }
      handleTypeChange(formData.type)
    }
  }

  /**
   * 关闭弹窗并重置表单
   */
  const handleClose = () => {
    visible.value = false
    formRef.value?.resetFields()
  }

  /**
   * 提交表单
   */
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
  /**
   * 监听平台类型变化
   */
  const handleTypeChange = async (val: string) => {
    optionData.modelList = []
    if (val) {
      const res = await api.getModel({ platform: val })
      optionData.modelList = res
    }
  }
</script>
