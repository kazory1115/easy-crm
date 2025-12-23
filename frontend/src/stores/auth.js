/**
 * 認證 Store
 *
 * 管理使用者認證狀態、登入登出、權限檢查
 */

import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import { post, get } from '@/utils/http'

export const useAuthStore = defineStore('auth', () => {
  // useRouter 必須在 setup 函式內呼叫
  const router = useRouter()

  // ==========================================
  // State
  // ==========================================

  const user = ref(JSON.parse(localStorage.getItem('auth_user')) || null)
  const token = ref(localStorage.getItem('auth_token') || null)
  const permissions = ref(JSON.parse(localStorage.getItem('auth_permissions')) || [])
  const roles = ref(JSON.parse(localStorage.getItem('auth_roles')) || [])

  // ==========================================
  // Getters
  // ==========================================

  const isLoggedIn = computed(() => !!token.value && !!user.value)
  const isAuthenticated = computed(() => isLoggedIn.value) // Alias for consistency

  const isAdmin = computed(() => {
    return roles.value.includes('admin') || roles.value.includes('super-admin') || roles.value.includes('super_admin')
  })

  const displayName = computed(() => {
    return user.value?.name || user.value?.email || '訪客'
  })

  // ==========================================
  // Actions
  // ==========================================

  async function login(credentials) {
    console.log('[Auth] Attempting login with:', credentials.email)

    try {
      // 呼叫真實 API
      const response = await post('/auth/login', {
        email: credentials.email,
        password: credentials.password,
        device_name: credentials.device_name || 'web-browser'
      })

      // API 回傳格式: { user, token, roles, permissions }
      const {
        user: userData,
        token: authToken,
        roles: roleList = [],
        permissions: permissionList = []
      } = response

      // 更新 store
      setToken(authToken)
      setUser(userData)
      setPermissions(Array.isArray(permissionList) ? permissionList : [])
      setRoles(Array.isArray(roleList) ? roleList : [])

      console.log('[Auth] Login successful')
      return Promise.resolve()
    } catch (error) {
      console.error('[Auth] Login failed:', error)
      clearAuth() // 登入失敗時清掉所有認證資料
      throw new Error(error.response?.data?.message || '登入失敗，請檢查帳號或密碼。')
    }
  }

  async function logout() {
    console.log('[Auth] Logging out')

    try {
      // 呼叫真實 API 登出（撤銷 token）
      await post('/auth/logout')
    } catch (error) {
      console.error('[Auth] Logout API failed:', error)
      // 即使 API 失敗，也要清掉本地狀態
    }

    clearAuth()
    // 使用 router 進行導向
    // 確保在客戶端環境下執行
    if (typeof window !== 'undefined') {
      router.push('/login')
    }
  }

  function setToken(newToken) {
    token.value = newToken
    if (newToken) {
      localStorage.setItem('auth_token', newToken)
    } else {
      localStorage.removeItem('auth_token')
    }
  }

  function setUser(userData) {
    user.value = userData
    if (userData) {
      localStorage.setItem('auth_user', JSON.stringify(userData))
    } else {
      localStorage.removeItem('auth_user')
    }
  }

  function setPermissions(permissionList) {
    permissions.value = permissionList
    if (permissionList && permissionList.length > 0) {
      localStorage.setItem('auth_permissions', JSON.stringify(permissionList))
    } else {
      localStorage.removeItem('auth_permissions')
    }
  }

  function setRoles(roleList) {
    roles.value = roleList
    if (roleList && roleList.length > 0) {
      localStorage.setItem('auth_roles', JSON.stringify(roleList))
    } else {
      localStorage.removeItem('auth_roles')
    }
  }

  function clearAuth() {
    setToken(null)
    setUser(null)
    setPermissions([])
    setRoles([])
  }

  function hasPermission(permission, requireAll = false) {
    if (!permission) return true
    if (isAdmin.value) return true

    const permissionsToCheck = Array.isArray(permission) ? permission : [permission]

    if (requireAll) {
      return permissionsToCheck.every(p => permissions.value.includes(p))
    } else {
      return permissionsToCheck.some(p => permissions.value.includes(p))
    }
  }

  function hasRole(role) {
    if (!role) return true
    const rolesToCheck = Array.isArray(role) ? role : [role]
    return rolesToCheck.some(r => roles.value.includes(r))
  }

  function checkAuthOnAppStart() {
    console.log('[Auth] Checking auth status on app start')
    if (!isLoggedIn.value) {
      console.log('[Auth] No valid session found.')
      clearAuth()
    } else {
      console.log('[Auth] Session restored for user:', user.value.email)
      // 可選：此處呼叫 /auth/me 重新同步權限
    }
  }

  // ==========================================
  // Initialization
  // ==========================================
  checkAuthOnAppStart()

  // ==========================================
  // Return
  // ==========================================
  return {
    user,
    token,
    permissions,
    roles,
    isLoggedIn,
    isAuthenticated,
    isAdmin,
    displayName,
    login,
    logout,
    clearAuth,
    hasPermission,
    hasRole,
    checkAuthOnAppStart
  }
})
