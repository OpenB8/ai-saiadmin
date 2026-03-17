import request from '@/utils/http'

/**
 * 短信记录 API接口
 */
export default {
  /**
   * 获取数据列表
   * @param params 搜索参数
   * @returns 数据列表
   */
  list(params: Record<string, any>) {
    return request.get<Api.Common.ApiPage>({
      url: '/app/saisms/admin/SmsRecord/index',
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
      url: '/app/saisms/admin/SmsRecord/read?id=' + id
    })
  },

  /**
   * 删除数据
   * @param params 包含ids的参数
   * @returns 执行结果
   */
  delete(params: Record<string, any>) {
    return request.del<any>({
      url: '/app/saisms/admin/SmsRecord/destroy',
      data: params
    })
  }
}
