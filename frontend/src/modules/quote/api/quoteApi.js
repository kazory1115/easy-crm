/**
 * 報價單 API
 *
 * 封裝所有報價單相關的 API 請求
 */

import { get, post, put, del, download } from '@/utils/http'

/**
 * 報價單 API 端點
 */
const QUOTE_API = {
  QUOTES: '/quotes',
  QUOTE_DETAIL: (id) => `/quotes/${id}`,
  QUOTE_PDF: (id) => `/quotes/${id}/pdf`,
  QUOTE_EXCEL: (id) => `/quotes/${id}/excel`,
  TEMPLATES: '/templates',
  TEMPLATE_DETAIL: (id) => `/templates/${id}`
}

// ==========================================
// 報價單 CRUD
// ==========================================

/**
 * 取得報價單列表
 * @param {Object} params - 查詢參數
 * @param {number} params.page - 頁碼
 * @param {number} params.per_page - 每頁筆數
 * @param {string} params.search - 搜尋關鍵字
 * @param {string} params.status - 狀態篩選
 * @param {string} params.sort_by - 排序欄位
 * @param {string} params.sort_order - 排序方向 (asc/desc)
 * @returns {Promise<Object>} 報價單列表
 */
export function getQuotes(params = {}) {
  return get(QUOTE_API.QUOTES, params)
}

/**
 * 取得單一報價單
 * @param {number} id - 報價單 ID
 * @returns {Promise<Object>} 報價單資料
 */
export function getQuote(id) {
  return get(QUOTE_API.QUOTE_DETAIL(id))
}

/**
 * 建立報價單
 * @param {Object} data - 報價單資料
 * @returns {Promise<Object>} 建立的報價單
 */
export function createQuote(data) {
  return post(QUOTE_API.QUOTES, data)
}

/**
 * 更新報價單
 * @param {number} id - 報價單 ID
 * @param {Object} data - 更新資料
 * @returns {Promise<Object>} 更新後的報價單
 */
export function updateQuote(id, data) {
  return put(QUOTE_API.QUOTE_DETAIL(id), data)
}

/**
 * 刪除報價單
 * @param {number} id - 報價單 ID
 * @returns {Promise<void>}
 */
export function deleteQuote(id) {
  return del(QUOTE_API.QUOTE_DETAIL(id))
}

/**
 * 批次刪除報價單
 * @param {Array<number>} ids - 報價單 ID 列表
 * @returns {Promise<void>}
 */
export function batchDeleteQuotes(ids) {
  return post(`${QUOTE_API.QUOTES}/batch-delete`, { ids })
}

// ==========================================
// 報價單狀態管理
// ==========================================

/**
 * 更新報價單狀態
 * @param {number} id - 報價單 ID
 * @param {string} status - 狀態 (draft/sent/approved/rejected)
 * @returns {Promise<Object>} 更新後的報價單
 */
export function updateQuoteStatus(id, status) {
  return patch(`${QUOTE_API.QUOTE_DETAIL(id)}/status`, { status })
}

/**
 * 發送報價單（更新狀態為 sent）
 * @param {number} id - 報價單 ID
 * @param {Object} data - 發送選項
 * @param {string} data.email - 收件人 Email
 * @param {string} data.subject - 郵件主旨
 * @param {string} data.message - 郵件內容
 * @returns {Promise<Object>}
 */
export function sendQuote(id, data) {
  return post(`${QUOTE_API.QUOTE_DETAIL(id)}/send`, data)
}

// ==========================================
// 報價單匯出
// ==========================================

/**
 * 匯出報價單為 PDF
 * @param {number} id - 報價單 ID
 * @param {string} filename - 檔案名稱
 * @returns {Promise<void>}
 */
export function exportQuotePDF(id, filename) {
  const defaultFilename = `quote_${id}_${Date.now()}.pdf`
  return download(QUOTE_API.QUOTE_PDF(id), filename || defaultFilename)
}

/**
 * 匯出報價單為 Excel
 * @param {number} id - 報價單 ID
 * @param {string} filename - 檔案名稱
 * @returns {Promise<void>}
 */
export function exportQuoteExcel(id, filename) {
  const defaultFilename = `quote_${id}_${Date.now()}.xlsx`
  return download(QUOTE_API.QUOTE_EXCEL(id), filename || defaultFilename)
}

/**
 * 批次匯出報價單
 * @param {Array<number>} ids - 報價單 ID 列表
 * @param {string} format - 格式 (pdf/excel)
 * @param {string} filename - 檔案名稱
 * @returns {Promise<void>}
 */
export function batchExportQuotes(ids, format = 'pdf', filename) {
  const defaultFilename = `quotes_${Date.now()}.${format === 'pdf' ? 'pdf' : 'xlsx'}`
  return download(
    `${QUOTE_API.QUOTES}/batch-export`,
    filename || defaultFilename,
    { ids, format }
  )
}

// ==========================================
// 範本 CRUD
// ==========================================

/**
 * 取得範本列表
 * @param {Object} params - 查詢參數
 * @returns {Promise<Array>} 範本列表
 */
export function getTemplates(params = {}) {
  return get(QUOTE_API.TEMPLATES, params)
}

/**
 * 取得單一範本
 * @param {number} id - 範本 ID
 * @returns {Promise<Object>} 範本資料
 */
export function getTemplate(id) {
  return get(QUOTE_API.TEMPLATE_DETAIL(id))
}

/**
 * 建立範本
 * @param {Object} data - 範本資料
 * @returns {Promise<Object>} 建立的範本
 */
export function createTemplate(data) {
  return post(QUOTE_API.TEMPLATES, data)
}

/**
 * 更新範本
 * @param {number} id - 範本 ID
 * @param {Object} data - 更新資料
 * @returns {Promise<Object>} 更新後的範本
 */
export function updateTemplate(id, data) {
  return put(QUOTE_API.TEMPLATE_DETAIL(id), data)
}

/**
 * 刪除範本
 * @param {number} id - 範本 ID
 * @returns {Promise<void>}
 */
export function deleteTemplate(id) {
  return del(QUOTE_API.TEMPLATE_DETAIL(id))
}

// ==========================================
// 統計資料
// ==========================================

/**
 * 取得報價單統計
 * @param {Object} params - 查詢參數
 * @param {string} params.start_date - 開始日期
 * @param {string} params.end_date - 結束日期
 * @returns {Promise<Object>} 統計資料
 */
export function getQuoteStats(params = {}) {
  return get(`${QUOTE_API.QUOTES}/stats`, params)
}

// 導出預設物件
export default {
  getQuotes,
  getQuote,
  createQuote,
  updateQuote,
  deleteQuote,
  batchDeleteQuotes,
  updateQuoteStatus,
  sendQuote,
  exportQuotePDF,
  exportQuoteExcel,
  batchExportQuotes,
  getTemplates,
  getTemplate,
  createTemplate,
  updateTemplate,
  deleteTemplate,
  getQuoteStats
}
