<template>
  <el-dialog
    v-model="visible"
    :title="dialogType === 'add' ? '新增会员等级' : '编辑会员等级'"
    width="600px"
    align-center
    :close-on-click-modal="false"
    @close="handleClose"
  >
    <el-form ref="formRef" :model="formData" :rules="rules" label-width="100px">
      <el-row :gutter="20">
        <el-col :span="24">
          <el-form-item label="等级名称" prop="level_name">
            <el-input v-model="formData.level_name" placeholder="请输入等级名称" />
          </el-form-item>
        </el-col>
        <el-col :span="24">
          <el-form-item label="等级标识" prop="level_code">
            <el-input v-model="formData.level_code" placeholder="请输入等级标识" />
          </el-form-item>
        </el-col>
        <el-col :span="12">
          <el-form-item label="升级积分" prop="min_points">
            <el-input-number v-model="formData.min_points" :min="0" placeholder="请输入升级积分" />
          </el-form-item>
        </el-col>
        <el-col :span="12">
          <el-form-item label="上限积分" prop="max_points">
            <el-input-number v-model="formData.max_points" :min="0" placeholder="请输入上限积分" />
          </el-form-item>
        </el-col>
        <el-col :span="24">
          <el-form-item label="等级图标" prop="level_icon">
            <sa-image-upload v-model="formData.level_icon" :limit="1" :multiple="false" />
          </el-form-item>
        </el-col>
        <el-col :span="24">
          <el-form-item label="等级权益" prop="privileges">
            <el-input
              v-model="formData.privileges"
              type="textarea"
              :rows="3"
              placeholder="请输入等级权益"
            />
          </el-form-item>
        </el-col>
        <el-col :span="12">
          <el-form-item label="排序" prop="sort">
            <el-input-number v-model="formData.sort" placeholder="请输入排序" />
          </el-form-item>
        </el-col>
        <el-col :span="12">
          <el-form-item label="状态" prop="status">
            <sa-radio v-model="formData.status" dict="data_status" />
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
  import api from '../../../api/setting/level'
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
    level_name: [{ required: true, message: '等级名称必需填写', trigger: 'blur' }],
    level_code: [{ required: true, message: '等级标识必需填写', trigger: 'blur' }],
    min_points: [{ required: true, message: '升级积分必需填写', trigger: 'blur' }],
    max_points: [{ required: true, message: '上限积分必需填写', trigger: 'blur' }]
  })

  const initialFormData = {
    level_name: '',
    level_code: '',
    min_points: null as number | null,
    max_points: null as number | null,
    level_icon: '',
    privileges: '',
    sort: 100,
    status: 1,
    id: null as number | null
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
    if (props.data) {
      await nextTick()
      initForm()
    }
  }

  const initForm = () => {
    if (props.data) {
      for (const key in formData) {
        if (props.data[key] != null && props.data[key] != undefined) {
          ;(formData as any)[key] = props.data[key]
        }
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
