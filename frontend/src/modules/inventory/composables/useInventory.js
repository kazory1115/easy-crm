import { ref } from 'vue'
import { useAppStore } from '@/stores/app'
import * as inventoryApi from '../api/inventoryApi'

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
  if (response?.data) return response.data
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

function normalizeWarehouse(warehouse = {}) {
  const source = warehouse || {}

  return {
    ...source,
    id: Number(source.id || 0),
    stock_levels_count: Number(source.stock_levels_count || 0),
    stock_movements_count: Number(source.stock_movements_count || 0),
    stock_adjustments_count: Number(source.stock_adjustments_count || 0),
    name: source.name || '',
    code: source.code || '',
    location: source.location || '',
    status: source.status || 'active'
  }
}

function getStockStatus(quantity, reserved, minLevel) {
  const available = Number(quantity || 0) - Number(reserved || 0)

  if (available <= 0) return 'out_of_stock'
  if (available <= Number(minLevel || 0)) return 'low_stock'
  return 'in_stock'
}

function normalizeStockLevel(stockLevel = {}) {
  const source = stockLevel || {}
  const quantity = Number(source.quantity || 0)
  const reserved = Number(source.reserved || 0)
  const minLevel = Number(source.min_level || 0)

  return {
    ...source,
    id: Number(source.id || 0),
    warehouse_id: Number(source.warehouse_id || source.warehouse?.id || 0),
    item_id: Number(source.item_id || source.item?.id || 0),
    quantity,
    reserved,
    min_level: minLevel,
    max_level: source.max_level === null || source.max_level === undefined ? null : Number(source.max_level),
    available_quantity: Number(source.available_quantity ?? (quantity - reserved)),
    warehouse_name: source.warehouse?.name || '',
    warehouse_code: source.warehouse?.code || '',
    item_name: source.item?.name || '',
    item_unit: source.item?.unit || '',
    item_category: source.item?.category || '',
    stock_status: getStockStatus(quantity, reserved, minLevel)
  }
}

function normalizeStockMovement(movement = {}) {
  const source = movement || {}

  return {
    ...source,
    id: Number(source.id || 0),
    warehouse_id: Number(source.warehouse_id || source.warehouse?.id || 0),
    item_id: Number(source.item_id || source.item?.id || 0),
    quantity: Number(source.quantity || 0),
    type: source.type || 'inbound',
    warehouse_name: source.warehouse?.name || '',
    warehouse_code: source.warehouse?.code || '',
    item_name: source.item?.name || '',
    item_unit: source.item?.unit || '',
    creator_name: source.creator?.name || '',
    note: source.note || '',
    occurred_at: source.occurred_at || source.created_at || null
  }
}

function normalizeStockAdjustment(adjustment = {}) {
  const source = adjustment || {}
  const beforeQty = Number(source.before_qty || 0)
  const afterQty = Number(source.after_qty || 0)

  return {
    ...source,
    id: Number(source.id || 0),
    warehouse_id: Number(source.warehouse_id || source.warehouse?.id || 0),
    item_id: Number(source.item_id || source.item?.id || 0),
    before_qty: beforeQty,
    after_qty: afterQty,
    difference: Number(source.difference ?? (afterQty - beforeQty)),
    reason: source.reason || '',
    note: source.note || '',
    warehouse_name: source.warehouse?.name || '',
    warehouse_code: source.warehouse?.code || '',
    item_name: source.item?.name || '',
    item_unit: source.item?.unit || '',
    creator_name: source.creator?.name || '',
    created_at: source.created_at || null
  }
}

function normalizeItemOption(item = {}) {
  const source = item || {}

  return {
    id: Number(source.id || 0),
    name: source.name || '',
    unit: source.unit || '',
    category: source.category || '',
    status: source.status || 'active'
  }
}

export function useInventory() {
  const appStore = useAppStore()

  const warehouses = ref([])
  const stockLevels = ref([])
  const stockMovements = ref([])
  const stockAdjustments = ref([])
  const warehouseOptions = ref([])
  const itemOptions = ref([])

  const warehousePagination = ref({ currentPage: 1, lastPage: 1, perPage: 15, total: 0 })
  const stockPagination = ref({ currentPage: 1, lastPage: 1, perPage: 15, total: 0 })
  const movementPagination = ref({ currentPage: 1, lastPage: 1, perPage: 15, total: 0 })
  const adjustmentPagination = ref({ currentPage: 1, lastPage: 1, perPage: 15, total: 0 })

  const warehouseLoading = ref(false)
  const stockLoading = ref(false)
  const movementLoading = ref(false)
  const adjustmentLoading = ref(false)
  const optionsLoading = ref(false)
  const submitting = ref(false)
  const error = ref(null)

  const fetchWarehouses = async (params = {}) => {
    warehouseLoading.value = true
    error.value = null

    try {
      const response = await inventoryApi.getWarehouses(params)
      warehouses.value = extractRows(response).map(normalizeWarehouse)
      warehousePagination.value = extractPagination(response, params.per_page || 15)
      return warehouses.value
    } catch (err) {
      error.value = extractErrorMessage(err, '載入倉庫列表失敗')
      appStore.showError(error.value)
      return []
    } finally {
      warehouseLoading.value = false
    }
  }

  const fetchStockLevels = async (params = {}) => {
    stockLoading.value = true
    error.value = null

    try {
      const response = await inventoryApi.getStockLevels(params)
      stockLevels.value = extractRows(response).map(normalizeStockLevel)
      stockPagination.value = extractPagination(response, params.per_page || 15)
      return stockLevels.value
    } catch (err) {
      error.value = extractErrorMessage(err, '載入庫存列表失敗')
      appStore.showError(error.value)
      return []
    } finally {
      stockLoading.value = false
    }
  }

  const fetchStockMovements = async (params = {}) => {
    movementLoading.value = true
    error.value = null

    try {
      const response = await inventoryApi.getStockMovements(params)
      stockMovements.value = extractRows(response).map(normalizeStockMovement)
      movementPagination.value = extractPagination(response, params.per_page || 15)
      return stockMovements.value
    } catch (err) {
      error.value = extractErrorMessage(err, '載入庫存異動失敗')
      appStore.showError(error.value)
      return []
    } finally {
      movementLoading.value = false
    }
  }

  const fetchStockAdjustments = async (params = {}) => {
    adjustmentLoading.value = true
    error.value = null

    try {
      const response = await inventoryApi.getStockAdjustments(params)
      stockAdjustments.value = extractRows(response).map(normalizeStockAdjustment)
      adjustmentPagination.value = extractPagination(response, params.per_page || 15)
      return stockAdjustments.value
    } catch (err) {
      error.value = extractErrorMessage(err, '載入庫存調整紀錄失敗')
      appStore.showError(error.value)
      return []
    } finally {
      adjustmentLoading.value = false
    }
  }

  const createStockMovement = async (payload) => {
    submitting.value = true
    error.value = null

    try {
      const response = await inventoryApi.createStockMovement(payload)
      const data = extractRecord(response)
      appStore.showSuccess(response?.message || '庫存異動成功')

      if (payload.type === 'transfer') {
        return data
      }

      return normalizeStockMovement(data)
    } catch (err) {
      error.value = extractErrorMessage(err, '建立庫存異動失敗')
      appStore.showError(error.value)
      return null
    } finally {
      submitting.value = false
    }
  }

  const createStockAdjustment = async (payload) => {
    submitting.value = true
    error.value = null

    try {
      const response = await inventoryApi.createStockAdjustment(payload)
      const data = extractRecord(response)
      appStore.showSuccess(response?.message || '庫存調整成功')
      return {
        ...data,
        adjustment: normalizeStockAdjustment(data?.adjustment),
        stock_level: normalizeStockLevel(data?.stock_level),
        movement: normalizeStockMovement(data?.movement)
      }
    } catch (err) {
      error.value = extractErrorMessage(err, '建立庫存調整失敗')
      appStore.showError(error.value)
      return null
    } finally {
      submitting.value = false
    }
  }

  const fetchWarehouseOptions = async () => {
    optionsLoading.value = true

    try {
      const response = await inventoryApi.getWarehouses({
        per_page: 100,
        status: 'active',
        sort_by: 'name',
        sort_order: 'asc'
      })

      warehouseOptions.value = extractRows(response).map(normalizeWarehouse)
      return warehouseOptions.value
    } catch (err) {
      appStore.showWarning('無法載入倉庫選項')
      return warehouseOptions.value
    } finally {
      optionsLoading.value = false
    }
  }

  const fetchItemOptions = async () => {
    optionsLoading.value = true

    try {
      const response = await inventoryApi.getItemOptions({
        paginate: true,
        per_page: 100,
        status: 'active',
        sort_by: 'name',
        sort_order: 'asc'
      })

      itemOptions.value = extractRows(response).map(normalizeItemOption)
      return itemOptions.value
    } catch (err) {
      appStore.showWarning('無法載入品項選項')
      return itemOptions.value
    } finally {
      optionsLoading.value = false
    }
  }

  const fetchStockPreview = async ({ warehouse_id, item_id }) => {
    if (!warehouse_id || !item_id) return null

    try {
      const response = await inventoryApi.getStockLevels({
        warehouse_id,
        item_id,
        per_page: 1
      })

      const row = extractRows(response)[0]
      return row ? normalizeStockLevel(row) : null
    } catch (err) {
      return null
    }
  }

  return {
    warehouses,
    stockLevels,
    stockMovements,
    stockAdjustments,
    warehouseOptions,
    itemOptions,
    warehousePagination,
    stockPagination,
    movementPagination,
    adjustmentPagination,
    warehouseLoading,
    stockLoading,
    movementLoading,
    adjustmentLoading,
    optionsLoading,
    submitting,
    error,
    fetchWarehouses,
    fetchStockLevels,
    fetchStockMovements,
    fetchStockAdjustments,
    createStockMovement,
    createStockAdjustment,
    fetchWarehouseOptions,
    fetchItemOptions,
    fetchStockPreview
  }
}
