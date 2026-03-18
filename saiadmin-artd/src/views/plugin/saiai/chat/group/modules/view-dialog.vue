<template>
  <el-drawer v-model="visible" size="70%" title="查看详情" :footer="false">
    <!-- 详情 start -->
    <div>
      <el-descriptions :column="1" label-width="100px" border>
        <el-descriptions-item label="用户">
          <div v-text="formData?.user.username"></div>
        </el-descriptions-item>
        <el-descriptions-item label="会话标题">
          <div v-text="formData?.title"></div>
        </el-descriptions-item>
        <el-descriptions-item label="修改时间">
          <div v-text="formData?.update_time"></div>
        </el-descriptions-item>
      </el-descriptions>
    </div>
    <!-- 详情 end -->
  </el-drawer>
</template>

<script setup lang="ts">
  import api from '../../../api/chat/group'

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

  /**
   * 弹窗显示状态双向绑定
   */
  const visible = computed({
    get: () => props.modelValue,
    set: (value) => emit('update:modelValue', value)
  })

  /**
   * 初始数据
   */
  const initialFormData = {
    id: null,
    user_id: null,
    title: '',
    update_time: '',
    user: {} as any,
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
    // 如果有数据，则填充数据
    if (props.data) {
      await nextTick()
      initForm()
    }
  }

  /**
   * 初始化表单数据
   */
  const initForm = async () => {
    if (props.data && props.data.id) {
      const data = await api.read(props.data.id)
      for (const key in formData) {
        if (data[key] != null && data[key] != undefined) {
          ;(formData as any)[key] = data[key]
        }
      }
    }
  }
</script>
