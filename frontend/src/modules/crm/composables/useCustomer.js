import { computed, ref } from 'vue'
import { useAppStore } from '@/stores/app'
import * as crmApi from '../api/crmApi'

function extractRows(response) {
  if (Array.isArray(response)) return response
  if (Array.isArray(response?.data)) return response.data
  if (Array.isArray(response?.data?.data)) return response.data.data
  return []
}

function extractRecord(response) {
  if (response?.data?.id) return response.data
  if (response?.id) return response
  if (response?.data?.data?.id) return response.data.data
  return null
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

function normalizeCustomer(customer = {}) {
  const source = customer || {}

  return {
    ...source,
    id: Number(source.id || 0),
    contacts_count: Number(source.contacts_count || 0),
    activities_count: Number(source.activities_count || 0),
    opportunities_count: Number(source.opportunities_count || 0),
    name: source.name || '',
    contact_person: source.contact_person || '',
    phone: source.phone || '',
    mobile: source.mobile || '',
    email: source.email || '',
    tax_id: source.tax_id || '',
    website: source.website || '',
    industry: source.industry || '',
    address: source.address || '',
    notes: source.notes || '',
    status: source.status || 'active'
  }
}

function normalizeContact(contact = {}) {
  const source = contact || {}

  return {
    ...source,
    id: Number(source.id || 0),
    name: source.name || '',
    title: source.title || '',
    phone: source.phone || '',
    mobile: source.mobile || '',
    email: source.email || '',
    notes: source.notes || '',
    is_primary: Boolean(source.is_primary)
  }
}

function normalizeActivity(activity = {}) {
  const source = activity || {}

  return {
    ...source,
    id: Number(source.id || 0),
    type: source.type || 'other',
    subject: source.subject || '',
    content: source.content || '',
    activity_at: source.activity_at || null,
    next_action_at: source.next_action_at || null,
    user_name: source.user?.name || source.user_name || ''
  }
}

export function useCustomer() {
  const appStore = useAppStore()

  const customers = ref([])
  const currentCustomer = ref(null)
  const contacts = ref([])
  const activities = ref([])
  const pagination = ref({
    currentPage: 1,
    lastPage: 1,
    perPage: 15,
    total: 0
  })
  const loading = ref(false)
  const detailLoading = ref(false)
  const relatedLoadingCount = ref(0)
  const relatedLoading = computed(() => relatedLoadingCount.value > 0)
  const submitting = ref(false)
  const error = ref(null)

  const fetchCustomers = async (params = {}) => {
    loading.value = true
    error.value = null

    try {
      const response = await crmApi.getCustomers(params)
      customers.value = extractRows(response).map(normalizeCustomer)
      pagination.value = extractPagination(response, params.per_page || 15)
      return customers.value
    } catch (err) {
      error.value = extractErrorMessage(err, '載入客戶列表失敗')
      appStore.showError(error.value)
      return []
    } finally {
      loading.value = false
    }
  }

  const fetchCustomer = async (id) => {
    detailLoading.value = true
    error.value = null

    try {
      const response = await crmApi.getCustomer(id)
      currentCustomer.value = normalizeCustomer(extractRecord(response))
      return currentCustomer.value
    } catch (err) {
      error.value = extractErrorMessage(err, '載入客戶資料失敗')
      appStore.showError(error.value)
      return null
    } finally {
      detailLoading.value = false
    }
  }

  const createCustomer = async (payload) => {
    submitting.value = true
    error.value = null

    try {
      const response = await crmApi.createCustomer(payload)
      const created = normalizeCustomer(extractRecord(response))
      appStore.showSuccess(response?.message || '客戶建立成功')
      return created
    } catch (err) {
      error.value = extractErrorMessage(err, '建立客戶失敗')
      appStore.showError(error.value)
      return null
    } finally {
      submitting.value = false
    }
  }

  const updateCustomer = async (id, payload) => {
    submitting.value = true
    error.value = null

    try {
      const response = await crmApi.updateCustomer(id, payload)
      const updated = normalizeCustomer(extractRecord(response))
      appStore.showSuccess(response?.message || '客戶更新成功')
      return updated
    } catch (err) {
      error.value = extractErrorMessage(err, '更新客戶失敗')
      appStore.showError(error.value)
      return null
    } finally {
      submitting.value = false
    }
  }

  const deleteCustomer = async (id) => {
    submitting.value = true
    error.value = null

    try {
      const response = await crmApi.deleteCustomer(id)
      appStore.showSuccess(response?.message || '客戶刪除成功')
      return true
    } catch (err) {
      error.value = extractErrorMessage(err, '刪除客戶失敗')
      appStore.showError(error.value)
      return false
    } finally {
      submitting.value = false
    }
  }

  const fetchCustomerContacts = async (customerId, params = {}) => {
    relatedLoadingCount.value += 1

    try {
      const response = await crmApi.getCustomerContacts(customerId, params)
      contacts.value = extractRows(response).map(normalizeContact)
      return contacts.value
    } catch (err) {
      error.value = extractErrorMessage(err, '載入聯絡人失敗')
      appStore.showError(error.value)
      contacts.value = []
      return []
    } finally {
      relatedLoadingCount.value = Math.max(0, relatedLoadingCount.value - 1)
    }
  }

  const fetchCustomerActivities = async (customerId, params = {}) => {
    relatedLoadingCount.value += 1

    try {
      const response = await crmApi.getCustomerActivities(customerId, params)
      activities.value = extractRows(response).map(normalizeActivity)
      return activities.value
    } catch (err) {
      error.value = extractErrorMessage(err, '載入活動紀錄失敗')
      appStore.showError(error.value)
      activities.value = []
      return []
    } finally {
      relatedLoadingCount.value = Math.max(0, relatedLoadingCount.value - 1)
    }
  }

  return {
    customers,
    currentCustomer,
    contacts,
    activities,
    pagination,
    loading,
    detailLoading,
    relatedLoading,
    submitting,
    error,
    fetchCustomers,
    fetchCustomer,
    createCustomer,
    updateCustomer,
    deleteCustomer,
    fetchCustomerContacts,
    fetchCustomerActivities
  }
}
