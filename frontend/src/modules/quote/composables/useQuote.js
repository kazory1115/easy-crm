import { ref } from 'vue'
import { useAppStore } from '@/stores/app'
import * as quoteApi from '../api/quoteApi'

function extractErrorMessage(error, fallbackMessage) {
  return error?.response?.data?.error
    || error?.response?.data?.message
    || error?.message
    || fallbackMessage
}

function extractQuoteRows(response) {
  if (Array.isArray(response)) return response
  if (Array.isArray(response?.data)) return response.data
  if (Array.isArray(response?.data?.data)) return response.data.data
  return []
}

function extractQuoteRecord(response) {
  if (response?.data?.id) return response.data
  if (response?.id) return response
  return null
}

export function useQuote() {
  const appStore = useAppStore()

  const quotes = ref([])
  const currentQuote = ref(null)
  const loading = ref(false)
  const submitting = ref(false)
  const error = ref(null)

  async function fetchQuotes(params = {}) {
    loading.value = true
    error.value = null

    try {
      const response = await quoteApi.getQuotes(params)
      quotes.value = extractQuoteRows(response)
      return quotes.value
    } catch (err) {
      error.value = extractErrorMessage(err, '載入報價單列表失敗')
      appStore.showError(error.value)
      throw err
    } finally {
      loading.value = false
    }
  }

  async function fetchQuote(id) {
    loading.value = true
    error.value = null

    try {
      const response = await quoteApi.getQuote(id)
      currentQuote.value = extractQuoteRecord(response)
      return currentQuote.value
    } catch (err) {
      error.value = extractErrorMessage(err, '載入報價單失敗')
      appStore.showError(error.value)
      throw err
    } finally {
      loading.value = false
    }
  }

  async function createQuote(quoteData) {
    submitting.value = true
    error.value = null

    try {
      const response = await quoteApi.createQuote(quoteData)
      appStore.showSuccess(response?.message || '報價單建立成功')
      return extractQuoteRecord(response)
    } catch (err) {
      error.value = extractErrorMessage(err, '建立報價單失敗')
      appStore.showError(error.value)
      throw err
    } finally {
      submitting.value = false
    }
  }

  async function updateQuote(id, updates) {
    submitting.value = true
    error.value = null

    try {
      const response = await quoteApi.updateQuote(id, updates)
      appStore.showSuccess(response?.message || '報價單更新成功')
      return extractQuoteRecord(response)
    } catch (err) {
      error.value = extractErrorMessage(err, '更新報價單失敗')
      appStore.showError(error.value)
      throw err
    } finally {
      submitting.value = false
    }
  }

  async function deleteQuote(id) {
    submitting.value = true
    error.value = null

    try {
      const response = await quoteApi.deleteQuote(id)
      appStore.showSuccess(response?.message || '報價單已刪除')
      return true
    } catch (err) {
      error.value = extractErrorMessage(err, '刪除報價單失敗')
      appStore.showError(error.value)
      throw err
    } finally {
      submitting.value = false
    }
  }

  async function sendQuote(id, payload) {
    submitting.value = true
    error.value = null

    try {
      const response = await quoteApi.sendQuote(id, payload)
      appStore.showSuccess(response?.message || '報價單已寄出')
      return extractQuoteRecord(response)
    } catch (err) {
      error.value = extractErrorMessage(err, '寄送報價單失敗')
      appStore.showError(error.value)
      throw err
    } finally {
      submitting.value = false
    }
  }

  async function downloadQuotePdf(id, filename) {
    try {
      await quoteApi.exportQuotePDF(id, filename)
      appStore.showSuccess('PDF 下載開始')
      return true
    } catch (err) {
      error.value = extractErrorMessage(err, '下載 PDF 失敗')
      appStore.showError(error.value)
      throw err
    }
  }

  async function downloadQuoteExcel(id, filename) {
    try {
      await quoteApi.exportQuoteExcel(id, filename)
      appStore.showSuccess('Excel 下載開始')
      return true
    } catch (err) {
      error.value = extractErrorMessage(err, '下載 Excel 失敗')
      appStore.showError(error.value)
      throw err
    }
  }

  function reset() {
    quotes.value = []
    currentQuote.value = null
    loading.value = false
    submitting.value = false
    error.value = null
  }

  return {
    quotes,
    currentQuote,
    loading,
    submitting,
    error,
    fetchQuotes,
    fetchQuote,
    createQuote,
    updateQuote,
    deleteQuote,
    sendQuote,
    downloadQuotePdf,
    downloadQuoteExcel,
    reset
  }
}

export function useItem() {
  const appStore = useAppStore()

  const items = ref([])
  const currentItem = ref(null)
  const loading = ref(false)
  const error = ref(null)

  async function fetchItems(params = {}) {
    loading.value = true
    error.value = null

    try {
      const response = await quoteApi.getItems(params)
      items.value = Array.isArray(response) ? response : (response.data || [])
      return items.value
    } catch (err) {
      error.value = extractErrorMessage(err, '載入品項列表失敗')
      appStore.showError(error.value)
      throw err
    } finally {
      loading.value = false
    }
  }

  async function fetchItem(id) {
    loading.value = true
    error.value = null

    try {
      const response = await quoteApi.getItem(id)
      currentItem.value = response.data || response
      return currentItem.value
    } catch (err) {
      error.value = extractErrorMessage(err, '載入品項失敗')
      appStore.showError(error.value)
      throw err
    } finally {
      loading.value = false
    }
  }

  return {
    items,
    currentItem,
    loading,
    error,
    fetchItems,
    fetchItem
  }
}

export function useTemplate() {
  const appStore = useAppStore()

  const templates = ref([])
  const currentTemplate = ref(null)
  const loading = ref(false)
  const error = ref(null)

  async function fetchTemplates(params = {}) {
    loading.value = true
    error.value = null

    try {
      const response = await quoteApi.getTemplates({ ...params, paginate: false })
      templates.value = Array.isArray(response) ? response : (response.data || [])
      return templates.value
    } catch (err) {
      error.value = extractErrorMessage(err, '載入模板失敗')
      appStore.showError(error.value)
      throw err
    } finally {
      loading.value = false
    }
  }

  async function createTemplate(templateData) {
    loading.value = true
    error.value = null

    try {
      const response = await quoteApi.createTemplate(templateData)
      appStore.showSuccess(response?.message || '模板建立成功')
      return response.data
    } catch (err) {
      error.value = extractErrorMessage(err, '建立模板失敗')
      appStore.showError(error.value)
      throw err
    } finally {
      loading.value = false
    }
  }

  async function updateTemplate(id, updates) {
    loading.value = true
    error.value = null

    try {
      const response = await quoteApi.updateTemplate(id, updates)
      appStore.showSuccess(response?.message || '模板更新成功')
      return response.data
    } catch (err) {
      error.value = extractErrorMessage(err, '更新模板失敗')
      appStore.showError(error.value)
      throw err
    } finally {
      loading.value = false
    }
  }

  async function deleteTemplate(id) {
    loading.value = true
    error.value = null

    try {
      const response = await quoteApi.deleteTemplate(id)
      appStore.showSuccess(response?.message || '模板已刪除')
      return true
    } catch (err) {
      error.value = extractErrorMessage(err, '刪除模板失敗')
      appStore.showError(error.value)
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
