<template>
  <el-dialog
    v-model="visible"
    :title="dialogType === 'add' ? '新增文章' : '编辑文章'"
    width="960px"
    align-center
    :close-on-click-modal="false"
    @close="handleClose"
  >
    <el-form ref="formRef" :model="formData" :rules="rules" label-width="120px">
      <el-row :gutter="20">
        <el-col :span="24">
          <el-form-item label="文章分类" prop="category_id">
            <el-tree-select
              v-model="formData.category_id"
              :data="optionData.category_id"
              node-key="id"
              :props="{ label: 'category_name' }"
              placeholder="请选择文章分类"
              check-strictly
              clearable
            />
          </el-form-item>
        </el-col>
        <el-col :span="24">
          <el-form-item label="文章标题" prop="title">
            <el-input v-model="formData.title" placeholder="请输入文章标题" />
          </el-form-item>
        </el-col>
        <el-col :span="24">
          <el-form-item label="文章图片" prop="image">
            <sa-image-upload v-model="formData.image" :limit="1" />
          </el-form-item>
        </el-col>
        <el-col :span="24">
          <el-form-item label="文章作者" prop="author">
            <el-input v-model="formData.author" placeholder="请输入文章作者" />
          </el-form-item>
        </el-col>
        <el-col :span="24">
          <el-form-item label="文章简介" prop="describe">
            <el-input
              v-model="formData.describe"
              type="textarea"
              :rows="2"
              placeholder="请输入文章简介"
            />
          </el-form-item>
        </el-col>
        <el-col :span="24">
          <el-form-item label="文章内容" prop="content">
            <sa-editor v-model="formData.content" height="400px" />
          </el-form-item>
        </el-col>
        <el-col :span="12">
          <el-form-item label="浏览次数" prop="views">
            <el-input-number v-model="formData.views" placeholder="请输入浏览次数" />
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
        <el-col :span="12">
          <el-form-item label="是否热门" prop="is_hot">
            <sa-radio v-model="formData.is_hot" dict="yes_or_no" />
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
  import api from '../../../api/cms/article'
  import categoryApi from '../../../api/cms/category'
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
    category_id: <any[]>[]
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
    category_id: [{ required: true, message: '文章分类必需填写', trigger: 'blur' }],
    title: [{ required: true, message: '文章标题必需填写', trigger: 'blur' }],
    describe: [{ required: true, message: '文章简介必需填写', trigger: 'blur' }],
    content: [{ required: true, message: '文章内容必需填写', trigger: 'blur' }]
  })

  /**
   * 初始数据
   */
  const initialFormData = {
    category_id: null as number | null,
    title: '',
    image: '',
    author: '',
    describe: '',
    content: '',
    views: 0,
    sort: 100,
    status: 1,
    is_hot: 2,
    id: null as number | null
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
    const resp = await categoryApi.list({ saiType: 'all' })
    optionData.category_id = resp.data || resp
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
</script>
