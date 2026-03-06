import { ref } from 'vue'
import { useAppStore } from '@/stores/app'
import * as quoteApi from '../api/quoteApi'

export function useQuote() {
  const appStore = useAppStore()

  const quotes = ref([])
  const currentQuote = ref(null)
  const loading = ref(false)
  const error = ref(null)

  async function fetchQuotes(params = {}) {
    loading.value = true
    error.value = null

    try {
      const response = await quoteApi.getQuotes(params)
      quotes.value = response.data || []
      return quotes.value
    } catch (err) {
      error.value = err
      appStore.showError('取得報價單列表失敗')
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
      currentQuote.value = response.data
      return currentQuote.value
    } catch (err) {
      error.value = err
      appStore.showError('取得報價單失敗')
      throw err
    } finally {
      loading.value = false
    }
  }

  async function createQuote(quoteData) {
    loading.value = true
    error.value = null

    try {
      const response = await quoteApi.createQuote(quoteData)
      appStore.showSuccess('報價單建立成功')
      return response.data
    } catch (err) {
      error.value = err
      appStore.showError('報價單建立失敗')
      throw err
    } finally {
      loading.value = false
    }
  }

  async function updateQuote(id, updates) {
    loading.value = true
    error.value = null

    try {
      const response = await quoteApi.updateQuote(id, updates)
      appStore.showSuccess('報價單更新成功')
      return response.data
    } catch (err) {
      error.value = err
      appStore.showError('報價單更新失敗')
      throw err
    } finally {
      loading.value = false
    }
  }

  async function deleteQuote(id) {
    loading.value = true
    error.value = null

    try {
      await quoteApi.deleteQuote(id)
      appStore.showSuccess('報價單刪除成功')
    } catch (err) {
      error.value = err
      appStore.showError('報價單刪除失敗')
      throw err
    } finally {
      loading.value = false
    }
  }

  function reset() {
    quotes.value = []
    currentQuote.value = null
    loading.value = false
    error.value = null
  }

  return {
    quotes,
    currentQuote,
    loading,
    error,
    fetchQuotes,
    fetchQuote,
    createQuote,
    updateQuote,
    deleteQuote,
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
      error.value = err
      appStore.showError('取得一般項目列表失敗')
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
      error.value = err
      appStore.showError('取得一般項目失敗')
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
      error.value = err
      appStore.showError('取得範本列表失敗')
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
      appStore.showSuccess('範本建立成功')
      return response.data
    } catch (err) {
      error.value = err
      appStore.showError('範本建立失敗')
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
      appStore.showSuccess('範本更新成功')
      return response.data
    } catch (err) {
      error.value = err
      appStore.showError('範本更新失敗')
      throw err
    } finally {
      loading.value = false
    }
  }

  async function deleteTemplate(id) {
    loading.value = true
    error.value = null

    try {
      await quoteApi.deleteTemplate(id)
      appStore.showSuccess('範本刪除成功')
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
