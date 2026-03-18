<!-- AI 对话页面 -->
<template>
  <div class="page-content flex !p-0" :style="{ height: containerMinHeight }">
    <!-- 左侧会话列表 -->
    <div
      class="w-64 border-r border-[var(--el-border-color-light)] flex flex-col bg-[var(--el-fill-color-lighter)]"
    >
      <div class="p-3 border-b border-[var(--el-border-color-light)]">
        <ElButton class="w-full !rounded-xl" @click="handleCreateSession">
          <el-icon class="mr-1"><Plus /></el-icon>
          新建对话
        </ElButton>
      </div>
      <ElScrollbar class="flex-1">
        <div class="p-2 space-y-1">
          <div
            v-for="item in sessionList"
            :key="item.id"
            class="group relative flex items-center p-3 rounded-xl cursor-pointer transition-colors"
            :class="
              currentGroupId === item.id
                ? 'bg-[var(--el-bg-color)] shadow-sm'
                : 'hover:bg-[var(--el-fill-color-light)]'
            "
            @click="handleSwitchSession(item)"
          >
            <div class="flex-1 min-w-0 mr-2">
              <div
                class="truncate text-sm font-medium"
                :class="
                  currentGroupId === item.id
                    ? 'text-primary'
                    : 'text-[var(--el-text-color-primary)]'
                "
              >
                {{ item.title || '新对话' }}
              </div>
              <div class="text-xs text-[var(--el-text-color-secondary)] mt-0.5">
                {{ item.create_time }}
              </div>
            </div>

            <!-- 删除按钮 (hover显示) -->
            <div
              class="opacity-0 group-hover:opacity-100 transition-opacity"
              @click.stop="handleDeleteSession(item)"
            >
              <el-icon class="text-[var(--el-text-color-secondary)] hover:text-danger"
                ><Delete
              /></el-icon>
            </div>
          </div>
        </div>
      </ElScrollbar>
    </div>

    <!-- 右侧聊天区域 -->
    <div class="box-border flex-1 h-full flex flex-col bg-[var(--el-bg-color)]">
      <!-- 顶部标题栏 -->
      <div class="flex-cb pt-4 px-6 pb-4 border-b border-[var(--el-border-color-light)]">
        <div class="flex-c gap-3">
          <ElAvatar :size="45" :src="aiAvatar" />
          <div>
            <span class="text-lg font-semibold">SaiAI 助手</span>
            <div class="flex-c gap-1.5 mt-1">
              <div
                class="w-2 h-2 rounded-full"
                :class="isConnected ? 'bg-success' : 'bg-danger'"
              ></div>
              <span class="text-xs text-[var(--el-text-color-secondary)]">在线</span>
            </div>
          </div>
        </div>
        <div class="flex-c gap-2">
          <ElButton
            :icon="Setting"
            circle
            plain
            @click="modelDialogVisible = true"
            title="切换模型"
          />
          <ElButton :icon="Delete" circle plain @click="handleClearMessages" title="清空对话" />
        </div>
      </div>
      <!-- 聊天消息区域 -->
      <div
        class="flex-1 py-6 px-6 overflow-y-auto [&::-webkit-scrollbar]:!w-1.5"
        ref="messageContainer"
        v-loading="isLoadingDetail"
      >
        <!-- 欢迎消息 -->
        <div
          v-if="messages.length === 0"
          class="flex flex-col items-center justify-center h-full text-center"
        >
          <ElAvatar :size="80" :src="aiAvatar" class="mb-4" />
          <h2 class="text-xl font-semibold mb-2">欢迎使用 SaiAI 助手</h2>
          <p class="text-[var(--el-text-color-secondary)] max-w-md"
            >我是基于 AI
            的智能助手，可以帮助您回答问题、编写代码、分析数据等。请在下方输入您的问题开始对话。</p
          >
        </div>

        <!-- 消息列表 -->
        <template v-for="message in messages" :key="message.id">
          <div
            :class="[
              'flex gap-3 items-start w-full mb-6',
              message.isMe ? 'flex-row-reverse' : 'flex-row'
            ]"
          >
            <ElAvatar :size="36" :src="message.avatar" class="flex-shrink-0" />
            <div
              class="flex flex-col max-w-[75%]"
              :class="message.isMe ? 'items-end' : 'items-start'"
            >
              <div
                class="flex gap-2 mb-1.5 text-xs"
                :class="message.isMe ? 'flex-row-reverse' : 'flex-row'"
              >
                <span class="font-medium">{{ message.sender }}</span>
                <span class="text-[var(--el-text-color-secondary)]">{{ message.time }}</span>
              </div>
              <!-- 用户消息 -->
              <div
                v-if="message.isMe"
                class="py-3 px-4 text-sm leading-relaxed rounded-xl whitespace-pre-wrap bg-primary text-white rounded-tr-sm"
              >
                {{ message.content }}
              </div>
              <!-- AI 消息 (Markdown 渲染) -->
              <div
                v-else
                class="py-3 px-4 text-sm leading-relaxed rounded-xl rounded-tl-sm bg-[var(--el-fill-color-light)] markdown-body min-w-[60px]"
              >
                <!-- 加载中效果 -->
                <div v-if="message.isStreaming && !message.content" class="flex items-center gap-1">
                  <span class="text-[var(--el-text-color-secondary)]">思考中</span>
                  <span class="flex gap-1">
                    <span
                      class="w-1.5 h-1.5 bg-[var(--el-text-color-secondary)] rounded-full animate-bounce"
                      style="animation-delay: 0ms"
                    ></span>
                    <span
                      class="w-1.5 h-1.5 bg-[var(--el-text-color-secondary)] rounded-full animate-bounce"
                      style="animation-delay: 150ms"
                    ></span>
                    <span
                      class="w-1.5 h-1.5 bg-[var(--el-text-color-secondary)] rounded-full animate-bounce"
                      style="animation-delay: 300ms"
                    ></span>
                  </span>
                </div>
                <!-- 实际内容 -->
                <div v-else v-html="renderMarkdown(message.content)"></div>
              </div>
              <span
                v-if="message.isStreaming && message.content"
                class="inline-block w-2 h-4 mt-1 bg-[var(--el-text-color-secondary)] rounded-sm animate-pulse"
              ></span>
            </div>
          </div>
        </template>
      </div>

      <!-- 输入区域 -->
      <div class="p-4 border-t border-[var(--el-border-color-light)]">
        <div class="flex gap-3 items-end">
          <ElInput
            v-model="messageText"
            type="textarea"
            :rows="2"
            :autosize="{ minRows: 2, maxRows: 5 }"
            placeholder="输入您的问题，按 Enter 发送..."
            resize="none"
            :disabled="isLoading"
            @keydown.enter.exact.prevent="handleSendMessage"
            @keydown.enter.shift.exact="() => {}"
            class="flex-1"
          />
          <ElButton
            type="primary"
            :icon="isLoading ? Loading : Promotion"
            :loading="isLoading"
            :disabled="!messageText.trim() || isLoading"
            @click="handleSendMessage"
            class="h-10 px-5"
          >
            {{ isLoading ? '生成中' : '发送' }}
          </ElButton>
        </div>
        <div class="flex-c justify-between mt-2 text-xs text-[var(--el-text-color-secondary)]">
          <span>按 Shift + Enter 换行</span>
          <span>由 {{ currentModel.model || 'SaiAi' }} 提供支持</span>
        </div>
      </div>
    </div>
    <ElDialog
      v-model="modelDialogVisible"
      title="切换模型"
      width="400px"
      align-center
      append-to-body
    >
      <div class="flex flex-col gap-3">
        <div
          v-for="item in modelList"
          :key="item.id"
          class="group relative flex items-center justify-between p-4 rounded-xl border-2 cursor-pointer transition-all duration-200"
          :class="[
            currentModel.id === item.id
              ? 'border-primary bg-[var(--el-color-primary-light-9)] text-primary'
              : 'border-transparent bg-[var(--el-fill-color-lighter)] hover:bg-[var(--el-fill-color-light)] hover:scale-[1.02]'
          ]"
          @click="selectModel(item)"
        >
          <div class="flex items-center gap-3">
            <div
              class="flex items-center justify-center w-10 h-10 rounded-lg text-lg font-bold transition-colors"
              :class="
                currentModel.id === item.id
                  ? 'bg-primary text-white'
                  : 'bg-[var(--el-bg-color)] text-[var(--el-text-color-secondary)] shadow-sm'
              "
            >
              {{ item.name.charAt(0).toUpperCase() }}
            </div>
            <div class="flex flex-col">
              <span class="font-bold text-sm">{{ item.name }}</span>
              <span class="text-xs opacity-70">{{ item.model }}</span>
            </div>
          </div>

          <div
            v-if="currentModel.id === item.id"
            class="flex items-center justify-center w-6 h-6 rounded-full bg-primary text-white"
          >
            <el-icon><Check /></el-icon>
          </div>
        </div>
      </div>
    </ElDialog>
  </div>
</template>

<script setup lang="ts">
  import { Delete, Promotion, Loading, Setting, Check, Plus } from '@element-plus/icons-vue'
  import { ElMessage, ElMessageBox } from 'element-plus'
  import { marked } from 'marked'
  import hljs from 'highlight.js'
  import 'highlight.js/styles/github-dark.css'

  import { useAutoLayoutHeight } from '@/hooks/core/useLayoutHeight'
  import { useChatStore } from './store/chat'
  import api from './api/index'

  // 配置 marked
  marked.setOptions({
    breaks: true, // 支持换行
    gfm: true // 支持 GitHub Flavored Markdown
  })

  // 配置代码高亮
  const renderer = new marked.Renderer()
  renderer.code = ({ text, lang }: { text: string; lang?: string }) => {
    const language = lang && hljs.getLanguage(lang) ? lang : 'plaintext'
    const highlighted = hljs.highlight(text, { language }).value
    return `<pre class="hljs"><code class="language-${language}">${highlighted}</code></pre>`
  }
  marked.use({ renderer })

  defineOptions({ name: 'SaiAI' })

  const { containerMinHeight } = useAutoLayoutHeight()

  // 使用 store
  const chatStore = useChatStore()
  const { messages, isLoading, isConnected, currentModel, currentGroupId } = storeToRefs(chatStore)
  const {
    sendMessage: storeSendMessage,
    clearMessages: storeClearMessages,
    aiAvatar,
    saveModel
  } = chatStore

  const modelList = ref<any[]>([])
  const sessionList = ref<any[]>([])
  const modelDialogVisible = ref(false)
  const isLoadingDetail = ref(false)

  // 获取模型列表并初始化当前模型
  const getModelList = async () => {
    const data = await api.modelList({})
    modelList.value = data

    // 如果 store 中没有有效的模型（首次加载或清除后），则加载默认模型
    if (!currentModel.value.id) {
      try {
        const defaultModelData = await api.defaultModel()
        if (defaultModelData && defaultModelData.id) {
          currentModel.value = defaultModelData
          saveModel(defaultModelData)
        } else if (data && data.length > 0) {
          // 如果没有默认模型，使用列表中的第一个
          currentModel.value = data[0]
          saveModel(data[0])
        }
      } catch (e) {
        console.error('Failed to load default model:', e)
        // 回退到列表第一个模型
        if (data && data.length > 0) {
          currentModel.value = data[0]
          saveModel(data[0])
        }
      }
    }
  }

  // 获取会话列表
  const getSessionList = async () => {
    const data = await api.getSessionList()
    sessionList.value = data
  }

  // 创建新会话
  const handleCreateSession = async () => {
    storeClearMessages()
    currentGroupId.value = null
    // 重新获取列表确保最新状态
    await getSessionList()
  }

  // 切换会话
  const handleSwitchSession = async (item: any) => {
    if (currentGroupId.value === item.id) return

    currentGroupId.value = item.id
    isLoadingDetail.value = true
    try {
      const res = await api.getSessionDetail(item.id)
      // 加载历史消息
      storeClearMessages()
      // 这里需要手动将历史记录转换并填充到 store.messages
      // 注意：这里假设 store 允许直接修改或者提供了相应的方法，
      // 如果 store 没有 setMessages，我们需要扩展 store
      if (res.chats && res.chats.length > 0) {
        const historyMessages = res.chats.map((chat: any) => ({
          id: chat.id,
          sender: chat.role === 'user' ? '我' : currentModel.value.name || 'SaiAI',
          content: chat.content,
          time: chat.create_time,
          isMe: chat.role === 'user',
          avatar: chat.role === 'user' ? chat.user.avatar : aiAvatar, // 用户头像需要获取，这里简化
          isStreaming: false
        }))
        // 这是一个 hack，最好在 store 中增加 setMessages 方法
        // 暂时直接利用 storeToRefs 出来的 messages 响应式对象
        messages.value.push(...historyMessages)
      }
    } finally {
      isLoadingDetail.value = false
      scrollToBottom()
    }
  }

  // 删除会话
  const handleDeleteSession = async (item: any) => {
    try {
      await ElMessageBox.confirm('确定要删除该会话吗？', '提示', {
        type: 'warning'
      })
      await api.deleteSession(item.id)
      if (currentGroupId.value === item.id) {
        handleCreateSession()
      }
      await getSessionList()
      ElMessage.success('删除成功')
    } catch {
      // 用户取消或失败
    }
  }

  // 监听 currentGroupId 变化，如果为空（新建会话且发送了第一条消息后）会自动获取到 session_id，此时应该刷新列表
  watch(currentGroupId, (newVal) => {
    if (newVal) {
      getSessionList()
    }
  })

  // 选择模型并持久化保存
  const selectModel = (value: any) => {
    currentModel.value = value
    saveModel(value) // 保存到 localStorage
    modelDialogVisible.value = false
    ElMessage.success('已切换模型')
  }

  onMounted(() => {
    getModelList()
    getSessionList()
    scrollToBottom()
  })

  const messageText = ref('')
  const messageContainer = ref<HTMLElement | null>(null)

  /**
   * 滚动到消息列表底部
   */
  const scrollToBottom = () => {
    nextTick(() => {
      if (messageContainer.value) {
        messageContainer.value.scrollTop = messageContainer.value.scrollHeight
      }
    })
  }

  /**
   * 渲染 Markdown 内容
   */
  const renderMarkdown = (content: string): string => {
    if (!content) return ''
    try {
      return marked.parse(content) as string
    } catch {
      return content
    }
  }

  /**
   * 发送消息
   */
  const handleSendMessage = async () => {
    const text = messageText.value.trim()
    if (await storeSendMessage(text, scrollToBottom)) {
      messageText.value = ''
    }
  }

  /**
   * 清空对话
   */
  const handleClearMessages = async () => {
    if (messages.value.length === 0) return

    try {
      await ElMessageBox.confirm('确定要清空所有对话记录吗？', '提示', {
        confirmButtonText: '确定',
        cancelButtonText: '取消',
        type: 'warning'
      })
      storeClearMessages()
      ElMessage.success('对话已清空')
    } catch {
      // 用户取消
    }
  }

  onMounted(() => {
    scrollToBottom()
  })
</script>

<style scoped>
  .page-content {
    background: linear-gradient(135deg, var(--el-bg-color) 0%, var(--el-fill-color-light) 100%);
  }

  :deep(.el-textarea__inner) {
    box-shadow: none !important;
    border-radius: 12px;
  }

  :deep(.el-textarea__inner:focus) {
    box-shadow: 0 0 0 1px var(--el-color-primary) !important;
  }

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
