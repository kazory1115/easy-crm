/**
 * 員工管理 Composable（API-only）
 */

import { ref, computed } from 'vue'
import { useAppStore } from '@/stores/app'
import * as staffApi from '../api/staffApi'

function getRowsFromResponse(response) {
  if (Array.isArray(response)) return response
  if (Array.isArray(response?.data)) return response.data
  if (Array.isArray(response?.data?.data)) return response.data.data
  return []
}

function getItemFromResponse(response) {
  if (response?.data?.id) return response.data
  return response
}

function normalizeStaff(staff = {}) {
  const serial = staff.id == null ? '0000' : String(staff.id).padStart(4, '0')

  return {
    ...staff,
    employeeId: staff.employeeId || staff.employee_id || `EMP${serial}`,
    department: staff.department || '',
    position: staff.position || '-'
  }
}

function buildDepartments(list = []) {
  const names = Array.from(
    new Set(
      list
        .map((staff) => (staff.department || '').trim())
        .filter((name) => name.length > 0)
    )
  )

  return names.map((name) => ({ id: name, name }))
}

export function useStaff() {
  const appStore = useAppStore()

  const staffList = ref([])
  const currentStaff = ref(null)
  const departments = ref([])
  const loading = ref(false)
  const error = ref(null)

  const stats = computed(() => {
    const list = staffList.value

    return {
      total: list.length,
      byDepartment: list.reduce((accumulator, staff) => {
        const department = staff.department || '未分類'
        accumulator[department] = (accumulator[department] || 0) + 1
        return accumulator
      }, {})
    }
  })

  const fetchStaffList = async (params = {}) => {
    loading.value = true
    error.value = null

    try {
      const response = await staffApi.getStaffList(params)
      const rows = getRowsFromResponse(response)
      const normalizedRows = rows.map(normalizeStaff)

      const filteredRows = normalizedRows.filter((staff) => {
        const departmentMatch = !params.department || staff.department === params.department
        return departmentMatch
      })

      staffList.value = filteredRows
      departments.value = buildDepartments(normalizedRows)
      return staffList.value
    } catch (err) {
      error.value = err.message || '載入員工列表失敗'
      appStore.showError(error.value)
      return []
    } finally {
      loading.value = false
    }
  }

  const fetchStaff = async (id) => {
    loading.value = true
    error.value = null

    try {
      const response = await staffApi.getStaff(id)
      currentStaff.value = normalizeStaff(getItemFromResponse(response))
      return currentStaff.value
    } catch (err) {
      error.value = err.message || '載入員工資料失敗'
      appStore.showError(error.value)
      return null
    } finally {
      loading.value = false
    }
  }

  const createStaff = async (payload) => {
    loading.value = true
    error.value = null

    try {
      const response = await staffApi.createStaff(payload)
      appStore.showSuccess('員工新增成功')
      return normalizeStaff(getItemFromResponse(response))
    } catch (err) {
      error.value = err.message || '新增員工失敗'
      appStore.showError(error.value)
      return null
    } finally {
      loading.value = false
    }
  }

  const updateStaff = async (id, payload) => {
    loading.value = true
    error.value = null

    try {
      const response = await staffApi.updateStaff(id, payload)
      appStore.showSuccess('員工資料更新成功')
      return normalizeStaff(getItemFromResponse(response))
    } catch (err) {
      error.value = err.message || '更新員工失敗'
      appStore.showError(error.value)
      return null
    } finally {
      loading.value = false
    }
  }

  const deleteStaff = async (id) => {
    loading.value = true
    error.value = null

    try {
      await staffApi.deleteStaff(id)
      appStore.showSuccess('員工刪除成功')
      return true
    } catch (err) {
      error.value = err.message || '刪除員工失敗'
      appStore.showError(error.value)
      return false
    } finally {
      loading.value = false
    }
  }

  return {
    staffList,
    currentStaff,
    departments,
    loading,
    error,
    stats,
    fetchStaffList,
    fetchStaff,
    createStaff,
    updateStaff,
    deleteStaff
  }
}
