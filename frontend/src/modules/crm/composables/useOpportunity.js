import { ref } from 'vue'
import { useAppStore } from '@/stores/app'
import * as crmApi from '../api/crmApi'

function extractRows(response) {
  if (Array.isArray(response)) return response
  if (Array.isArray(response?.data)) return response.data
  if (Array.isArray(response?.data?.data)) return response.data.data
  return []
}

function extractPagination(response, requestPerPage = 15) {
  const source = response?.meta ? response.meta : response

  return {
    currentPage: Number(source?.current_page ?? 1),
    lastPage: Number(source?.last_page ?? 1),
    perPage: Number(source?.per_page ?? requestPerPage),
    total: Number(source?.total ?? 0)
  }
}

function extractErrorMessage(error, fallbackMessage) {
  return error?.response?.data?.error
    || error?.response?.data?.message
    || error?.message
    || fallbackMessage
}

function normalizeOpportunity(opportunity = {}) {
  const source = opportunity || {}

  return {
    ...source,
    id: Number(source.id || 0),
    customer_id: Number(source.customer_id || source.customer?.id || 0),
    customer_name: source.customer?.name || source.customer_name || '',
    stage: source.stage || 'new',
    status: source.status || 'open',
    amount: Number(source.amount || 0),
    name: source.name || '',
    notes: source.notes || '',
    expected_close_date: source.expected_close_date || null
  }
}

export function useOpportunity() {
  const appStore = useAppStore()

  const opportunities = ref([])
  const pagination = ref({
    currentPage: 1,
    lastPage: 1,
    perPage: 15,
    total: 0
  })
  const loading = ref(false)
  const error = ref(null)

  const fetchOpportunities = async (params = {}) => {
    loading.value = true
    error.value = null

    try {
      const response = await crmApi.getOpportunities(params)
      opportunities.value = extractRows(response).map(normalizeOpportunity)
      pagination.value = extractPagination(response, params.per_page || 15)
      return opportunities.value
    } catch (err) {
      error.value = extractErrorMessage(err, '載入商機列表失敗')
      appStore.showError(error.value)
      return []
    } finally {
      loading.value = false
    }
  }

  return {
    opportunities,
    pagination,
    loading,
    error,
    fetchOpportunities
  }
}
