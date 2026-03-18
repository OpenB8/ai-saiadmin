<template>
  <el-drawer v-model="visible" size="40%" title="查看详情" :footer="false">
    <!-- 详情 start -->
    <div>
      <el-descriptions :column="1" label-width="100px" border>
        <el-descriptions-item label="订单号">
          <div v-text="formData?.order_no"></div>
        </el-descriptions-item>
        <el-descriptions-item label="订单名称">
          <div v-text="formData?.order_name"></div>
        </el-descriptions-item>
        <el-descriptions-item label="订单备注">
          <div v-text="formData?.remark"></div>
        </el-descriptions-item>
        <el-descriptions-item label="下单时间">
          <div v-text="formData?.create_time"></div>
        </el-descriptions-item>
        <el-descriptions-item label="订单金额">
          <div v-text="formData?.order_price"></div>
        </el-descriptions-item>
        <el-descriptions-item label="支付方式">
          <sa-dict :value="formData?.pay_method" dict="saipay_method" />
        </el-descriptions-item>
        <el-descriptions-item label="支付类型">
          <div v-text="formData?.pay_type"></div>
        </el-descriptions-item>
        <el-descriptions-item label="支付状态">
          <ElTag v-if="formData?.pay_status === 1" type="success">已支付</ElTag>
          <ElTag v-else type="danger">未支付</ElTag>
        </el-descriptions-item>
        <el-descriptions-item v-if="formData?.pay_status === 1" label="支付金额">
          <div v-text="formData?.pay_price"></div>
        </el-descriptions-item>
        <el-descriptions-item v-if="formData?.pay_status === 1" label="支付时间">
          <div v-text="formData?.pay_time"></div>
        </el-descriptions-item>
      </el-descriptions>
    </div>
    <!-- 详情 end -->
  </el-drawer>
</template>

<script setup lang="ts">
  import api from '../../api/order'

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
    order_no: '',
    order_name: '',
    order_price: '',
    pay_method: '',
    pay_status: 2,
    pay_time: '',
    pay_type: '',
    pay_price: '',
    create_time: '',
    remark: ''
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
