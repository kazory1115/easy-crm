/**
 * 應用程式全域 Store
 *
 * 管理應用程式狀態、模組配置、側邊欄等
 */

import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { getModules, getEnabledModules, isModuleEnabled } from '@/config/modules'

export const useAppStore = defineStore('app', () => {
  // ==========================================
  // State
  // ==========================================

  /**
   * 側邊欄展開狀態
   */
  const sidebarCollapsed = ref(false)

  /**
   * 載入狀態
   */
  const loading = ref(false)

  /**
   * 全域通知列表
   */
  const notifications = ref([])

  /**
   * 麵包屑導航
   */
  const breadcrumbs = ref([])

  /**
   * 當前啟用的模組 ID
   */
  const currentModule = ref(null)

  // ==========================================
  // Getters
  // ==========================================

  /**
   * 取得所有模組（含未啟用）
   */
  const allModules = computed(() => getModules(false))

  /**
   * 取得已啟用的模組
   */
  const enabledModules = computed(() => getModules(true))

  /**
   * 取得側邊欄選單項目（僅已啟用模組）
   */
  const sidebarMenuItems = computed(() => {
    return enabledModules.value.map(module => ({
      id: module.id,
      name: module.name,
      icon: module.icon,
      path: module.path,
      badge: module.meta?.badge,
      color: module.meta?.color,
      children: module.children || null
    }))
  })

  /**
   * 檢查特定模組是否啟用
   */
  const checkModuleEnabled = computed(() => {
    return (moduleId) => isModuleEnabled(moduleId)
  })

  // ==========================================
  // Actions
  // ==========================================

  /**
   * 切換側邊欄展開/收合
   */
  function toggleSidebar() {
    sidebarCollapsed.value = !sidebarCollapsed.value
  }

  /**
   * 設定側邊欄狀態
   * @param {boolean} collapsed - 是否收合
   */
  function setSidebarCollapsed(collapsed) {
    sidebarCollapsed.value = collapsed
  }

  /**
   * 設定載入狀態
   * @param {boolean} status - 載入狀態
   */
  function setLoading(status) {
    loading.value = status
  }

  /**
   * 新增通知
   * @param {Object} notification - 通知物件
   * @param {string} notification.type - 類型 (success, error, warning, info)
   * @param {string} notification.message - 訊息
   * @param {number} [notification.duration=3000] - 顯示時長（毫秒）
   */
  function addNotification(notification) {
    const id = Date.now()
    const duration = notification.duration || 3000

    notifications.value.push({
      id,
      ...notification
    })

    // 自動移除
    if (duration > 0) {
      setTimeout(() => {
        removeNotification(id)
      }, duration)
    }
  }

  /**
   * 移除通知
   * @param {number} id - 通知 ID
   */
  function removeNotification(id) {
    const index = notifications.value.findIndex(n => n.id === id)
    if (index !== -1) {
      notifications.value.splice(index, 1)
    }
  }

  /**
   * 設定麵包屑
   * @param {Array} items - 麵包屑項目
   */
  function setBreadcrumbs(items) {
    breadcrumbs.value = items
  }

  /**
   * 設定當前模組
   * @param {string} moduleId - 模組 ID
   */
  function setCurrentModule(moduleId) {
    currentModule.value = moduleId
  }

  /**
   * 顯示成功訊息
   * @param {string} message - 訊息
   */
  function showSuccess(message) {
    addNotification({
      type: 'success',
      message
    })
  }

  /**
   * 顯示錯誤訊息
   * @param {string} message - 訊息
   */
  function showError(message) {
    addNotification({
      type: 'error',
      message,
      duration: 5000
    })
  }

  /**
   * 顯示警告訊息
   * @param {string} message - 訊息
   */
  function showWarning(message) {
    addNotification({
      type: 'warning',
      message
    })
  }

  /**
   * 顯示資訊訊息
   * @param {string} message - 訊息
   */
  function showInfo(message) {
    addNotification({
      type: 'info',
      message
    })
  }

  /**
   * 顯示通知（addNotification 的別名）
   * @param {Object} notification - 通知物件
   */
  function showNotification(notification) {
    addNotification(notification)
  }

  // ==========================================
  // Return
  // ==========================================

  return {
    // State
    sidebarCollapsed,
    loading,
    notifications,
    breadcrumbs,
    currentModule,

    // Getters
    allModules,
    enabledModules,
    sidebarMenuItems,
    checkModuleEnabled,

    // Actions
    toggleSidebar,
    setSidebarCollapsed,
    setLoading,
    addNotification,
    showNotification,
    removeNotification,
    setBreadcrumbs,
    setCurrentModule,
    showSuccess,
    showError,
    showWarning,
    showInfo
  }
})
