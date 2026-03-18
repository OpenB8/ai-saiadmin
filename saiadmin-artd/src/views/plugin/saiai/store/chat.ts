/**
 * AI 对话状态管理模块
 *
 * 提供 AI 聊天消息的状态管理、持久化和发送功能
 *
 * @module store/chat
 */
import { defineStore } from 'pinia'
import { ref, watch } from 'vue'
import { ElMessage } from 'element-plus'
import aiAvatar from '../assets/logo.png'
import { useUserStore } from '@/store/modules/user'

// 扩展 window 类型
declare global {
  interface Window {
    aiAbortController?: AbortController
  }
}

/** 消息类型定义 */
export interface ChatMessage {
  /** 消息唯一标识 */
  id: number
  /** 发送者名称 */
  sender: string
  /** 消息内容 */
  content: string
  /** 发送时间 */
  time: string
  /** 是否为用户消息 */
  isMe: boolean
  /** 头像 */
  avatar: string
  /** 是否正在流式输出 */
  isStreaming?: boolean
}

// 存储 key
const STORAGE_KEY = 'saiai_messages'
const MODEL_STORAGE_KEY = 'saiai_current_model'

/**
 * AI 聊天状态管理
 */
export const useChatStore = defineStore('saiaiChat', () => {
  // 消息列表
  const messages = ref<ChatMessage[]>([])
  // 消息 ID 计数器
  const messageId = ref(1)
  // 是否正在加载
  const isLoading = ref(false)
  // 是否已连接
  const isConnected = ref(true)

  /**
   * 从 localStorage 加载保存的模型
   */
  const loadStoredModel = () => {
    try {
      const stored = localStorage.getItem(MODEL_STORAGE_KEY)
      if (stored) {
        return JSON.parse(stored)
      }
    } catch (e) {
      console.error('Failed to load stored model:', e)
    }
    return null
  }

  /**
   * 保存模型到 localStorage
   */
  const saveModel = (model: any) => {
    try {
      localStorage.setItem(MODEL_STORAGE_KEY, JSON.stringify(model))
    } catch (e) {
      console.error('Failed to save model:', e)
    }
  }

  // 当前使用的模型 - 优先从 localStorage 恢复
  const storedModel = loadStoredModel()
  const currentModel = ref(
    storedModel || {
      id: null,
      type: '',
      name: '',
      model: ''
    }
  )

  // 当前会话分组ID
  const currentGroupId = ref<number | null>(null)

  /**
   * 从 sessionStorage 恢复消息
   */
  const loadMessages = () => {
    // 移除自动恢复逻辑，改为手动加载
  }

  /**
   * 保存消息到 sessionStorage
   */
  const saveMessages = () => {
    // 移除自动保存逻辑，依靠后端持久化
  }

  /**
   * 添加消息
   */
  const addMessage = (message: Omit<ChatMessage, 'id'>) => {
    const newMessage: ChatMessage = {
      ...message,
      id: messageId.value++
    }
    messages.value.push(newMessage)
    return messages.value.length - 1 // 返回索引
  }

  /**
   * 更新消息
   */
  const updateMessage = (index: number, updates: Partial<ChatMessage>) => {
    if (messages.value[index]) {
      messages.value[index] = {
        ...messages.value[index],
        ...updates
      }
    }
  }

  /**
   * 追加消息内容
   */
  const appendContent = (index: number, content: string) => {
    if (messages.value[index]) {
      messages.value[index] = {
        ...messages.value[index],
        content: messages.value[index].content + content
      }
    }
  }

  /**
   * 清空所有消息
   */
  const clearMessages = () => {
    messages.value = []
    messageId.value = 1
    sessionStorage.removeItem(STORAGE_KEY)
  }

  /**
   * 获取当前时间字符串
   */
  const getCurrentTime = () => {
    return new Date().toLocaleTimeString('zh-CN', {
      hour: '2-digit',
      minute: '2-digit'
    })
  }

  /**
   * 发送消息并获取 AI 流式响应
   * @param text 用户输入的消息文本
   * @param onScroll 滚动回调函数
   */
  const sendMessage = async (text: string, onScroll?: () => void) => {
    if (!text.trim() || isLoading.value) return false

    // 获取 token
    const userStore = useUserStore()
    const token = userStore.accessToken
    const avatar = userStore.info.avatar

    // 添加用户消息
    addMessage({
      sender: '我',
      content: text.trim(),
      time: getCurrentTime(),
      isMe: true,
      avatar: avatar || ''
    })
    onScroll?.()

    // 添加 AI 消息占位
    const aiMessageIndex = addMessage({
      sender: currentModel.value.name || 'SaiAI',
      content: '',
      time: getCurrentTime(),
      isMe: false,
      avatar: aiAvatar,
      isStreaming: true
    })
    onScroll?.()

    isLoading.value = true
    isConnected.value = true

    // 构建 API URL
    const baseURL = import.meta.env.VITE_API_URL || ''
    const url = `${baseURL}/app/saiai/api/index/index`

    // 取消之前的请求
    if (window.aiAbortController) {
      window.aiAbortController.abort()
    }

    // 创建新的 AbortController
    const controller = new AbortController()
    window.aiAbortController = controller

    try {
      const body: any = {
        message: text.trim(),
        type: currentModel.value.type
      }
      if (currentGroupId.value) {
        body.group_id = currentGroupId.value
      }

      const response = await fetch(url, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          Authorization: `Bearer ${token}`
        },
        body: JSON.stringify(body),
        signal: controller.signal
      })

      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`)
      }

      if (!response.body) {
        throw new Error('Response body is null')
      }

      const reader = response.body.getReader()
      const decoder = new TextDecoder()
      let buffer = ''

      while (true) {
        const { done, value } = await reader.read()
        if (done) break

        const chunk = decoder.decode(value, { stream: true })
        buffer += chunk

        const lines = buffer.split('\n')
        // 保留最后一行，因为可能是不完整的
        buffer = lines.pop() || ''

        for (const line of lines) {
          if (line.startsWith('data: ')) {
            const dataStr = line.slice(6).trim()
            if (!dataStr) continue

            try {
              const parsed = JSON.parse(dataStr)
              switch (parsed.type) {
                case 'start':
                  isConnected.value = true
                  break
                case 'session_id':
                  // 更新会话ID
                  currentGroupId.value = parsed.data
                  // 触发事件通知外部刷新列表（如果有必要）
                  break
                case 'content':
                  if (parsed.data) {
                    appendContent(aiMessageIndex, parsed.data)
                    onScroll?.()
                  }
                  break
                case 'done':
                  updateMessage(aiMessageIndex, { isStreaming: false })
                  isLoading.value = false
                  break
                case 'error':
                  updateMessage(aiMessageIndex, {
                    isStreaming: false,
                    content: `抱歉，请求失败：${parsed.data || '未知错误'}。请稍后重试。`
                  })
                  isLoading.value = false
                  break
              }
            } catch (e) {
              console.error('Failed to parse SSE data:', e)
            }
          }
        }
      }

      // 处理剩余的 buffer (通常 SSE 最后会有一个空行，buffer 为空，但以防万一)
      if (buffer.startsWith('data: ')) {
        try {
          const dataStr = buffer.slice(6).trim()
          if (dataStr) {
            const parsed = JSON.parse(dataStr)
            if (parsed.type === 'content' && parsed.data) {
              appendContent(aiMessageIndex, parsed.data)
              onScroll?.()
            }
          }
        } catch {}
      }
    } catch (error: any) {
      if (error.name === 'AbortError') {
        // 用户取消，不做处理
        return true
      }

      isConnected.value = false
      const msg = messages.value[aiMessageIndex]
      updateMessage(aiMessageIndex, {
        isStreaming: false,
        content: msg?.content || '抱歉，连接失败。请稍后重试。'
      })
      isLoading.value = false
      ElMessage.error('AI 响应失败，请检查网络连接')
      onScroll?.()
      console.error('Fetch error:', error)
    } finally {
      window.aiAbortController = undefined
    }

    return true
  }

  // 初始化时加载消息
  loadMessages()

  // 监听 messages 变化，自动保存
  watch(
    messages,
    () => {
      saveMessages()
    },
    { deep: true }
  )

  return {
    messages,
    messageId,
    isLoading,
    isConnected,
    aiAvatar,
    addMessage,
    updateMessage,
    appendContent,
    clearMessages,
    getCurrentTime,
    sendMessage,
    currentModel,
    currentGroupId,
    saveModel
  }
})

export default useChatStore
