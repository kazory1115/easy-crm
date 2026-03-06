export const MOVEMENT_TYPE_OPTIONS = [
  { value: 'inbound', label: '入庫' },
  { value: 'outbound', label: '出庫' },
  { value: 'transfer', label: '調撥' }
]

export const STOCK_STATUS_OPTIONS = [
  { value: 'in_stock', label: '有庫存' },
  { value: 'low_stock', label: '低庫存' },
  { value: 'out_of_stock', label: '缺貨' }
]

export const ADJUSTMENT_REASON_OPTIONS = [
  { value: 'count', label: '盤點修正' },
  { value: 'damage', label: '損壞' },
  { value: 'loss', label: '遺失' },
  { value: 'manual_adjustment', label: '手動調整' },
  { value: 'other', label: '其他' }
]

export function getWarehouseStatusLabel(status) {
  return status === 'inactive' ? '停用' : '啟用'
}

export function getWarehouseStatusClass(status) {
  return status === 'inactive'
    ? 'bg-gray-100 text-gray-700'
    : 'bg-emerald-100 text-emerald-700'
}

export function getMovementTypeLabel(type) {
  return MOVEMENT_TYPE_OPTIONS.find((option) => option.value === type)?.label || type || '-'
}

export function getMovementTypeClass(type) {
  if (type === 'outbound') return 'bg-red-100 text-red-700'
  if (type === 'transfer') return 'bg-blue-100 text-blue-700'
  return 'bg-emerald-100 text-emerald-700'
}

export function getStockHealthLabel(stockStatus) {
  const map = {
    in_stock: '有庫存',
    low_stock: '低庫存',
    out_of_stock: '缺貨'
  }

  return map[stockStatus] || stockStatus || '-'
}

export function getStockHealthClass(stockStatus) {
  if (stockStatus === 'out_of_stock') return 'bg-red-100 text-red-700'
  if (stockStatus === 'low_stock') return 'bg-amber-100 text-amber-700'
  return 'bg-emerald-100 text-emerald-700'
}

export function getAdjustmentReasonLabel(reason) {
  return ADJUSTMENT_REASON_OPTIONS.find((option) => option.value === reason)?.label || reason || '-'
}
