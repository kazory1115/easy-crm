export const ORDER_STATUS_OPTIONS = [
  { value: 'pending', label: '待處理' },
  { value: 'confirmed', label: '已確認' },
  { value: 'processing', label: '處理中' },
  { value: 'shipped', label: '已出貨' },
  { value: 'completed', label: '已完成' },
  { value: 'cancelled', label: '已取消' }
]

export const PAYMENT_STATUS_OPTIONS = [
  { value: 'unpaid', label: '未付款' },
  { value: 'partially_paid', label: '部分付款' },
  { value: 'paid', label: '已付款' },
  { value: 'refunded', label: '已退款' }
]

const ORDER_STATUS_CLASS_MAP = {
  pending: 'bg-yellow-100 text-yellow-700',
  confirmed: 'bg-blue-100 text-blue-700',
  processing: 'bg-purple-100 text-purple-700',
  shipped: 'bg-indigo-100 text-indigo-700',
  completed: 'bg-green-100 text-green-700',
  cancelled: 'bg-red-100 text-red-700'
}

const PAYMENT_STATUS_CLASS_MAP = {
  unpaid: 'bg-gray-100 text-gray-700',
  partially_paid: 'bg-orange-100 text-orange-700',
  paid: 'bg-green-100 text-green-700',
  refunded: 'bg-red-100 text-red-700'
}

export function getOrderStatusLabel(status) {
  return ORDER_STATUS_OPTIONS.find((item) => item.value === status)?.label || status || '-'
}

export function getPaymentStatusLabel(status) {
  return PAYMENT_STATUS_OPTIONS.find((item) => item.value === status)?.label || status || '-'
}

export function getOrderStatusClass(status) {
  return ORDER_STATUS_CLASS_MAP[status] || 'bg-gray-100 text-gray-700'
}

export function getPaymentStatusClass(status) {
  return PAYMENT_STATUS_CLASS_MAP[status] || 'bg-gray-100 text-gray-700'
}

export function createDefaultOrderItem() {
  return {
    name: '',
    description: '',
    quantity: 1,
    unit: '式',
    unit_price: 0,
    notes: ''
  }
}

export function toNumber(value, fallback = 0) {
  const parsed = Number(value)
  return Number.isFinite(parsed) ? parsed : fallback
}
