<template>
  <el-dialog v-model="visible" title="编辑站点配置" width="800px" align-center :close-on-click-modal="false" @close="handleClose">
    <el-form ref="formRef" :model="formData" :rules="rules" label-width="100px">
      <el-row :gutter="20">
        <el-col :span="24">
          <el-form-item label="站点名称" prop="site_name">
            <el-input v-model="formData.site_name" placeholder="请输入站点名称" />
          </el-form-item>
        </el-col>
        <el-col :span="24">
          <el-form-item label="站点logo" prop="site_logo">
            <sa-image-upload v-model="formData.site_logo" :limit="1" :multiple="false" />
          </el-form-item>
        </el-col>
        <el-col :span="24">
          <el-form-item label="站点描述" prop="site_desc">
            <el-input v-model="formData.site_desc" type="textarea" :rows="2" placeholder="请输入站点描述" />
          </el-form-item>
        </el-col>
        <el-col :span="24">
          <el-form-item label="版权信息" prop="site_copyright">
            <el-input v-model="formData.site_copyright" placeholder="请输入版权信息" />
          </el-form-item>
        </el-col>
        <el-col :span="24">
          <el-form-item label="备案号" prop="site_record_number">
            <el-input v-model="formData.site_record_number" placeholder="请输入备案号" />
          </el-form-item>
        </el-col>
        <el-col :span="24">
          <el-form-item label="站点配置" prop="site_config">
            <el-input v-model="formData.site_config" type="textarea" :rows="3" placeholder="请输入站点配置" />
          </el-form-item>
        </el-col>
        <el-col :span="24">
          <el-form-item label="联系电话" prop="contact">
            <el-input v-model="formData.contact" placeholder="请输入联系电话" />
          </el-form-item>
        </el-col>
        <el-col :span="24">
          <el-form-item label="备注" prop="remark">
            <el-input v-model="formData.remark" type="textarea" :rows="2" placeholder="请输入备注" />
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
  import api from '../../../api/setting/info'
  import { ElMessage } from 'element-plus'
  import type { FormInstance, FormRules } from 'element-plus'

  interface Props { modelValue: boolean; dialogType: string; data?: Record<string, any> }
  interface Emits { (e: 'update:modelValue', value: boolean): void; (e: 'success'): void }
  const props = withDefaults(defineProps<Props>(), { modelValue: false, dialogType: 'edit', data: undefined })
  const emit = defineEmits<Emits>()
  const formRef = ref<FormInstance>()
  const visible = computed({ get: () => props.modelValue, set: (value) => emit('update:modelValue', value) })
  const rules = reactive<FormRules>({ site_name: [{ required: true, message: '站点名称必需填写', trigger: 'blur' }] })
  const initialFormData = { site_name: '', site_logo: '', site_desc: '', site_copyright: '', site_record_number: '', site_config: '', contact: '', remark: '', id: null as number | null }
  const formData = reactive({ ...initialFormData })
  watch(() => props.modelValue, (newVal) => { if (newVal) initPage() })
  const initPage = async () => {
    Object.assign(formData, initialFormData)
    if (props.data) { await nextTick(); initForm() }
  }
  const initForm = () => {
    if (props.data) {
      for (const key in formData) {
        if (props.data[key] != null && props.data[key] != undefined) (formData as any)[key] = props.data[key]
      }
    }
  }
  const handleClose = () => { visible.value = false; formRef.value?.resetFields() }
  const handleSubmit = async () => {
    if (!formRef.value) return
    try {
      await formRef.value.validate()
      await api.update(formData)
      ElMessage.success('修改成功')
      emit('success'); handleClose()
    } catch (error) { console.log('表单验证失败:', error) }
  }
</script>
