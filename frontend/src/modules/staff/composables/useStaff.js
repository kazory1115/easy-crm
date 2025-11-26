/**
 * 員工管理 Composable
 *
 * 提供員工管理的業務邏輯與狀態管理
 */

import { ref, computed } from 'vue'
import { useAppStore } from '@/stores/app'
import * as staffApi from '../api/staffApi'

// 是否使用 API 模式（false 時使用 LocalStorage）
const USE_API = true
const STORAGE_KEY = 'easy_crm_staff'
const ROLES_STORAGE_KEY = 'easy_crm_roles'

/**
 * 員工管理 Composable
 */
export function useStaff() {
  const appStore = useAppStore()

  // 狀態
  const staffList = ref([])
  const currentStaff = ref(null)
  const roles = ref([])
  const departments = ref([])
  const loading = ref(false)
  const error = ref(null)

  // 統計資料
  const stats = computed(() => {
    const list = staffList.value
    return {
      total: list.length,
      active: list.filter(s => s.status === 'active').length,
      inactive: list.filter(s => s.status === 'inactive').length,
      suspended: list.filter(s => s.status === 'suspended').length,
      byDepartment: list.reduce((acc, s) => {
        acc[s.department] = (acc[s.department] || 0) + 1
        return acc
      }, {})
    }
  })

  // ==================== LocalStorage 操作 ====================

  /**
   * 從 LocalStorage 讀取資料
   */
  const loadFromStorage = () => {
    try {
      const data = localStorage.getItem(STORAGE_KEY)
      staffList.value = data ? JSON.parse(data) : []

      const rolesData = localStorage.getItem(ROLES_STORAGE_KEY)
      roles.value = rolesData ? JSON.parse(rolesData) : getDefaultRoles()

      departments.value = getDefaultDepartments()
    } catch (e) {
      console.error('載入員工資料失敗:', e)
      staffList.value = []
      roles.value = getDefaultRoles()
    }
  }

  /**
   * 儲存資料到 LocalStorage
   */
  const saveToStorage = () => {
    try {
      localStorage.setItem(STORAGE_KEY, JSON.stringify(staffList.value))
      localStorage.setItem(ROLES_STORAGE_KEY, JSON.stringify(roles.value))
    } catch (e) {
      console.error('儲存員工資料失敗:', e)
    }
  }

  /**
   * 取得預設角色
   */
  const getDefaultRoles = () => [
    {
      id: 'admin',
      name: '系統管理員',
      description: '擁有系統所有權限',
      permissions: ['*'],
      isSystem: true,
      createdAt: new Date().toISOString()
    },
    {
      id: 'manager',
      name: '部門主管',
      description: '可管理部門員工與檢視報表',
      permissions: ['staff.view', 'staff.edit', 'report.view'],
      isSystem: true,
      createdAt: new Date().toISOString()
    },
    {
      id: 'staff',
      name: '一般員工',
      description: '基本操作權限',
      permissions: ['quote.view', 'quote.create', 'crm.view'],
      isSystem: true,
      createdAt: new Date().toISOString()
    }
  ]

  /**
   * 取得預設部門
   */
  const getDefaultDepartments = () => [
    { id: 'sales', name: '業務部' },
    { id: 'marketing', name: '行銷部' },
    { id: 'rd', name: '研發部' },
    { id: 'hr', name: '人資部' },
    { id: 'finance', name: '財務部' },
    { id: 'it', name: '資訊部' }
  ]

  // ==================== 員工 CRUD ====================

  /**
   * 取得員工列表
   */
  const fetchStaffList = async (params = {}) => {
    loading.value = true
    error.value = null

    try {
      if (USE_API) {
        const response = await staffApi.getStaffList(params)
        staffList.value = response.data || response
      } else {
        loadFromStorage()

        // 本地篩選
        let filtered = [...staffList.value]

        if (params.search) {
          const search = params.search.toLowerCase()
          filtered = filtered.filter(s =>
            s.name.toLowerCase().includes(search) ||
            s.email.toLowerCase().includes(search) ||
            s.employeeId?.toLowerCase().includes(search)
          )
        }

        if (params.department) {
          filtered = filtered.filter(s => s.department === params.department)
        }

        if (params.status) {
          filtered = filtered.filter(s => s.status === params.status)
        }

        staffList.value = filtered
      }
    } catch (e) {
      error.value = e.message || '載入員工列表失敗'
      appStore.showNotification(error.value, 'error')
    } finally {
      loading.value = false
    }
  }

  /**
   * 取得單一員工
   */
  const fetchStaff = async (id) => {
    loading.value = true
    error.value = null

    try {
      if (USE_API) {
        currentStaff.value = await staffApi.getStaff(id)
      } else {
        loadFromStorage()
        currentStaff.value = staffList.value.find(s => s.id === id) || null
      }
      return currentStaff.value
    } catch (e) {
      error.value = e.message || '載入員工資料失敗'
      appStore.showNotification(error.value, 'error')
      return null
    } finally {
      loading.value = false
    }
  }

  /**
   * 新增員工
   */
  const createStaff = async (data) => {
    loading.value = true
    error.value = null

    try {
      let newStaff

      if (USE_API) {
        newStaff = await staffApi.createStaff(data)
      } else {
        loadFromStorage()

        newStaff = {
          ...data,
          id: `staff_${Date.now()}`,
          employeeId: generateEmployeeId(),
          status: 'active',
          createdAt: new Date().toISOString(),
          updatedAt: new Date().toISOString()
        }

        staffList.value.push(newStaff)
        saveToStorage()
      }

      appStore.showNotification('員工新增成功', 'success')
      return newStaff
    } catch (e) {
      error.value = e.message || '新增員工失敗'
      appStore.showNotification(error.value, 'error')
      return null
    } finally {
      loading.value = false
    }
  }

  /**
   * 更新員工
   */
  const updateStaff = async (id, data) => {
    loading.value = true
    error.value = null

    try {
      let updatedStaff

      if (USE_API) {
        updatedStaff = await staffApi.updateStaff(id, data)
      } else {
        loadFromStorage()

        const index = staffList.value.findIndex(s => s.id === id)
        if (index === -1) {
          throw new Error('找不到員工資料')
        }

        updatedStaff = {
          ...staffList.value[index],
          ...data,
          updatedAt: new Date().toISOString()
        }

        staffList.value[index] = updatedStaff
        saveToStorage()
      }

      appStore.showNotification('員工資料更新成功', 'success')
      return updatedStaff
    } catch (e) {
      error.value = e.message || '更新員工失敗'
      appStore.showNotification(error.value, 'error')
      return null
    } finally {
      loading.value = false
    }
  }

  /**
   * 刪除員工
   */
  const deleteStaff = async (id) => {
    loading.value = true
    error.value = null

    try {
      if (USE_API) {
        await staffApi.deleteStaff(id)
      } else {
        loadFromStorage()
        staffList.value = staffList.value.filter(s => s.id !== id)
        saveToStorage()
      }

      appStore.showNotification('員工刪除成功', 'success')
      return true
    } catch (e) {
      error.value = e.message || '刪除員工失敗'
      appStore.showNotification(error.value, 'error')
      return false
    } finally {
      loading.value = false
    }
  }

  /**
   * 批次刪除員工
   */
  const batchDeleteStaff = async (ids) => {
    loading.value = true
    error.value = null

    try {
      if (USE_API) {
        await staffApi.batchDeleteStaff(ids)
      } else {
        loadFromStorage()
        staffList.value = staffList.value.filter(s => !ids.includes(s.id))
        saveToStorage()
      }

      appStore.showNotification(`已刪除 ${ids.length} 位員工`, 'success')
      return true
    } catch (e) {
      error.value = e.message || '批次刪除失敗'
      appStore.showNotification(error.value, 'error')
      return false
    } finally {
      loading.value = false
    }
  }

  /**
   * 更新員工狀態
   */
  const updateStaffStatus = async (id, status) => {
    return await updateStaff(id, { status })
  }

  // ==================== 角色管理 ====================

  /**
   * 取得角色列表
   */
  const fetchRoles = async () => {
    loading.value = true
    error.value = null

    try {
      if (USE_API) {
        roles.value = await staffApi.getRoles()
      } else {
        loadFromStorage()
      }
      return roles.value
    } catch (e) {
      error.value = e.message || '載入角色列表失敗'
      appStore.showNotification(error.value, 'error')
      return []
    } finally {
      loading.value = false
    }
  }

  /**
   * 新增角色
   */
  const createRole = async (data) => {
    loading.value = true
    error.value = null

    try {
      let newRole

      if (USE_API) {
        newRole = await staffApi.createRole(data)
      } else {
        loadFromStorage()

        newRole = {
          ...data,
          id: `role_${Date.now()}`,
          isSystem: false,
          createdAt: new Date().toISOString()
        }

        roles.value.push(newRole)
        saveToStorage()
      }

      appStore.showNotification('角色新增成功', 'success')
      return newRole
    } catch (e) {
      error.value = e.message || '新增角色失敗'
      appStore.showNotification(error.value, 'error')
      return null
    } finally {
      loading.value = false
    }
  }

  /**
   * 更新角色
   */
  const updateRole = async (id, data) => {
    loading.value = true
    error.value = null

    try {
      if (USE_API) {
        await staffApi.updateRole(id, data)
      } else {
        loadFromStorage()

        const index = roles.value.findIndex(r => r.id === id)
        if (index === -1) {
          throw new Error('找不到角色')
        }

        if (roles.value[index].isSystem) {
          throw new Error('系統角色無法修改')
        }

        roles.value[index] = { ...roles.value[index], ...data }
        saveToStorage()
      }

      appStore.showNotification('角色更新成功', 'success')
      return true
    } catch (e) {
      error.value = e.message || '更新角色失敗'
      appStore.showNotification(error.value, 'error')
      return false
    } finally {
      loading.value = false
    }
  }

  /**
   * 刪除角色
   */
  const deleteRole = async (id) => {
    loading.value = true
    error.value = null

    try {
      if (USE_API) {
        await staffApi.deleteRole(id)
      } else {
        loadFromStorage()

        const role = roles.value.find(r => r.id === id)
        if (role?.isSystem) {
          throw new Error('系統角色無法刪除')
        }

        roles.value = roles.value.filter(r => r.id !== id)
        saveToStorage()
      }

      appStore.showNotification('角色刪除成功', 'success')
      return true
    } catch (e) {
      error.value = e.message || '刪除角色失敗'
      appStore.showNotification(error.value, 'error')
      return false
    } finally {
      loading.value = false
    }
  }

  // ==================== 工具函數 ====================

  /**
   * 生成員工編號
   */
  const generateEmployeeId = () => {
    const year = new Date().getFullYear().toString().slice(-2)
    const random = Math.floor(Math.random() * 10000).toString().padStart(4, '0')
    return `EMP${year}${random}`
  }

  /**
   * 取得部門列表
   */
  const fetchDepartments = async () => {
    if (USE_API) {
      departments.value = await staffApi.getDepartments()
    } else {
      departments.value = getDefaultDepartments()
    }
    return departments.value
  }

  /**
   * 匯出員工資料
   */
  const exportStaff = async (format = 'excel', ids = []) => {
    loading.value = true

    try {
      if (USE_API) {
        const blob = await staffApi.exportStaff({ format, ids })
        const url = URL.createObjectURL(blob)
        const a = document.createElement('a')
        a.href = url
        a.download = `員工資料_${new Date().toISOString().split('T')[0]}.${format === 'excel' ? 'xlsx' : 'pdf'}`
        a.click()
        URL.revokeObjectURL(url)
      } else {
        // LocalStorage 模式：匯出 JSON
        const data = ids.length > 0
          ? staffList.value.filter(s => ids.includes(s.id))
          : staffList.value

        const blob = new Blob([JSON.stringify(data, null, 2)], { type: 'application/json' })
        const url = URL.createObjectURL(blob)
        const a = document.createElement('a')
        a.href = url
        a.download = `員工資料_${new Date().toISOString().split('T')[0]}.json`
        a.click()
        URL.revokeObjectURL(url)
      }

      appStore.showNotification('匯出成功', 'success')
    } catch (e) {
      appStore.showNotification('匯出失敗', 'error')
    } finally {
      loading.value = false
    }
  }

  return {
    // 狀態
    staffList,
    currentStaff,
    roles,
    departments,
    loading,
    error,
    stats,

    // 員工操作
    fetchStaffList,
    fetchStaff,
    createStaff,
    updateStaff,
    deleteStaff,
    batchDeleteStaff,
    updateStaffStatus,
    exportStaff,

    // 角色操作
    fetchRoles,
    createRole,
    updateRole,
    deleteRole,

    // 其他
    fetchDepartments
  }
}
