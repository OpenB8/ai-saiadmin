import request from '@/utils/http'

/**
 * API接口
 */
export default {
  /**
   * 获取数据列表
   * @param params 搜索参数
   * @returns 数据列表
   */
  modelList(params: Record<string, any>) {
    return request.get<Api.Common.ApiData[]>({
      url: '/app/saiai/api/index/modelList',
      params
    })
  },

  /**
   * 获取默认模型
   * @returns 模型
   */
  defaultModel() {
    return request.get<Api.Common.ApiData>({
      url: '/app/saiai/api/index/defaultModel'
    })
  },

  /**
   * 获取会话列表
   */
  getSessionList() {
    return request.get<any[]>({
      url: '/app/saiai/api/ChatGroup/getList'
    })
  },

  /**
   * 创建新会话
   */
  createSession(title: string) {
    return request.post<any>({
      url: '/app/saiai/api/ChatGroup/create',
      data: { title }
    })
  },

  /**
   * 获取会话详情
   */
  getSessionDetail(id: number) {
    return request.get<any>({
      url: '/app/saiai/api/ChatGroup/detail',
      params: { id }
    })
  },

  /**
   * 删除会话
   */
  deleteSession(id: number) {
    return request.post<any>({
      url: '/app/saiai/api/ChatGroup/delete',
      data: { id }
    })
  },

  /**
   * 更新会话标题
   */
  updateSession(id: number, title: string) {
    return request.post<any>({
      url: '/app/saiai/api/ChatGroup/update',
      data: { id, title }
    })
  }
}
