/**
 * 認證 Store
 *
 * 管理使用者認證狀態、登入登出、權限檢查
 */

import { defineStore } from 'pinia'
import { ref, computed } from 'vue'

export const useAuthStore = defineStore('auth', () => {
  // ==========================================
  // State
  // ==========================================

  /**
   * 認證 Token
   */
  const token = ref(localStorage.getItem('auth_token') || null)

  /**
   * 使用者資訊
   */
  const user = ref(null)

  /**
   * 使用者權限列表
   */
  const permissions = ref([])

  /**
   * 使用者角色列表
   */
  const roles = ref([])

  // ==========================================
  // Getters
  // ==========================================

  /**
   * 是否已登入
   */
  const isAuthenticated = computed(() => !!token.value)

  /**
   * 是否為管理員
   */
  const isAdmin = computed(() => {
    return roles.value.includes('admin') || roles.value.includes('super_admin')
  })

  /**
   * 使用者顯示名稱
   */
  const displayName = computed(() => {
    return user.value?.name || user.value?.email || '訪客'
  })

  // ==========================================
  // Actions
  // ==========================================

  /**
   * 登入
   * @param {Object} credentials - 登入憑證
   * @param {string} credentials.email - Email
   * @param {string} credentials.password - 密碼
   * @returns {Promise<void>}
   */
  async function login(credentials) {
    try {
      // TODO: 實際 API 呼叫
      // const response = await authApi.login(credentials)

      // 暫時模擬登入
      const mockToken = 'mock_token_' + Date.now()
      const mockUser = {
        id: 1,
        name: '測試使用者',
        email: credentials.email,
        avatar: null
      }
      const mockPermissions = ['quote.view', 'quote.create', 'quote.edit', 'quote.delete']
      const mockRoles = ['user']

      setToken(mockToken)
      setUser(mockUser)
      setPermissions(mockPermissions)
      setRoles(mockRoles)

      return Promise.resolve()
    } catch (error) {
      console.error('[Auth] Login failed:', error)
      throw error
    }
  }

  /**
   * 登出
   */
  async function logout() {
    try {
      // TODO: 實際 API 呼叫
      // await authApi.logout()

      clearAuth()
    } catch (error) {
      console.error('[Auth] Logout failed:', error)
      // 即使 API 失敗，仍清除本地認證資訊
      clearAuth()
    }
  }

  /**
   * 取得目前使用者資訊
   */
  async function fetchUser() {
    try {
      // TODO: 實際 API 呼叫
      // const response = await authApi.me()
      // setUser(response.data)

      console.log('[Auth] User info fetched')
    } catch (error) {
      console.error('[Auth] Fetch user failed:', error)
      throw error
    }
  }

  /**
   * 刷新 Token
   */
  async function refreshToken() {
    try {
      // TODO: 實際 API 呼叫
      // const response = await authApi.refresh()
      // setToken(response.data.token)

      console.log('[Auth] Token refreshed')
    } catch (error) {
      console.error('[Auth] Refresh token failed:', error)
      throw error
    }
  }

  /**
   * 設定 Token
   * @param {string} newToken - 新 Token
   */
  function setToken(newToken) {
    token.value = newToken
    if (newToken) {
      localStorage.setItem('auth_token', newToken)
    } else {
      localStorage.removeItem('auth_token')
    }
  }

  /**
   * 設定使用者資訊
   * @param {Object} userData - 使用者資料
   */
  function setUser(userData) {
    user.value = userData
  }

  /**
   * 設定權限列表
   * @param {Array<string>} permissionList - 權限列表
   */
  function setPermissions(permissionList) {
    permissions.value = permissionList
  }

  /**
   * 設定角色列表
   * @param {Array<string>} roleList - 角色列表
   */
  function setRoles(roleList) {
    roles.value = roleList
  }

  /**
   * 清除認證資訊
   */
  function clearAuth() {
    setToken(null)
    setUser(null)
    setPermissions([])
    setRoles([])
  }

  /**
   * 檢查是否有指定權限
   * @param {string|Array<string>} permission - 權限（支援單一或多個）
   * @param {boolean} requireAll - 是否需要全部權限（預設 false，有任一即可）
   * @returns {boolean}
   */
  function hasPermission(permission, requireAll = false) {
    if (!permission) return true
    if (isAdmin.value) return true // 管理員擁有所有權限

    const permissionsToCheck = Array.isArray(permission) ? permission : [permission]

    if (requireAll) {
      return permissionsToCheck.every(p => permissions.value.includes(p))
    } else {
      return permissionsToCheck.some(p => permissions.value.includes(p))
    }
  }

  /**
   * 檢查是否有指定角色
   * @param {string|Array<string>} role - 角色（支援單一或多個）
   * @returns {boolean}
   */
  function hasRole(role) {
    if (!role) return true

    const rolesToCheck = Array.isArray(role) ? role : [role]
    return rolesToCheck.some(r => roles.value.includes(r))
  }

  // ==========================================
  // Initialization
  // ==========================================

  /**
   * 初始化（從 localStorage 恢復認證狀態）
   */
  function init() {
    if (token.value) {
      // TODO: 驗證 token 是否有效
      // 可以在這裡呼叫 fetchUser() 取得使用者資訊
      console.log('[Auth] Initialized with token')
    }
  }

  // 自動初始化
  init()

  // ==========================================
  // Return
  // ==========================================

  return {
    // State
    token,
    user,
    permissions,
    roles,

    // Getters
    isAuthenticated,
    isAdmin,
    displayName,

    // Actions
    login,
    logout,
    fetchUser,
    refreshToken,
    setToken,
    setUser,
    setPermissions,
    setRoles,
    clearAuth,
    hasPermission,
    hasRole
  }
})
