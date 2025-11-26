/**
 * 操作紀錄 API 層
 *
 * 提供系統操作紀錄的查詢功能
 */

import { get } from '@/utils/http'

const API_BASE = '/activity-logs'

// ==================== 操作紀錄 API ====================

/**
 * 取得操作紀錄列表
 * @param {Object} params - 查詢參數
 * @param {number} params.page - 頁碼
 * @param {number} params.per_page - 每頁數量
 * @param {string} params.search - 搜尋關鍵字
 * @param {string} params.event - 事件類型篩選 (created/updated/deleted)
 * @param {string} params.module - 模組篩選
 * @param {string} params.causer_id - 操作者 ID 篩選
 * @param {string} params.start_date - 開始日期
 * @param {string} params.end_date - 結束日期
 * @returns {Promise<Object>} 操作紀錄列表與分頁資訊
 */
export const getActivityLogs = async (params = {}) => {
  return await get(API_BASE, params)
}

/**
 * 取得單一操作紀錄詳情
 * @param {string|number} id - 紀錄 ID
 * @returns {Promise<Object>} 紀錄詳情
 */
export const getActivityLog = async (id) => {
  return await get(`${API_BASE}/${id}`)
}

/**
 * 取得目前使用者的操作紀錄
 * @param {Object} params - 查詢參數
 * @param {number} params.page - 頁碼
 * @param {number} params.per_page - 每頁數量
 * @param {string} params.event - 事件類型篩選
 * @param {string} params.start_date - 開始日期
 * @param {string} params.end_date - 結束日期
 * @returns {Promise<Object>} 操作紀錄列表與分頁資訊
 */
export const getMyActivityLogs = async (params = {}) => {
  return await get(`${API_BASE}/my-logs`, params)
}

/**
 * 取得特定模組的操作紀錄
 * @param {string} module - 模組名稱 (quote/template/customer/user 等)
 * @param {Object} params - 查詢參數
 * @param {number} params.page - 頁碼
 * @param {number} params.per_page - 每頁數量
 * @param {string} params.event - 事件類型篩選
 * @param {string} params.start_date - 開始日期
 * @param {string} params.end_date - 結束日期
 * @returns {Promise<Object>} 操作紀錄列表與分頁資訊
 */
export const getModuleActivityLogs = async (module, params = {}) => {
  return await get(`${API_BASE}/module/${module}`, params)
}

/**
 * 取得操作紀錄統計資料
 * @param {Object} params - 查詢參數
 * @param {string} params.group_by - 分組方式 (event/module)
 * @param {string} params.start_date - 開始日期
 * @param {string} params.end_date - 結束日期
 * @returns {Promise<Object>} 統計資料
 */
export const getActivityLogStats = async (params = {}) => {
  return await get(`${API_BASE}/stats`, params)
}
