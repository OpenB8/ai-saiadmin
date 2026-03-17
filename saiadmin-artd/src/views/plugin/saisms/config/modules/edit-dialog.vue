<template>
  <el-dialog
    v-model="visible"
    :title="dialogType === 'add' ? '新增短信配置' : '编辑短信配置'"
    width="800px"
    align-center
    :close-on-click-modal="false"
    @close="handleClose"
  >
    <el-form ref="formRef" :model="formData" :rules="rules" label-width="100px">
      <el-form-item label="网关标识" prop="gateway">
        <el-input v-model="formData.gateway" placeholder="请输入网关标识" />
      </el-form-item>
      <el-form-item label="网关名称" prop="config_name">
        <el-input v-model="formData.config_name" placeholder="请输入网关名称" />
      </el-form-item>
      <el-form-item label="配置参数" prop="config_data">
        <el-row
          :gutter="10"
          class="mb-2 w-full"
          v-for="(item, index) in formData.config_data"
          :key="index"
        >
          <el-col :span="8">
            <el-input v-model="item.key" placeholder="请输入参数名"></el-input>
          </el-col>
          <el-col :span="12">
            <el-input v-model="item.value" placeholder="请输入参数值"></el-input>
          </el-col>
          <el-col :span="4">
            <el-button type="danger" @click="removeConfigData(index)">
              <template #icon>
                <ArtSvgIcon icon="ri:delete-bin-5-line" />
              </template>
            </el-button>
          </el-col>
        </el-row>
      </el-form-item>
      <el-form-item>
        <el-button type="primary" @click="addConfigData">
          <template #icon>
            <ArtSvgIcon icon="ri:add-fill" />
          </template>
          添加参数
        </el-button>
      </el-form-item>
      <el-form-item label="排序" prop="sort">
        <el-input-number v-model="formData.sort" :min="0" placeholder="请输入排序" />
      </el-form-item>
      <el-form-item label="状态" prop="status">
        <sa-radio v-model="formData.status" dict="data_status" />
      </el-form-item>
    </el-form>
    <template #footer>
      <el-button @click="handleClose">取消</el-button>
      <el-button type="primary" @click="handleSubmit">提交</el-button>
    </template>
  </el-dialog>
</template>

<script setup lang="ts">
  import api from '../../api/config'
  import { ElMessage } from 'element-plus'
  import type { FormInstance, FormRules } from 'element-plus'

  interface Props {
    modelValue: boolean
    dialogType: string
    data?: Record<string, any>
  }

  interface ConfigData {
    key: string
    value: string
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

  const initialFormData = {
    id: undefined as number | undefined,
    gateway: '',
    config_name: '',
    config_data: [] as ConfigData[],
    sort: 100,
    status: 1
  }
  const formData = reactive({ ...initialFormData })

  const rules = reactive<FormRules>({
    gateway: [{ required: true, message: '网关标识必填', trigger: 'blur' }],
    config_name: [{ required: true, message: '网关名称必填', trigger: 'blur' }]
  })

  /**
   * 添加配置参数
   */
  const addConfigData = () => {
    formData.config_data.push({ key: '', value: '' })
  }

  /**
   * 删除配置参数
   */
  const removeConfigData = (index: number) => {
    formData.config_data.splice(index, 1)
  }

  watch(
    () => props.modelValue,
    (newVal) => {
      if (newVal) initPage()
    }
  )

  /**
   * 初始化页面数据
   */
  const initPage = async () => {
    // 重置为初始值
    Object.assign(formData, initialFormData)
    formData.config_data = []

    if (props.data) {
      await nextTick()
      // 填充基础字段
      formData.id = props.data.id
      formData.gateway = props.data.gateway || ''
      formData.config_name = props.data.config_name || ''
      formData.sort = props.data.sort ?? 100
      formData.status = props.data.status ?? 1

      // 将 config 对象转换为 config_data 数组
      if (props.data.config && typeof props.data.config === 'object') {
        formData.config_data = Object.entries(props.data.config).map(([key, value]) => ({
          key,
          value: String(value)
        }))
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
      // 将 config_data 数组转换回 config 对象
      const config: Record<string, string> = {}
      formData.config_data.forEach((item) => {
        if (item.key) {
          config[item.key] = item.value
        }
      })

      const data = {
        id: formData.id,
        gateway: formData.gateway,
        config_name: formData.config_name,
        config,
        sort: formData.sort,
        status: formData.status
      }

      if (props.dialogType === 'add') {
        await api.save(data)
        ElMessage.success('添加成功')
      } else {
        await api.update(data)
        ElMessage.success('修改成功')
      }
      emit('success')
      handleClose()
    } catch (e) {
      console.error(e)
    }
  }
</script>
