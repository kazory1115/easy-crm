/**
 * 員工管理 API 層
 *
 * 提供員工資料與角色權限的 CRUD 操作
 */

import { get, post, put, del } from '@/utils/http'

const API_BASE = '/users'  // 修正為正確的後端 API 路徑
const ROLE_API_BASE = '/roles'

// ==================== 員工 API ====================

/**
 * 取得員工列表
 * @param {Object} params - 查詢參數
 * @param {number} params.page - 頁碼
 * @param {number} params.per_page - 每頁數量
 * @param {string} params.search - 搜尋關鍵字
 * @param {string} params.department - 部門篩選
 * @param {string} params.status - 狀態篩選
 * @returns {Promise<Object>} 員工列表與分頁資訊
 */
export const getStaffList = async (params = {}) => {
  return await get(API_BASE, params)
}

/**
 * 取得單一員工詳情
 * @param {string|number} id - 員工 ID
 * @returns {Promise<Object>} 員工詳情
 */
export const getStaff = async (id) => {
  return await get(`${API_BASE}/${id}`)
}

/**
 * 新增員工
 * @param {Object} data - 員工資料
 * @returns {Promise<Object>} 新增的員工資料
 */
export const createStaff = async (data) => {
  return await post(API_BASE, data)
}

/**
 * 更新員工資料
 * @param {string|number} id - 員工 ID
 * @param {Object} data - 更新的資料
 * @returns {Promise<Object>} 更新後的員工資料
 */
export const updateStaff = async (id, data) => {
  return await put(`${API_BASE}/${id}`, data)
}

/**
 * 刪除員工
 * @param {string|number} id - 員工 ID
 * @returns {Promise<Object>} 刪除結果
 */
export const deleteStaff = async (id) => {
  return await del(`${API_BASE}/${id}`)
}

/**
 * 批次刪除員工
 * @param {Array<string|number>} ids - 員工 ID 陣列
 * @returns {Promise<Object>} 刪除結果
 */
export const batchDeleteStaff = async (ids) => {
  return await post(`${API_BASE}/batch-delete`, { ids })
}

/**
 * 更新員工狀態
 * @param {string|number} id - 員工 ID
 * @param {string} status - 狀態 (active/inactive/suspended)
 * @returns {Promise<Object>} 更新結果
 */
export const updateStaffStatus = async (id, status) => {
  return await put(`${API_BASE}/${id}/status`, { status })
}

/**
 * 取得員工統計資料
 * @returns {Promise<Object>} 統計資料
 */
export const getStaffStats = async () => {
  return await get(`${API_BASE}/stats`)
}

/**
 * 匯出員工資料
 * @param {Object} params - 匯出參數
 * @param {string} params.format - 匯出格式 (excel/pdf)
 * @param {Array} params.ids - 指定員工 ID（可選）
 * @returns {Promise<Blob>} 檔案資料
 */
export const exportStaff = async (params = {}) => {
  return await get(`${API_BASE}/export`, params, { responseType: 'blob' })
}

// ==================== 角色 API ====================

/**
 * 取得角色列表
 * @returns {Promise<Array>} 角色列表
 */
export const getRoles = async () => {
  return await get(ROLE_API_BASE)
}

/**
 * 取得單一角色詳情
 * @param {string|number} id - 角色 ID
 * @returns {Promise<Object>} 角色詳情
 */
export const getRole = async (id) => {
  return await get(`${ROLE_API_BASE}/${id}`)
}

/**
 * 新增角色
 * @param {Object} data - 角色資料
 * @returns {Promise<Object>} 新增的角色資料
 */
export const createRole = async (data) => {
  return await post(ROLE_API_BASE, data)
}

/**
 * 更新角色
 * @param {string|number} id - 角色 ID
 * @param {Object} data - 更新的資料
 * @returns {Promise<Object>} 更新後的角色資料
 */
export const updateRole = async (id, data) => {
  return await put(`${ROLE_API_BASE}/${id}`, data)
}

/**
 * 刪除角色
 * @param {string|number} id - 角色 ID
 * @returns {Promise<Object>} 刪除結果
 */
export const deleteRole = async (id) => {
  return await del(`${ROLE_API_BASE}/${id}`)
}

/**
 * 取得所有權限列表
 * @returns {Promise<Array>} 權限列表
 */
export const getPermissions = async () => {
  return await get(`${ROLE_API_BASE}/permissions`)
}

/**
 * 指派角色給員工
 * @param {string|number} staffId - 員工 ID
 * @param {Array<string|number>} roleIds - 角色 ID 陣列
 * @returns {Promise<Object>} 指派結果
 */
export const assignRoles = async (staffId, roleIds) => {
  return await post(`${API_BASE}/${staffId}/roles`, { role_ids: roleIds })
}

// ==================== 部門 API ====================

/**
 * 取得部門列表
 * @returns {Promise<Array>} 部門列表
 */
export const getDepartments = async () => {
  return await get(`${API_BASE}/departments`)
}

/**
 * 取得組織架構
 * @returns {Promise<Object>} 組織架構樹
 */
export const getOrgStructure = async () => {
  return await get(`${API_BASE}/org-structure`)
}
