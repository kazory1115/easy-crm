import { ref } from 'vue'
import { get } from '@/utils/http'
import { useAppStore } from '@/stores/app'
import * as orderApi from '../api/orderApi'
import { toNumber } from '../constants'

function extractRows(response) {
  if (Array.isArray(response)) return response
  if (Array.isArray(response?.data)) return response.data
  if (Array.isArray(response?.data?.data)) return response.data.data
  return []
}

function extractOrder(response) {
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

function normalizeOrderItem(item = {}) {
  const quantity = Math.max(1, parseInt(item.quantity, 10) || 1)
  const unitPrice = toNumber(item.unit_price, 0)

  return {
    ...item,
    quantity,
    unit_price: unitPrice,
    subtotal: toNumber(item.subtotal, quantity * unitPrice)
  }
}

function normalizeOrder(order = {}) {
  const items = Array.isArray(order.items) ? order.items.map(normalizeOrderItem) : []

  return {
    ...order,
    customer_id: order.customer_id ?? order.customer?.id ?? null,
    customer_name: order.customer_name ?? order.customer?.name ?? '',
    subtotal: toNumber(order.subtotal, 0),
    tax_amount: toNumber(order.tax_amount, 0),
    discount_amount: toNumber(order.discount_amount, 0),
    total_amount: toNumber(order.total_amount, 0),
    tax_rate: toNumber(order.tax_rate, 0),
    items
  }
}

function extractErrorMessage(error, fallbackMessage) {
  return error?.response?.data?.error
    || error?.response?.data?.message
    || error?.message
    || fallbackMessage
}

function appendCustomerOption(map, id, name) {
  const normalizedId = Number(id)
  if (!Number.isInteger(normalizedId) || normalizedId <= 0) return
  if (map.has(normalizedId)) return

  map.set(normalizedId, {
    id: normalizedId,
    name: name || `客戶 #${normalizedId}`
  })
}

function collectCustomers(records = []) {
  const map = new Map()

  records.forEach((record) => {
    appendCustomerOption(
      map,
      record?.customer_id ?? record?.customer?.id,
      record?.customer_name ?? record?.customer?.name
    )
  })

  return Array.from(map.values())
}

export function useOrder() {
  const appStore = useAppStore()

  const orders = ref([])
  const currentOrder = ref(null)
  const pagination = ref({
    currentPage: 1,
    lastPage: 1,
    perPage: 15,
    total: 0
  })
  const customerOptions = ref([])
  const loading = ref(false)
  const submitting = ref(false)
  const customerLoading = ref(false)
  const error = ref(null)

  const fetchOrders = async (params = {}) => {
    loading.value = true
    error.value = null

    try {
      const response = await orderApi.getOrders(params)
      orders.value = extractRows(response).map(normalizeOrder)
      pagination.value = extractPagination(response, params.per_page || 15)
      return orders.value
    } catch (err) {
      error.value = extractErrorMessage(err, '載入訂單列表失敗')
      appStore.showError(error.value)
      return []
    } finally {
      loading.value = false
    }
  }

  const fetchOrder = async (id) => {
    loading.value = true
    error.value = null

    try {
      const response = await orderApi.getOrder(id)
      currentOrder.value = normalizeOrder(extractOrder(response))
      return currentOrder.value
    } catch (err) {
      error.value = extractErrorMessage(err, '載入訂單失敗')
      appStore.showError(error.value)
      return null
    } finally {
      loading.value = false
    }
  }

  const createOrder = async (payload) => {
    submitting.value = true
    error.value = null

    try {
      const response = await orderApi.createOrder(payload)
      const created = normalizeOrder(extractOrder(response))
      appStore.showSuccess(response?.message || '訂單建立成功')
      return created
    } catch (err) {
      error.value = extractErrorMessage(err, '建立訂單失敗')
      appStore.showError(error.value)
      return null
    } finally {
      submitting.value = false
    }
  }

  const updateOrder = async (id, payload) => {
    submitting.value = true
    error.value = null

    try {
      const response = await orderApi.updateOrder(id, payload)
      const updated = normalizeOrder(extractOrder(response))
      appStore.showSuccess(response?.message || '訂單更新成功')
      return updated
    } catch (err) {
      error.value = extractErrorMessage(err, '更新訂單失敗')
      appStore.showError(error.value)
      return null
    } finally {
      submitting.value = false
    }
  }

  const deleteOrder = async (id) => {
    submitting.value = true
    error.value = null

    try {
      const response = await orderApi.deleteOrder(id)
      appStore.showSuccess(response?.message || '訂單刪除成功')
      return true
    } catch (err) {
      error.value = extractErrorMessage(err, '刪除訂單失敗')
      appStore.showError(error.value)
      return false
    } finally {
      submitting.value = false
    }
  }

  const convertQuoteToOrder = async (quoteId) => {
    submitting.value = true
    error.value = null

    try {
      const response = await orderApi.convertQuoteToOrder(quoteId)
      const convertedOrder = normalizeOrder(extractOrder(response))
      appStore.showSuccess(response?.message || '報價單已成功轉為訂單')
      return convertedOrder
    } catch (err) {
      error.value = extractErrorMessage(err, '報價單轉單失敗')
      appStore.showError(error.value)
      return null
    } finally {
      submitting.value = false
    }
  }

  const fetchCustomerOptions = async () => {
    customerLoading.value = true

    try {
      const results = await Promise.allSettled([
        get('/quotes', { per_page: 100, sort_by: 'updated_at', sort_order: 'desc' }),
        get('/orders', { per_page: 100, sort_by: 'updated_at', sort_order: 'desc' })
      ])

      const quoteResponse = results[0]?.status === 'fulfilled' ? results[0].value : null
      const orderResponse = results[1]?.status === 'fulfilled' ? results[1].value : null
      const quoteCustomers = collectCustomers(extractRows(quoteResponse))
      const orderCustomers = collectCustomers(extractRows(orderResponse))
      const mergedMap = new Map()

      quoteCustomers.forEach((customer) => appendCustomerOption(mergedMap, customer.id, customer.name))
      orderCustomers.forEach((customer) => appendCustomerOption(mergedMap, customer.id, customer.name))

      customerOptions.value = Array.from(mergedMap.values()).sort((a, b) => a.id - b.id)

      if (results.every((result) => result.status === 'rejected')) {
        appStore.showWarning('無法自動載入客戶清單，請手動輸入 customer_id')
      }

      return customerOptions.value
    } catch (err) {
      appStore.showWarning('無法自動載入客戶清單，請手動輸入 customer_id')
      return customerOptions.value
    } finally {
      customerLoading.value = false
    }
  }

  return {
    orders,
    currentOrder,
    pagination,
    customerOptions,
    loading,
    submitting,
    customerLoading,
    error,
    fetchOrders,
    fetchOrder,
    createOrder,
    updateOrder,
    deleteOrder,
    convertQuoteToOrder,
    fetchCustomerOptions
  }
}
