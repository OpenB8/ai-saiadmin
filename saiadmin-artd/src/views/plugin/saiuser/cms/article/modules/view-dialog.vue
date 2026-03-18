<template>
  <el-drawer v-model="visible" size="70%" title="查看详情" :footer="false">
    <!-- 详情 start -->
    <div>
      <el-descriptions :column="1" label-width="100px" border>
        <el-descriptions-item label="文章分类">
          <div v-text="formData?.category?.category_name"></div>
        </el-descriptions-item>
        <el-descriptions-item label="文章标题">
          <div v-text="formData?.title"></div>
        </el-descriptions-item>
        <el-descriptions-item label="文章图片">
          <img :src="formData?.image" style="width: 200px" />
        </el-descriptions-item>
        <el-descriptions-item label="文章作者">
          <div v-text="formData?.author"></div>
        </el-descriptions-item>
        <el-descriptions-item label="文章简介">
          <div v-text="formData?.describe"></div>
        </el-descriptions-item>
        <el-descriptions-item label="文章内容">
          <div v-html="formData?.content"></div>
        </el-descriptions-item>
        <el-descriptions-item label="浏览次数">
          <div v-text="formData?.views"></div>
        </el-descriptions-item>
        <el-descriptions-item label="排序">
          <div v-text="formData?.sort"></div>
        </el-descriptions-item>
        <el-descriptions-item label="状态">
          <sa-dict :value="formData?.status" dict="data_status" render="span" />
        </el-descriptions-item>
        <el-descriptions-item label="是否热门">
          <sa-dict :value="formData?.is_hot" dict="yes_or_no" render="span" />
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
  import api from '../../../api/cms/article'

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
    category_id: null,
    title: '',
    image: '',
    author: '',
    describe: '',
    content: '',
    views: null,
    sort: 100,
    status: 1,
    is_hot: 2,
    id: null,
    update_time: '',
    category: {} as any,
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
      const resp = await api.read(props.data.id)
      const data = resp.data || resp
      for (const key in formData) {
        if (data[key] != null && data[key] != undefined) {
          ;(formData as any)[key] = data[key]
        }
      }
    }
  }
</script>
