<template>
  <el-drawer
    v-model="visible"
    title="聊天记录"
    size="70%"
    destroy-on-close
    :close-on-click-modal="false"
    @close="handleClose"
  >
    <div class="art-full-height flex flex-col bg-[var(--el-bg-color)]">
      <!-- 聊天内容区域 -->
      <div class="flex-1 py-6 px-6 overflow-y-auto [&::-webkit-scrollbar]:!w-1.5">
        <template v-if="tableData.length > 0">
          <div v-for="item in tableData" :key="item.id" class="mb-6 group relative">
            <div
              :class="[
                'flex gap-3 items-start w-full',
                item.role === 'user' ? 'flex-row-reverse' : 'flex-row'
              ]"
            >
              <!-- 头像 -->
              <ElAvatar
                :size="36"
                :src="item.role === 'user' ? item.user.avatar : aiAvatar"
                class="flex-shrink-0"
              />

              <!-- 消息体 -->
              <div
                class="flex flex-col max-w-[75%]"
                :class="item.role === 'user' ? 'items-end' : 'items-start'"
              >
                <!-- 顶部信息 -->
                <div
                  class="flex gap-2 mb-1.5 text-xs"
                  :class="item.role === 'user' ? 'flex-row-reverse' : 'flex-row'"
                >
                  <span class="font-medium">{{
                    item.role === 'user' ? item.user.username : 'AI 助手'
                  }}</span>
                  <span class="text-[var(--el-text-color-secondary)]">{{ item.create_time }}</span>

                  <!-- 单条删除按钮 -->
                  <ElLink
                    type="danger"
                    class="opacity-0 group-hover:opacity-100 transition-opacity ml-2"
                    :underline="false"
                    @click="handleDeleteOne(item)"
                  >
                    删除
                  </ElLink>
                </div>

                <!-- 内容 -->
                <div
                  v-if="item.role === 'user'"
                  class="py-3 px-4 text-sm leading-relaxed rounded-xl whitespace-pre-wrap bg-primary text-white rounded-tr-sm"
                >
                  {{ item.content }}
                </div>
                <!-- AI 消息 (Markdown 渲染) -->
                <div
                  v-else
                  class="py-3 px-4 text-sm leading-relaxed rounded-xl rounded-tl-sm bg-[var(--el-fill-color-light)] markdown-body min-w-[60px]"
                  v-html="renderMarkdown(item.content)"
                ></div>

                <!-- Token 信息 -->
                <div v-if="item.tokens" class="mt-1 text-xs text-[var(--el-text-color-secondary)]">
                  消耗 tokens: {{ item.tokens }}
                </div>
              </div>
            </div>
          </div>
        </template>
        <ElEmpty v-else description="暂无聊天记录" />
      </div>
    </div>
  </el-drawer>
</template>

<script setup lang="ts">
  import { ElMessage, ElMessageBox } from 'element-plus'
  import api from '../../../api/chat/group'
  import { marked } from 'marked'
  import hljs from 'highlight.js'
  import 'highlight.js/styles/github-dark.css'

  // 导入头像资源 (假设可以直接使用 store 或者 assets，这里先用 computed 或者 hardcode)
  import aiAvatarImg from '../../../assets/logo.png'

  const aiAvatar = aiAvatarImg

  // 配置 marked
  marked.setOptions({
    breaks: true,
    gfm: true
  })

  const renderer = new marked.Renderer()
  renderer.code = ({ text, lang }: { text: string; lang?: string }) => {
    const language = lang && hljs.getLanguage(lang) ? lang : 'plaintext'
    const highlighted = hljs.highlight(text, { language }).value
    return `<pre class="hljs"><code class="language-${language}">${highlighted}</code></pre>`
  }
  marked.use({ renderer })

  const renderMarkdown = (content: string) => {
    if (!content) return ''
    try {
      return marked.parse(content)
    } catch {
      return content
    }
  }

  interface Props {
    modelValue: boolean
    data?: Record<string, any>
  }

  interface Emits {
    (e: 'update:modelValue', value: boolean): void
    (e: 'success'): void
  }

  const props = withDefaults(defineProps<Props>(), {
    modelValue: false,
    data: undefined
  })

  const emit = defineEmits<Emits>()
  const tableData = ref<any[]>([])

  const searchForm = ref({
    group_id: '',
    title: '',
    create_time: []
  })

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
    if (!props.data?.id) {
      ElMessage.error('请先选择一个分组')
      return
    }
    searchForm.value.group_id = props.data.id
    refreshData()
  }

  const refreshData = async () => {
    const data = await api.chatList({
      ...searchForm.value
    })
    tableData.value = data
  }

  // 单个删除
  const handleDeleteOne = (row: any) => {
    ElMessageBox.confirm('确定要删除这条消息吗？', '提示', {
      confirmButtonText: '确定',
      cancelButtonText: '取消',
      type: 'warning'
    }).then(() => {
      api.deleteChat({ ids: [row.id] }).then(() => {
        ElMessage.success('删除成功')
        refreshData()
      })
    })
  }

  const handleClose = () => {
    visible.value = false
  }
</script>

<style scoped>
  /* 样式复用 index.vue */
  /* Markdown 内容样式 */
  .markdown-body :deep(p) {
    margin: 0 0 0.75em;
    line-height: 1.6;
  }

  .markdown-body :deep(p:last-child) {
    margin-bottom: 0;
  }

  .markdown-body :deep(pre) {
    margin: 0.75em 0;
    padding: 1em;
    border-radius: 8px;
    overflow-x: auto;
    font-size: 0.875em;
  }

  .markdown-body :deep(code) {
    font-family: 'Fira Code', 'Monaco', 'Consolas', monospace;
    font-size: 0.9em;
  }

  .markdown-body :deep(:not(pre) > code) {
    background: var(--el-fill-color);
    padding: 0.2em 0.4em;
    border-radius: 4px;
    color: var(--el-color-danger);
  }

  .markdown-body :deep(ul),
  .markdown-body :deep(ol) {
    margin: 0.5em 0;
    padding-left: 1.5em;
  }

  .markdown-body :deep(li) {
    margin: 0.25em 0;
  }

  .markdown-body :deep(h1),
  .markdown-body :deep(h2),
  .markdown-body :deep(h3),
  .markdown-body :deep(h4) {
    margin: 0.75em 0 0.5em;
    font-weight: 600;
  }

  .markdown-body :deep(h1) {
    font-size: 1.5em;
  }
  .markdown-body :deep(h2) {
    font-size: 1.3em;
  }
  .markdown-body :deep(h3) {
    font-size: 1.15em;
  }
  .markdown-body :deep(h4) {
    font-size: 1em;
  }

  .markdown-body :deep(blockquote) {
    margin: 0.75em 0;
    padding: 0.5em 1em;
    border-left: 4px solid var(--el-color-primary);
    background: var(--el-fill-color-light);
    border-radius: 0 4px 4px 0;
  }

  .markdown-body :deep(table) {
    border-collapse: collapse;
    margin: 0.75em 0;
    width: 100%;
  }

  .markdown-body :deep(th),
  .markdown-body :deep(td) {
    border: 1px solid var(--el-border-color);
    padding: 0.5em 0.75em;
    text-align: left;
  }

  .markdown-body :deep(th) {
    background: var(--el-fill-color-light);
    font-weight: 600;
  }

  .markdown-body :deep(a) {
    color: var(--el-color-primary);
    text-decoration: none;
  }

  .markdown-body :deep(a:hover) {
    text-decoration: underline;
  }

  .markdown-body :deep(hr) {
    border: none;
    border-top: 1px solid var(--el-border-color);
    margin: 1em 0;
  }
</style>
