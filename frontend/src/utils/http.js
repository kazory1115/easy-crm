import axios from 'axios'
import { useAuthStore } from '@/stores/auth'
import { useAppStore } from '@/stores/app'

// API base URL
const BASE_URL = import.meta.env.VITE_API_BASE_URL || '/api'

// Axios instance
const http = axios.create({
  baseURL: BASE_URL,
  timeout: 30000,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  }
})

// Request interceptor
http.interceptors.request.use(
  (config) => {
    const authStore = useAuthStore()
    if (authStore.token) {
      config.headers.Authorization = `Bearer ${authStore.token}`
    }

    const appStore = useAppStore()
    appStore.setLoading(true)

    console.log(`[HTTP] >>${config.method.toUpperCase()} ${config.url}`, config.data || '')

    return config
  },
  (error) => {
    console.error('[HTTP] Request error:', error)
    return Promise.reject(error)
  }
)

// Response interceptor
http.interceptors.response.use(
  (response) => {
    const appStore = useAppStore()
    appStore.setLoading(false)

    console.log(`[HTTP] <<${response.status} ${response.config.url}`, response.data)

    return response.data
  },
  (error) => {
    const appStore = useAppStore()
    appStore.setLoading(false)

    console.error('[HTTP] Response error:', error)

    handleError(error)

    return Promise.reject(error)
  }
)

// Centralized error handler
function handleError(error) {
  const appStore = useAppStore()
  const authStore = useAuthStore()

  if (!error.response) {
    appStore.showError('Network request failed. Please check your connection.')
    return
  }

  const { status, data } = error.response

  switch (status) {
    case 400:
      appStore.showError(data.message || 'Bad request')
      break

    case 401:
      appStore.showError('Unauthorized. Please login again.')
      authStore.clearAuth()
      window.location.href = '/login'
      break

    case 403:
      appStore.showError('Forbidden')
      break

    case 404:
      appStore.showError('Not found')
      break

    case 422: {
      const errors = data.errors || {}
      const firstError = Object.values(errors)[0]
      const message = Array.isArray(firstError) ? firstError[0] : 'Validation failed'
      appStore.showError(message)
      break
    }

    case 500:
      appStore.showError('Server error')
      break

    case 503:
      appStore.showError('Service unavailable')
      break

    default:
      appStore.showError(data.message || 'Unknown error')
  }
}

// HTTP helpers
export function get(url, params = {}, config = {}) {
  return http.get(url, { params, ...config })
}

export function post(url, data = {}, config = {}) {
  return http.post(url, data, config)
}

export function put(url, data = {}, config = {}) {
  return http.put(url, data, config)
}

export function patch(url, data = {}, config = {}) {
  return http.patch(url, data, config)
}

export function del(url, config = {}) {
  return http.delete(url, config)
}

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

export default http
