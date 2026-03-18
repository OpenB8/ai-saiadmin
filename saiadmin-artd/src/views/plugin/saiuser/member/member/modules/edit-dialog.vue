<template>
  <el-dialog
    v-model="visible"
    :title="dialogType === 'add' ? '新增会员' : '编辑会员'"
    width="600px"
    align-center
    :close-on-click-modal="false"
    @close="handleClose"
  >
    <el-form ref="formRef" :model="formData" :rules="rules" label-width="100px">
      <el-row :gutter="20">
        <el-col :span="24">
          <el-form-item label="用户名" prop="username">
            <el-input v-model="formData.username" placeholder="请输入用户名" />
          </el-form-item>
        </el-col>
        <el-col :span="24" v-if="dialogType === 'add'">
          <el-form-item label="密码" prop="password">
            <el-input
              v-model="formData.password"
              type="password"
              placeholder="请输入密码"
              show-password
            />
          </el-form-item>
        </el-col>
        <el-col :span="24">
          <el-form-item label="用户昵称" prop="nickname">
            <el-input v-model="formData.nickname" placeholder="请输入用户昵称" />
          </el-form-item>
        </el-col>
        <el-col :span="24">
          <el-form-item label="头像" prop="avatar">
            <sa-image-picker v-model="formData.avatar" :limit="1" :multiple="false" />
          </el-form-item>
        </el-col>
        <el-col :span="24">
          <el-form-item label="邮箱" prop="email" help="创建账户必须填写，邮箱格式：123@qq.com">
            <el-input v-model="formData.email" placeholder="请输入邮箱" />
          </el-form-item>
        </el-col>
        <el-col :span="24">
          <el-form-item label="手机" prop="mobile">
            <el-input v-model="formData.mobile" placeholder="请输入手机" />
          </el-form-item>
        </el-col>
        <el-col :span="24">
          <el-form-item label="会员等级" prop="member_level_id">
            <el-select v-model="formData.member_level_id" placeholder="请选择会员等级" clearable>
              <el-option
                v-for="item in optionData.member_level_id"
                :key="item.id"
                :label="item.level_name"
                :value="item.id"
              />
            </el-select>
          </el-form-item>
        </el-col>
        <el-col :span="24">
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
  import api from '../../../api/member/member'
  import levelApi from '../../../api/setting/level'
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
    member_level_id: <any[]>[]
  })

  const visible = computed({
    get: () => props.modelValue,
    set: (value) => emit('update:modelValue', value)
  })

  const rules = reactive<FormRules>({
    username: [{ required: true, message: '用户名必需填写', trigger: 'blur' }],
    password: [{ required: true, message: '密码必需填写', trigger: 'blur' }],
    member_level_id: [{ required: true, message: '会员等级必需填写', trigger: 'blur' }],
    status: [{ required: true, message: '状态必需填写', trigger: 'blur' }]
  })

  const initialFormData = {
    username: '',
    password: '',
    nickname: '',
    avatar: '',
    email: '',
    mobile: '',
    member_level_id: 1 as number | null,
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
    const resp = await levelApi.list({ saiType: 'all' })
    optionData.member_level_id = resp.data || resp
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
