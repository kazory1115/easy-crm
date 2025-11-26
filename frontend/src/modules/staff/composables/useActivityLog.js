/**
 * 操作紀錄 Composable
 *
 * 提供操作紀錄的查詢與統計功能
 */

import { ref, computed } from 'vue'
import { useAppStore } from '@/stores/app'
import * as logApi from '../api/logApi'

/**
 * 操作紀錄 Composable
 */
export function useActivityLog() {
  const appStore = useAppStore()

  // 狀態
  const logs = ref([])
  const currentLog = ref(null)
  const loading = ref(false)
  const error = ref(null)
  const pagination = ref({
    current_page: 1,
    per_page: 20,
    total: 0,
    last_page: 1
  })

  // 統計資料
  const stats = ref({
    byEvent: {},
    byModule: {},
    total: 0
  })

  // ==================== 操作紀錄查詢 ====================

  /**
   * 取得操作紀錄列表
   */
  const fetchActivityLogs = async (params = {}) => {
    loading.value = true
    error.value = null

    try {
      const response = await logApi.getActivityLogs(params)

      // 後端回應格式: { data: [...], meta: { current_page, per_page, total, last_page } }
      logs.value = response.data || []

      if (response.meta) {
        pagination.value = response.meta
      }

      return response
    } catch (e) {
      error.value = e.message || '載入操作紀錄失敗'
      appStore.showNotification(error.value, 'error')
      return null
    } finally {
      loading.value = false
    }
  }

  /**
   * 取得單一操作紀錄詳情
   */
  const fetchActivityLog = async (id) => {
    loading.value = true
    error.value = null

    try {
      const response = await logApi.getActivityLog(id)
      currentLog.value = response.data || response
      return currentLog.value
    } catch (e) {
      error.value = e.message || '載入紀錄詳情失敗'
      appStore.showNotification(error.value, 'error')
      return null
    } finally {
      loading.value = false
    }
  }

  /**
   * 取得我的操作紀錄
   */
  const fetchMyActivityLogs = async (params = {}) => {
    loading.value = true
    error.value = null

    try {
      const response = await logApi.getMyActivityLogs(params)
      logs.value = response.data || []

      if (response.meta) {
        pagination.value = response.meta
      }

      return response
    } catch (e) {
      error.value = e.message || '載入我的操作紀錄失敗'
      appStore.showNotification(error.value, 'error')
      return null
    } finally {
      loading.value = false
    }
  }

  /**
   * 取得特定模組的操作紀錄
   */
  const fetchModuleActivityLogs = async (module, params = {}) => {
    loading.value = true
    error.value = null

    try {
      const response = await logApi.getModuleActivityLogs(module, params)
      logs.value = response.data || []

      if (response.meta) {
        pagination.value = response.meta
      }

      return response
    } catch (e) {
      error.value = e.message || '載入模組操作紀錄失敗'
      appStore.showNotification(error.value, 'error')
      return null
    } finally {
      loading.value = false
    }
  }

  /**
   * 取得操作紀錄統計資料
   */
  const fetchActivityLogStats = async (params = {}) => {
    loading.value = true
    error.value = null

    try {
      const response = await logApi.getActivityLogStats(params)
      stats.value = response.data || response
      return stats.value
    } catch (e) {
      error.value = e.message || '載入統計資料失敗'
      appStore.showNotification(error.value, 'error')
      return null
    } finally {
      loading.value = false
    }
  }

  // ==================== 工具函數 ====================

  /**
   * 取得事件類型的顯示文字
   */
  const getEventText = (event) => {
    const map = {
      created: '新增',
      updated: '更新',
      deleted: '刪除',
      viewed: '查看',
      exported: '匯出',
      imported: '匯入'
    }
    return map[event] || event
  }

  /**
   * 取得事件類型的樣式 class
   */
  const getEventClass = (event) => {
    const base = 'px-2 py-1 text-xs rounded-full'
    const map = {
      created: 'bg-green-100 text-green-800',
      updated: 'bg-blue-100 text-blue-800',
      deleted: 'bg-red-100 text-red-800',
      viewed: 'bg-gray-100 text-gray-800',
      exported: 'bg-purple-100 text-purple-800',
      imported: 'bg-yellow-100 text-yellow-800'
    }
    return `${base} ${map[event] || 'bg-gray-100 text-gray-800'}`
  }

  /**
   * 取得模組的顯示文字
   */
  const getModuleText = (module) => {
    const map = {
      quote: '報價單',
      template: '範本',
      customer: '客戶',
      user: '員工',
      item: '項目',
      role: '角色',
      crm: 'CRM',
      inventory: '庫存',
      report: '報表'
    }
    return map[module] || module
  }

  /**
   * 格式化日期時間
   */
  const formatDateTime = (dateString) => {
    if (!dateString) return '-'
    const date = new Date(dateString)
    return date.toLocaleString('zh-TW', {
      year: 'numeric',
      month: '2-digit',
      day: '2-digit',
      hour: '2-digit',
      minute: '2-digit',
      second: '2-digit'
    })
  }

  /**
   * 重置狀態
   */
  const reset = () => {
    logs.value = []
    currentLog.value = null
    loading.value = false
    error.value = null
    pagination.value = {
      current_page: 1,
      per_page: 20,
      total: 0,
      last_page: 1
    }
  }

  return {
    // 狀態
    logs,
    currentLog,
    loading,
    error,
    pagination,
    stats,

    // 方法
    fetchActivityLogs,
    fetchActivityLog,
    fetchMyActivityLogs,
    fetchModuleActivityLogs,
    fetchActivityLogStats,

    // 工具函數
    getEventText,
    getEventClass,
    getModuleText,
    formatDateTime,
    reset
  }
}
