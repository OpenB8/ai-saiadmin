<template>
  <el-drawer v-model="visible" size="70%" title="查看详情" :footer="false">
    <!-- 详情 start -->
    <el-divider content-position="left">基本信息</el-divider>
    <el-descriptions :column="2" border>
      <el-descriptions-item label="用户名">
        <div v-text="formData?.username"></div>
      </el-descriptions-item>
      <el-descriptions-item label="用户昵称">
        <div v-text="formData?.nickname"></div>
      </el-descriptions-item>
      <el-descriptions-item :span="2" label="头像">
        <img :src="formData?.avatar" style="width: 80px" />
      </el-descriptions-item>
      <el-descriptions-item label="邮箱">
        <div v-text="formData?.email"></div>
      </el-descriptions-item>
      <el-descriptions-item label="手机">
        <div v-text="formData?.mobile"></div>
      </el-descriptions-item>
      <el-descriptions-item label="会员等级">
        <div v-text="formData?.level_name"></div>
      </el-descriptions-item>
      <el-descriptions-item label="注册平台">
        <div v-text="formData?.platform_name"></div>
      </el-descriptions-item>
      <el-descriptions-item label="积分余额">
        <div v-text="formData?.points_balance"></div>
      </el-descriptions-item>
      <el-descriptions-item label="最后登录IP">
        <div v-text="formData?.last_login_ip"></div>
      </el-descriptions-item>
      <el-descriptions-item label="最后登录时间">
        <div v-text="formData?.last_login_time"></div>
      </el-descriptions-item>
      <el-descriptions-item label="状态">
        <sa-dict :value="formData?.status" dict="data_status" render="span" />
      </el-descriptions-item>
    </el-descriptions>

    <el-tabs v-model="activeTab" class="mt-4">
      <el-tab-pane label="登录日志" name="log">
        <el-table :data="logData" border>
          <el-table-column prop="create_time" label="登录时间" width="180" />
          <el-table-column prop="platform_name" label="登录平台" width="120" />
          <el-table-column prop="login_ip" label="登录IP" width="150" />
          <el-table-column prop="login_location" label="登录地点" width="150" />
          <el-table-column prop="user_agent" label="用户代理" show-overflow-tooltip />
          <el-table-column label="登录结果" width="100">
            <template #default="{ row }">
              <ElTag :type="row.login_result === 1 ? 'success' : 'danger'">
                {{ row.login_result === 1 ? '成功' : '失败' }}
              </ElTag>
            </template>
          </el-table-column>
          <el-table-column prop="fail_reason" label="失败原因" />
        </el-table>
      </el-tab-pane>
      <el-tab-pane label="积分日志" name="points">
        <el-table :data="pointsData" border>
          <el-table-column prop="create_time" label="发生时间" width="180" />
          <el-table-column label="积分类型" width="120">
            <template #default="{ row }">
              <sa-dict :value="row.operate_type" dict="saiuser_operate_type" render="span" />
            </template>
          </el-table-column>
          <el-table-column prop="operate_desc" label="积分说明" show-overflow-tooltip />
          <el-table-column prop="points_before" label="变动前积分" width="120" />
          <el-table-column prop="points_change" label="积分变动" width="120" />
          <el-table-column prop="points_after" label="变动后积分" width="120" />
        </el-table>
      </el-tab-pane>
    </el-tabs>
    <!-- 详情 end -->
  </el-drawer>
</template>

<script setup lang="ts">
  import api from '../../../api/member/member'

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

  const visible = computed({
    get: () => props.modelValue,
    set: (value) => emit('update:modelValue', value)
  })

  const activeTab = ref('log')
  const logData = ref<any[]>([])
  const pointsData = ref<any[]>([])

  const initialFormData = {
    username: '',
    nickname: '',
    avatar: '',
    email: '',
    mobile: '',
    level_name: '',
    platform_name: '',
    points_balance: 0,
    last_login_ip: '',
    last_login_time: '',
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
    logData.value = []
    pointsData.value = []
    if (props.data && props.data.id) {
      const resp = await api.read(props.data.id)
      const data = resp.data || resp
      for (const key in formData) {
        if (data[key] != null && data[key] != undefined) {
          ;(formData as any)[key] = data[key]
        }
      }
      logData.value = data.login_log || []
      pointsData.value = data.points_log || []
    }
  }
</script>

<style lang="scss" scoped>
  :deep(.el-descriptions__label) {
    width: 150px;
  }
</style>
