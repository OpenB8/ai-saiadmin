import request from '@/utils/http'

/**
 * 对话分组 API接口
 */
export default {
  /**
   * 获取数据列表
   * @param params 搜索参数
   * @returns 数据列表
   */
  list(params: Record<string, any>) {
    return request.get<Api.Common.ApiPage>({
      url: '/app/saiai/admin/chat/AiChatGroup/index',
      params
    })
  },

  /**
   * 读取数据
   * @param id 数据ID
   * @returns 数据详情
   */
  read(id: number | string) {
    return request.get<Api.Common.ApiData>({
      url: '/app/saiai/admin/chat/AiChatGroup/read?id=' + id
    })
  },

  /**
   * 创建数据
   * @param params 数据参数
   * @returns 执行结果
   */
  save(params: Record<string, any>) {
    return request.post<any>({
      url: '/app/saiai/admin/chat/AiChatGroup/save',
      data: params
    })
  },

  /**
   * 更新数据
   * @param params 数据参数
   * @returns 执行结果
   */
  update(params: Record<string, any>) {
    return request.put<any>({
      url: '/app/saiai/admin/chat/AiChatGroup/update',
      data: params
    })
  },

  /**
   * 删除数据
   * @param id 数据ID
   * @returns 执行结果
   */
  delete(params: Record<string, any>) {
    return request.del<any>({
      url: '/app/saiai/admin/chat/AiChatGroup/destroy',
      data: params
    })
  },

  /**
   * 聊天列表
   * @param params 搜索参数
   * @returns 数据列表
   */
  chatList(params: Record<string, any>) {
    return request.get<Api.Common.ApiData[]>({
      url: '/app/saiai/admin/chat/AiChatGroup/chatList',
      params
    })
  },

  /**
   * 删除聊天
   * @param params 数据参数
   * @returns 执行结果
   */
  deleteChat(params: Record<string, any>) {
    return request.del<any>({
      url: '/app/saiai/admin/chat/AiChatGroup/deleteChat',
      data: params
    })
  }
}
