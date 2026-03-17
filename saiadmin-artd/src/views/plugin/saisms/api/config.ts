import request from '@/utils/http'

/**
 * 短信配置 API接口
 */
export default {
  /**
   * 获取数据列表
   * @param params 搜索参数
   * @returns 数据列表
   */
  list(params: Record<string, any>) {
    return request.get<Api.Common.ApiPage>({
      url: '/app/saisms/admin/SmsConfig/index',
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
      url: '/app/saisms/admin/SmsConfig/read?id=' + id
    })
  },

  /**
   * 创建数据
   * @param params 数据参数
   * @returns 执行结果
   */
  save(params: Record<string, any>) {
    return request.post<any>({
      url: '/app/saisms/admin/SmsConfig/save',
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
      url: '/app/saisms/admin/SmsConfig/update',
      data: params
    })
  },

  /**
   * 删除数据
   * @param params 包含ids的参数
   * @returns 执行结果
   */
  delete(params: Record<string, any>) {
    return request.del<any>({
      url: '/app/saisms/admin/SmsConfig/destroy',
      data: params
    })
  },

  /**
   * 修改状态
   * @param params 包含id和status的参数
   * @returns 执行结果
   */
  changeStatus(params: Record<string, any>) {
    return request.post<any>({
      url: '/app/saisms/admin/SmsConfig/changeStatus',
      data: params
    })
  }
}
