/**
 * 報價單 Composable
 *
 * 支援兩種模式：
 * 1. LocalStorage 模式（離線，目前使用）
 * 2. API 模式（在線，後端整合完成後使用）
 */

import { ref, computed } from 'vue'
import { useAppStore } from '@/stores/app'
import * as quoteApi from '../api/quoteApi'

// ==========================================
// 資料來源模式
// ==========================================

/**
 * 資料來源類型
 * - 'localStorage': 使用 LocalStorage（離線模式）
 * - 'api': 使用後端 API（在線模式）
 */
const dataSource = ref('api') // 切換到 API 模式

// LocalStorage 鍵名
const STORAGE_KEYS = {
  QUOTES: 'quote_quotes',
  TEMPLATES: 'quote_templates',
  ITEMS: 'quote_items'
}

// ==========================================
// LocalStorage 操作
// ==========================================

/**
 * 從 LocalStorage 取得資料
 * @param {string} key - Storage 鍵名
 * @returns {Array} 資料陣列
 */
function getFromStorage(key) {
  const data = localStorage.getItem(key)
  return data ? JSON.parse(data) : []
}

/**
 * 儲存資料到 LocalStorage
 * @param {string} key - Storage 鍵名
 * @param {Array} data - 資料陣列
 */
function saveToStorage(key, data) {
  localStorage.setItem(key, JSON.stringify(data))
}

/**
 * 生成唯一 ID（LocalStorage 模式使用）
 * @param {Array} items - 項目列表
 * @returns {number} 新 ID
 */
function generateId(items) {
  if (items.length === 0) return 1
  return Math.max(...items.map(item => item.id || 0)) + 1
}

// ==========================================
// 報價單 Composable
// ==========================================

export function useQuote() {
  const appStore = useAppStore()

  // State
  const quotes = ref([])
  const currentQuote = ref(null)
  const loading = ref(false)
  const error = ref(null)

  // ==========================================
  // 報價單 CRUD
  // ==========================================

  /**
   * 取得報價單列表
   * @param {Object} params - 查詢參數
   * @returns {Promise<Array>}
   */
  async function fetchQuotes(params = {}) {
    loading.value = true
    error.value = null

    try {
      if (dataSource.value === 'localStorage') {
        // LocalStorage 模式
        quotes.value = getFromStorage(STORAGE_KEYS.QUOTES)

        // 簡單的篩選與排序
        let result = [...quotes.value]

        // 搜尋
        if (params.search) {
          const searchLower = params.search.toLowerCase()
          result = result.filter(quote =>
            quote.customerName?.toLowerCase().includes(searchLower) ||
            quote.projectName?.toLowerCase().includes(searchLower)
          )
        }

        // 狀態篩選
        if (params.status) {
          result = result.filter(quote => quote.status === params.status)
        }

        // 排序
        if (params.sort_by) {
          const sortField = params.sort_by
          const sortOrder = params.sort_order === 'desc' ? -1 : 1
          result.sort((a, b) => {
            if (a[sortField] < b[sortField]) return -sortOrder
            if (a[sortField] > b[sortField]) return sortOrder
            return 0
          })
        }

        quotes.value = result
        return result
      } else {
        // API 模式
        const response = await quoteApi.getQuotes(params)
        quotes.value = response.data || []
        return quotes.value
      }
    } catch (err) {
      error.value = err
      appStore.showError('取得報價單列表失敗')
      throw err
    } finally {
      loading.value = false
    }
  }

  /**
   * 取得單一報價單
   * @param {number} id - 報價單 ID
   * @returns {Promise<Object>}
   */
  async function fetchQuote(id) {
    loading.value = true
    error.value = null

    try {
      if (dataSource.value === 'localStorage') {
        // LocalStorage 模式
        const allQuotes = getFromStorage(STORAGE_KEYS.QUOTES)
        const quote = allQuotes.find(q => q.id === id)
        if (!quote) {
          throw new Error('報價單不存在')
        }
        currentQuote.value = quote
        return quote
      } else {
        // API 模式
        const response = await quoteApi.getQuote(id)
        currentQuote.value = response.data
        return currentQuote.value
      }
    } catch (err) {
      error.value = err
      appStore.showError('取得報價單失敗')
      throw err
    } finally {
      loading.value = false
    }
  }

  /**
   * 建立報價單
   * @param {Object} quoteData - 報價單資料
   * @returns {Promise<Object>}
   */
  async function createQuote(quoteData) {
    loading.value = true
    error.value = null

    try {
      if (dataSource.value === 'localStorage') {
        // LocalStorage 模式
        const allQuotes = getFromStorage(STORAGE_KEYS.QUOTES)
        const newQuote = {
          id: generateId(allQuotes),
          ...quoteData,
          createdAt: new Date().toISOString(),
          updatedAt: new Date().toISOString()
        }
        allQuotes.push(newQuote)
        saveToStorage(STORAGE_KEYS.QUOTES, allQuotes)
        appStore.showSuccess('報價單建立成功')
        return newQuote
      } else {
        // API 模式
        const response = await quoteApi.createQuote(quoteData)
        appStore.showSuccess('報價單建立成功')
        return response.data
      }
    } catch (err) {
      error.value = err
      appStore.showError('報價單建立失敗')
      throw err
    } finally {
      loading.value = false
    }
  }

  /**
   * 更新報價單
   * @param {number} id - 報價單 ID
   * @param {Object} updates - 更新資料
   * @returns {Promise<Object>}
   */
  async function updateQuote(id, updates) {
    loading.value = true
    error.value = null

    try {
      if (dataSource.value === 'localStorage') {
        // LocalStorage 模式
        const allQuotes = getFromStorage(STORAGE_KEYS.QUOTES)
        const index = allQuotes.findIndex(q => q.id === id)
        if (index === -1) {
          throw new Error('報價單不存在')
        }
        allQuotes[index] = {
          ...allQuotes[index],
          ...updates,
          updatedAt: new Date().toISOString()
        }
        saveToStorage(STORAGE_KEYS.QUOTES, allQuotes)
        appStore.showSuccess('報價單更新成功')
        return allQuotes[index]
      } else {
        // API 模式
        const response = await quoteApi.updateQuote(id, updates)
        appStore.showSuccess('報價單更新成功')
        return response.data
      }
    } catch (err) {
      error.value = err
      appStore.showError('報價單更新失敗')
      throw err
    } finally {
      loading.value = false
    }
  }

  /**
   * 刪除報價單
   * @param {number} id - 報價單 ID
   * @returns {Promise<void>}
   */
  async function deleteQuote(id) {
    loading.value = true
    error.value = null

    try {
      if (dataSource.value === 'localStorage') {
        // LocalStorage 模式
        const allQuotes = getFromStorage(STORAGE_KEYS.QUOTES)
        const filtered = allQuotes.filter(q => q.id !== id)
        saveToStorage(STORAGE_KEYS.QUOTES, filtered)
        appStore.showSuccess('報價單刪除成功')
      } else {
        // API 模式
        await quoteApi.deleteQuote(id)
        appStore.showSuccess('報價單刪除成功')
      }
    } catch (err) {
      error.value = err
      appStore.showError('報價單刪除失敗')
      throw err
    } finally {
      loading.value = false
    }
  }

  // ==========================================
  // 工具函式
  // ==========================================

  /**
   * 切換資料來源模式
   * @param {string} mode - 模式 ('localStorage' | 'api')
   */
  function setDataSource(mode) {
    if (['localStorage', 'api'].includes(mode)) {
      dataSource.value = mode
      console.log(`[useQuote] Data source changed to: ${mode}`)
    }
  }

  /**
   * 取得目前資料來源模式
   * @returns {string}
   */
  function getDataSource() {
    return dataSource.value
  }

  /**
   * 重置狀態
   */
  function reset() {
    quotes.value = []
    currentQuote.value = null
    loading.value = false
    error.value = null
  }

  // ==========================================
  // Return
  // ==========================================

  return {
    // State
    quotes,
    currentQuote,
    loading,
    error,

    // Methods
    fetchQuotes,
    fetchQuote,
    createQuote,
    updateQuote,
    deleteQuote,
    setDataSource,
    getDataSource,
    reset
  }
}

/**
 * 範本 Composable
 */
export function useTemplate() {
  const appStore = useAppStore()

  // State
  const templates = ref([])
  const currentTemplate = ref(null)
  const loading = ref(false)
  const error = ref(null)

  /**
   * 取得範本列表
   */
  async function fetchTemplates(params = {}) {
    loading.value = true
    error.value = null

    try {
      if (dataSource.value === 'localStorage') {
        templates.value = getFromStorage(STORAGE_KEYS.TEMPLATES)
        return templates.value
      } else {
        const response = await quoteApi.getTemplates(params)
        templates.value = response.data || []
        return templates.value
      }
    } catch (err) {
      error.value = err
      appStore.showError('取得範本列表失敗')
      throw err
    } finally {
      loading.value = false
    }
  }

  /**
   * 建立範本
   */
  async function createTemplate(templateData) {
    loading.value = true
    error.value = null

    try {
      if (dataSource.value === 'localStorage') {
        const allTemplates = getFromStorage(STORAGE_KEYS.TEMPLATES)
        const newTemplate = {
          id: crypto.randomUUID(),
          ...templateData,
          createdAt: new Date().toISOString()
        }
        allTemplates.push(newTemplate)
        saveToStorage(STORAGE_KEYS.TEMPLATES, allTemplates)
        appStore.showSuccess('範本建立成功')
        return newTemplate
      } else {
        const response = await quoteApi.createTemplate(templateData)
        appStore.showSuccess('範本建立成功')
        return response.data
      }
    } catch (err) {
      error.value = err
      appStore.showError('範本建立失敗')
      throw err
    } finally {
      loading.value = false
    }
  }

  /**
   * 更新範本
   */
  async function updateTemplate(id, updates) {
    loading.value = true
    error.value = null

    try {
      if (dataSource.value === 'localStorage') {
        const allTemplates = getFromStorage(STORAGE_KEYS.TEMPLATES)
        const index = allTemplates.findIndex(t => t.id === id)
        if (index === -1) throw new Error('範本不存在')

        allTemplates[index] = {
          ...allTemplates[index],
          ...updates,
          updatedAt: new Date().toISOString()
        }
        saveToStorage(STORAGE_KEYS.TEMPLATES, allTemplates)
        appStore.showSuccess('範本更新成功')
        return allTemplates[index]
      } else {
        const response = await quoteApi.updateTemplate(id, updates)
        appStore.showSuccess('範本更新成功')
        return response.data
      }
    } catch (err) {
      error.value = err
      appStore.showError('範本更新失敗')
      throw err
    } finally {
      loading.value = false
    }
  }

  /**
   * 刪除範本
   */
  async function deleteTemplate(id) {
    loading.value = true
    error.value = null

    try {
      if (dataSource.value === 'localStorage') {
        const allTemplates = getFromStorage(STORAGE_KEYS.TEMPLATES)
        const filtered = allTemplates.filter(t => t.id !== id)
        saveToStorage(STORAGE_KEYS.TEMPLATES, filtered)
        appStore.showSuccess('範本刪除成功')
      } else {
        await quoteApi.deleteTemplate(id)
        appStore.showSuccess('範本刪除成功')
      }
    } catch (err) {
      error.value = err
      appStore.showError('範本刪除失敗')
      throw err
    } finally {
      loading.value = false
    }
  }

  return {
    templates,
    currentTemplate,
    loading,
    error,
    fetchTemplates,
    createTemplate,
    updateTemplate,
    deleteTemplate
  }
}
