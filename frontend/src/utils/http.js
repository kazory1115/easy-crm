/**
 * HTTP 客戶端配置
 *
 * 基於 Axios 封裝，統一處理請求/回應
 */

import axios from 'axios'
import { useAuthStore } from '@/stores/auth'
import { useAppStore } from '@/stores/app'

/**
 * API 基礎 URL
 */
const BASE_URL = import.meta.env.VITE_API_BASE_URL || 'http://localhost:8180/api'

/**
 * 建立 Axios 實例
 */
const http = axios.create({
  baseURL: BASE_URL,
  timeout: 30000,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  }
})

/**
 * 請求攔截器
 */
http.interceptors.request.use(
  (config) => {
    // 加入 Auth Token
    const authStore = useAuthStore()
    if (authStore.token) {
      config.headers.Authorization = `Bearer ${authStore.token}`
    }

    // 顯示載入狀態
    const appStore = useAppStore()
    appStore.setLoading(true)

    console.log(`[HTTP] → ${config.method.toUpperCase()} ${config.url}`, config.data || '')

    return config
  },
  (error) => {
    console.error('[HTTP] Request error:', error)
    return Promise.reject(error)
  }
)

/**
 * 回應攔截器
 */
http.interceptors.response.use(
  (response) => {
    // 隱藏載入狀態
    const appStore = useAppStore()
    appStore.setLoading(false)

    console.log(`[HTTP] ← ${response.status} ${response.config.url}`, response.data)

    // 返回資料
    return response.data
  },
  (error) => {
    // 隱藏載入狀態
    const appStore = useAppStore()
    appStore.setLoading(false)

    console.error('[HTTP] Response error:', error)

    // 處理錯誤
    handleError(error)

    return Promise.reject(error)
  }
)

/**
 * 統一錯誤處理
 * @param {Error} error - 錯誤物件
 */
function handleError(error) {
  const appStore = useAppStore()
  const authStore = useAuthStore()

  if (!error.response) {
    // 網路錯誤
    appStore.showError('網路連線失敗，請檢查網路狀態')
    return
  }

  const { status, data } = error.response

  switch (status) {
    case 400:
      // Bad Request
      appStore.showError(data.message || '請求參數錯誤')
      break

    case 401:
      // Unauthorized
      appStore.showError('未授權，請重新登入')
      authStore.clearAuth()
      // 導向登入頁
      window.location.href = '/login'
      break

    case 403:
      // Forbidden
      appStore.showError('沒有權限執行此操作')
      break

    case 404:
      // Not Found
      appStore.showError('請求的資源不存在')
      break

    case 422:
      // Validation Error
      const errors = data.errors || {}
      const firstError = Object.values(errors)[0]
      const message = Array.isArray(firstError) ? firstError[0] : '資料驗證失敗'
      appStore.showError(message)
      break

    case 500:
      // Server Error
      appStore.showError('伺服器錯誤，請稍後再試')
      break

    case 503:
      // Service Unavailable
      appStore.showError('服務暫時無法使用，請稍後再試')
      break

    default:
      appStore.showError(data.message || '發生未知錯誤')
  }
}

/**
 * GET 請求
 * @param {string} url - 請求路徑
 * @param {Object} params - 查詢參數
 * @param {Object} config - Axios 配置
 * @returns {Promise}
 */
export function get(url, params = {}, config = {}) {
  return http.get(url, { params, ...config })
}

/**
 * POST 請求
 * @param {string} url - 請求路徑
 * @param {Object} data - 請求資料
 * @param {Object} config - Axios 配置
 * @returns {Promise}
 */
export function post(url, data = {}, config = {}) {
  return http.post(url, data, config)
}

/**
 * PUT 請求
 * @param {string} url - 請求路徑
 * @param {Object} data - 請求資料
 * @param {Object} config - Axios 配置
 * @returns {Promise}
 */
export function put(url, data = {}, config = {}) {
  return http.put(url, data, config)
}

/**
 * PATCH 請求
 * @param {string} url - 請求路徑
 * @param {Object} data - 請求資料
 * @param {Object} config - Axios 配置
 * @returns {Promise}
 */
export function patch(url, data = {}, config = {}) {
  return http.patch(url, data, config)
}

/**
 * DELETE 請求
 * @param {string} url - 請求路徑
 * @param {Object} config - Axios 配置
 * @returns {Promise}
 */
export function del(url, config = {}) {
  return http.delete(url, config)
}

/**
 * 上傳檔案
 * @param {string} url - 請求路徑
 * @param {FormData} formData - 表單資料
 * @param {Function} onProgress - 上傳進度回調
 * @returns {Promise}
 */
export function upload(url, formData, onProgress) {
  return http.post(url, formData, {
    headers: {
      'Content-Type': 'multipart/form-data'
    },
    onUploadProgress: (progressEvent) => {
      if (onProgress) {
        const percentCompleted = Math.round(
          (progressEvent.loaded * 100) / progressEvent.total
        )
        onProgress(percentCompleted)
      }
    }
  })
}

/**
 * 下載檔案
 * @param {string} url - 請求路徑
 * @param {string} filename - 檔案名稱
 * @param {Object} params - 查詢參數
 * @returns {Promise}
 */
export function download(url, filename, params = {}) {
  return http.get(url, {
    params,
    responseType: 'blob'
  }).then((blob) => {
    const link = document.createElement('a')
    link.href = window.URL.createObjectURL(blob)
    link.download = filename
    link.click()
    window.URL.revokeObjectURL(link.href)
  })
}

// 導出預設實例
export default http
