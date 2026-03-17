import request from '@/utils/http'

/**
 * 代码生成API
 */
export default {
  /**
   * 获取数据列表
   * @returns
   */
  getPageList(params: Record<string, any> = {}) {
    return request.get<Api.Common.ApiPage>({ url: '/app/saicode/table/index', params })
  },

  /**
   * 删除数据
   * @returns
   */
  realDestroy(data: Record<string, any>) {
    return request.del<any>({
      url: '/app/saicode/table/realDestroy',
      data
    })
  },

  /**
   * 编辑生成信息
   * @returns
   */
  update(id: string, data: Record<string, any>) {
    return request.put<any>({
      url: '/app/saicode/table/update?id=' + id,
      data
    })
  },

  /**
   * 读取信息
   * @returns
   */
  readTable(id: string) {
    return request.get<any>({
      url: '/app/saicode/table/read?id=' + id
    })
  },

  /**
   * 生成代码
   * @returns
   */
  generateCode(data: Record<string, any>) {
    return request.post<any>({
      url: '/app/saicode/table/generate',
      responseType: 'blob',
      timeout: 20 * 1000,
      data
    })
  },

  /**
   * 生成到文件
   * @returns
   */
  generateFile(data: Record<string, any>) {
    return request.post<any>({
      url: '/app/saicode/table/generateFile',
      data
    })
  },

  /**
   * 装载数据表
   * @returns
   */
  loadTable(data: Record<string, any>) {
    return request.post<any>({
      url: '/app/saicode/table/loadTable',
      data
    })
  },

  /**
   * 同步数据表
   * @returns
   */
  sync(id: string) {
    return request.post<any>({
      url: '/app/saicode/table/sync?id=' + id
    })
  },

  /**
   * 预览代码
   * @returns
   */
  preview(id: string) {
    return request.get<any>({
      url: '/app/saicode/table/preview?id=' + id
    })
  },

  // 获取表中字段信息
  getTableColumns(params: Record<string, any>) {
    return request.get<any>({
      url: '/app/saicode/table/getTableColumns',
      params
    })
  },

  // 获取数据源列表
  getDataSource(params: Record<string, any>) {
    return request.get<any>({
      url: '/app/saicode/table/source',
      params
    })
  },

  // 数据源数据表列表
  getSourceTable(params: Record<string, any>) {
    return request.get<Api.Common.ApiPage>({
      url: '/app/saicode/table/sourceTable',
      params
    })
  },

  // 保存表单设计
  saveDesign(data: Record<string, any>) {
    return request.post<any>({
      url: '/app/saicode/table/saveDesign',
      data
    })
  },

  // 保存搜索设计
  saveSearchDesign(data: Record<string, any>) {
    return request.post<any>({
      url: '/app/saicode/table/saveSearchDesign',
      data
    })
  }
}
