import { get, post, put, del } from '@/utils/http'

const API_BASE = '/users'

export const getStaffList = async (params = {}) => {
  return await get(API_BASE, params)
}

export const getStaff = async (id) => {
  return await get(`${API_BASE}/${id}`)
}

export const createStaff = async (data) => {
  return await post(API_BASE, data)
}

export const updateStaff = async (id, data) => {
  return await put(`${API_BASE}/${id}`, data)
}

export const deleteStaff = async (id) => {
  return await del(`${API_BASE}/${id}`)
}

export const getStaffStats = async () => {
  return await get(`${API_BASE}/stats`)
}

export const getStaffRoles = async (id) => {
  return await get(`${API_BASE}/${id}/roles`)
}

export const updateStaffRoles = async (id, roles = []) => {
  return await put(`${API_BASE}/${id}/roles`, { roles })
}

export const getStaffPermissions = async (id) => {
  return await get(`${API_BASE}/${id}/permissions`)
}

export const updateStaffPermissions = async (id, directPermissions = []) => {
  return await put(`${API_BASE}/${id}/permissions`, {
    direct_permissions: directPermissions
  })
}

export const getPermissionModules = async () => {
  return await get('/permissions/modules')
}
