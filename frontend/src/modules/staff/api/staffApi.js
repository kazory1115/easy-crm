/**
 * 員工管理 API 層（僅保留後端目前已提供的路由）
 */

import { get, post, put, del } from '@/utils/http'

const API_BASE = '/users'

/**
 * 取得員工列表
 */
export const getStaffList = async (params = {}) => {
  return await get(API_BASE, params)
}

/**
 * 取得單一員工
 */
export const getStaff = async (id) => {
  return await get(`${API_BASE}/${id}`)
}

/**
 * 新增員工
 */
export const createStaff = async (data) => {
  return await post(API_BASE, data)
}

/**
 * 更新員工
 */
export const updateStaff = async (id, data) => {
  return await put(`${API_BASE}/${id}`, data)
}

/**
 * 刪除員工
 */
export const deleteStaff = async (id) => {
  return await del(`${API_BASE}/${id}`)
}

/**
 * 取得員工統計資料
 */
export const getStaffStats = async () => {
  return await get(`${API_BASE}/stats`)
}
