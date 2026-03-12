import { ref } from 'vue'
import { useAppStore } from '@/stores/app'
import * as reportApi from '../api/reportApi'

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

function extractRecord(response) {
  if (response?.data?.id) return response.data
  if (response?.id) return response
  if (response?.data) return response.data
  return null
}

function extractErrorMessage(error, fallbackMessage) {
  return error?.response?.data?.error
    || error?.response?.data?.message
    || error?.message
    || fallbackMessage
}

function toNumber(value, fallback = 0) {
  const parsed = Number(value)
  return Number.isFinite(parsed) ? parsed : fallback
}

function normalizeTrendItem(item = {}) {
  return {
    period: item.period || '',
    count: toNumber(item.count, 0),
    amount: toNumber(item.amount, 0)
  }
}

function normalizeExportRecord(record = {}) {
  return {
    ...record,
    id: Number(record.id || 0),
    report_key: record.report_key || '',
    format: record.format || 'xlsx',
    status: record.status || 'queued',
    filters: record.filters && typeof record.filters === 'object' ? record.filters : null,
    file_path: record.file_path || '',
    created_at: record.created_at || null,
    completed_at: record.completed_at || null,
    user: record.user || null,
    user_name: record.user?.name || record.user?.email || '系統'
  }
}

function normalizeDashboard(payload = {}) {
  const quote = payload.quote || {}
  const order = payload.order || {}
  const inventory = payload.inventory || {}
  const exports = payload.exports || {}

  return {
    generated_at: payload.generated_at || null,
    filters: payload.filters || {
      start_date: '',
      end_date: '',
      range_days: 30
    },
    quote: {
      summary: {
        total: toNumber(quote.summary?.total, 0),
        draft: toNumber(quote.summary?.draft, 0),
        sent: toNumber(quote.summary?.sent, 0),
        approved: toNumber(quote.summary?.approved, 0),
        rejected: toNumber(quote.summary?.rejected, 0),
        expired: toNumber(quote.summary?.expired, 0),
        total_amount: toNumber(quote.summary?.total_amount, 0),
        approved_amount: toNumber(quote.summary?.approved_amount, 0),
        approval_rate: toNumber(quote.summary?.approval_rate, 0)
      },
      trend: Array.isArray(quote.trend) ? quote.trend.map(normalizeTrendItem) : []
    },
    order: {
      summary: {
        total: toNumber(order.summary?.total, 0),
        pending: toNumber(order.summary?.pending, 0),
        confirmed: toNumber(order.summary?.confirmed, 0),
        processing: toNumber(order.summary?.processing, 0),
        shipped: toNumber(order.summary?.shipped, 0),
        completed: toNumber(order.summary?.completed, 0),
        cancelled: toNumber(order.summary?.cancelled, 0),
        unpaid: toNumber(order.summary?.unpaid, 0),
        partially_paid: toNumber(order.summary?.partially_paid, 0),
        paid: toNumber(order.summary?.paid, 0),
        refunded: toNumber(order.summary?.refunded, 0),
        total_amount: toNumber(order.summary?.total_amount, 0),
        paid_amount: toNumber(order.summary?.paid_amount, 0),
        completion_rate: toNumber(order.summary?.completion_rate, 0)
      },
      trend: Array.isArray(order.trend) ? order.trend.map(normalizeTrendItem) : []
    },
    inventory: {
      summary: {
        warehouses: toNumber(inventory.summary?.warehouses, 0),
        tracked_items: toNumber(inventory.summary?.tracked_items, 0),
        stock_levels: toNumber(inventory.summary?.stock_levels, 0),
        total_units: toNumber(inventory.summary?.total_units, 0),
        total_reserved: toNumber(inventory.summary?.total_reserved, 0),
        low_stock_count: toNumber(inventory.summary?.low_stock_count, 0),
        out_of_stock_count: toNumber(inventory.summary?.out_of_stock_count, 0),
        movement_count: toNumber(inventory.summary?.movement_count, 0),
        inbound_count: toNumber(inventory.summary?.inbound_count, 0),
        outbound_count: toNumber(inventory.summary?.outbound_count, 0),
        transfer_count: toNumber(inventory.summary?.transfer_count, 0),
        adjustment_count: toNumber(inventory.summary?.adjustment_count, 0)
      },
      low_stock_items: Array.isArray(inventory.low_stock_items) ? inventory.low_stock_items : [],
      recent_movements: Array.isArray(inventory.recent_movements) ? inventory.recent_movements : []
    },
    exports: {
      summary: {
        total: toNumber(exports.summary?.total, 0),
        queued: toNumber(exports.summary?.queued, 0),
        processing: toNumber(exports.summary?.processing, 0),
        done: toNumber(exports.summary?.done, 0),
        failed: toNumber(exports.summary?.failed, 0)
      },
      recent_records: Array.isArray(exports.recent_records)
        ? exports.recent_records.map(normalizeExportRecord)
        : []
    }
  }
}

function createEmptyDashboard() {
  return normalizeDashboard({})
}

export function useReport() {
  const appStore = useAppStore()

  const dashboard = ref(createEmptyDashboard())
  const exportRecords = ref([])
  const exportPagination = ref({
    currentPage: 1,
    lastPage: 1,
    perPage: 15,
    total: 0
  })

  const dashboardLoading = ref(false)
  const exportLoading = ref(false)
  const submitting = ref(false)
  const error = ref(null)

  const fetchDashboard = async (params = {}) => {
    dashboardLoading.value = true
    error.value = null

    try {
      const response = await reportApi.getDashboard(params)
      dashboard.value = normalizeDashboard(response?.data || response)
      return dashboard.value
    } catch (err) {
      error.value = extractErrorMessage(err, '載入 dashboard 失敗')
      appStore.showError(error.value)
      return dashboard.value
    } finally {
      dashboardLoading.value = false
    }
  }

  const fetchExportRecords = async (params = {}) => {
    exportLoading.value = true
    error.value = null

    try {
      const response = await reportApi.getExportRecords(params)
      exportRecords.value = extractRows(response).map(normalizeExportRecord)
      exportPagination.value = extractPagination(response, params.per_page || 15)
      return exportRecords.value
    } catch (err) {
      error.value = extractErrorMessage(err, '載入匯出紀錄失敗')
      appStore.showError(error.value)
      return []
    } finally {
      exportLoading.value = false
    }
  }

  const createExportRecord = async (payload) => {
    submitting.value = true
    error.value = null

    try {
      const response = await reportApi.createExportRecord(payload)
      const record = normalizeExportRecord(extractRecord(response))
      appStore.showSuccess(response?.message || '匯出任務已建立')
      return record
    } catch (err) {
      error.value = extractErrorMessage(err, '建立匯出任務失敗')
      appStore.showError(error.value)
      return null
    } finally {
      submitting.value = false
    }
  }

  return {
    dashboard,
    exportRecords,
    exportPagination,
    dashboardLoading,
    exportLoading,
    submitting,
    error,
    fetchDashboard,
    fetchExportRecords,
    createExportRecord
  }
}
