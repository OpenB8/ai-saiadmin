<template>
  <el-dialog
    v-model="visible"
    title="数据权限"
    width="600px"
    align-center
    class="el-dialog-border"
    @close="handleClose"
  >
    <el-form :model="form" label-width="100px" class="mt-4">
      <el-form-item label="角色名称">
        <el-input v-model="form.name" disabled />
      </el-form-item>
      <el-form-item label="角色标识">
        <el-input v-model="form.code" disabled />
      </el-form-item>
      <el-form-item label="数据边界">
        <el-select v-model="form.data_scope" class="w-full">
          <el-option
            v-for="item in scopeList"
            :key="item.value"
            :label="item.label"
            :value="item.value"
          />
        </el-select>
      </el-form-item>
      <el-form-item v-show="form.data_scope === 2" label="部门列表">
        <div class="w-full">
          <div class="mb-2 flex gap-4">
            <el-checkbox v-model="isExpandAll" @change="toggleExpandAll">展开/折叠</el-checkbox>
            <el-checkbox v-model="isSelectAll" @change="toggleSelectAll">全选/全不选</el-checkbox>
            <el-checkbox v-model="checkStrictly">父子联动</el-checkbox>
          </div>
          <div class="rounded border border-gray-200 p-2" style="height: 300px; overflow: auto">
            <el-tree
              ref="treeRef"
              :data="deptList"
              show-checkbox
              node-key="id"
              :default-expand-all="isExpandAll"
              :check-strictly="!checkStrictly"
              :props="{ label: 'label' }"
            />
          </div>
        </div>
      </el-form-item>
    </el-form>
    <template #footer>
      <el-button @click="handleClose">取消</el-button>
      <el-button type="primary" @click="savePermission">保存</el-button>
    </template>
  </el-dialog>
</template>

<script setup lang="ts">
  import { ElMessage } from 'element-plus'
  import roleApi from '@/api/system/role'
  import deptApi from '@/api/system/dept'

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
    dialogType: 'edit',
    data: undefined
  })

  const emit = defineEmits<Emits>()

  const deptList = ref<Api.Common.ApiData[]>([])
  const treeRef = ref()
  const form = ref({
    id: 0,
    name: '',
    code: '',
    data_scope: 1
  })

  const isExpandAll = ref(false)
  const isSelectAll = ref(false)
  const checkStrictly = ref(true)

  const scopeList = [
    { value: 1, label: '全部数据权限' },
    { value: 2, label: '自定义数据权限' },
    { value: 3, label: '本部门数据权限' },
    { value: 4, label: '本部门及以下数据权限' },
    { value: 5, label: '本人数据权限' }
  ]

  const visible = computed({
    get: () => props.modelValue,
    set: (value) => emit('update:modelValue', value)
  })

  watch(
    () => props.modelValue,
    (newVal) => {
      if (newVal) {
        initPage()
      }
    }
  )

  const initPage = async () => {
    form.value = {
      id: props.data?.id ?? 0,
      name: props.data?.name ?? '',
      code: props.data?.code ?? '',
      data_scope: props.data?.data_scope ?? 1
    }

    deptList.value = await deptApi.list({ tree: true })
    await nextTick()

    const data = await roleApi.deptByRole({ id: props.data?.id })
    treeRef.value?.setCheckedKeys(data.depts?.map((item: any) => item.id) || [])
  }

  const handleClose = () => {
    visible.value = false
    isExpandAll.value = false
    isSelectAll.value = false
    checkStrictly.value = true
    treeRef.value?.setCheckedKeys([])
  }

  const savePermission = async () => {
    const deptIds = form.value.data_scope === 2 ? treeRef.value?.getCheckedKeys() || [] : []

    try {
      await roleApi.dataPermission({
        id: form.value.id,
        data_scope: form.value.data_scope,
        dept_ids: deptIds
      })
      ElMessage.success('保存成功')
      emit('success')
      handleClose()
    } catch (error) {
      console.error(error)
    }
  }

  const toggleExpandAll = () => {
    const nodes = treeRef.value?.store.nodesMap
    if (!nodes) return

    Object.values(nodes).forEach((node: any) => {
      node.expanded = isExpandAll.value
    })
  }

  const toggleSelectAll = () => {
    if (isSelectAll.value) {
      treeRef.value?.setCheckedNodes(deptList.value)
      return
    }

    treeRef.value?.setCheckedKeys([])
  }
</script>
