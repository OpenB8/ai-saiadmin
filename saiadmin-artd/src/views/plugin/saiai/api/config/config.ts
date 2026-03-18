import request from '@/utils/http'

/**
 * AI配置 API接口
 */
export default {
  /**
   * 获取数据列表
   * @param params 搜索参数
   * @returns 数据列表
   */
  list(params: Record<string, any>) {
    return request.get<Api.Common.ApiPage>({
      url: '/app/saiai/admin/config/AiConfig/index',
      params
    })
  },

  /**
   * 获取ai列表
   * @param params 搜索参数
   * @returns 数据列表
   */
  getAiList(params: Record<string, any>) {
    return request.get<Api.Common.ApiData[]>({
      url: '/app/saiai/admin/config/AiConfig/getAiList',
      params
    })
  },

  /**
   * 获取模型列表
   * @param params 搜索参数
   * @returns 数据列表
   */
  getModel(params: Record<string, any>) {
    return request.get<Api.Common.ApiData[]>({
      url: '/app/saiai/admin/config/AiConfig/getModel',
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
      url: '/app/saiai/admin/config/AiConfig/read?id=' + id
    })
  },

  /**
   * 创建数据
   * @param params 数据参数
   * @returns 执行结果
   */
  save(params: Record<string, any>) {
    return request.post<any>({
      url: '/app/saiai/admin/config/AiConfig/save',
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
      url: '/app/saiai/admin/config/AiConfig/update',
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
      url: '/app/saiai/admin/config/AiConfig/destroy',
      data: params
    })
  }
}
