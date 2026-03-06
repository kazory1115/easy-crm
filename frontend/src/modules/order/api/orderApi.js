import { get, post, put, del } from '@/utils/http'

const ORDER_API = {
  LIST: '/orders',
  DETAIL: (id) => `/orders/${id}`,
  CONVERT_FROM_QUOTE: (quoteId) => `/quotes/${quoteId}/convert-to-order`
}

export function getOrders(params = {}) {
  return get(ORDER_API.LIST, params)
}

export function getOrder(id) {
  return get(ORDER_API.DETAIL(id))
}

export function createOrder(data) {
  return post(ORDER_API.LIST, data)
}

export function updateOrder(id, data) {
  return put(ORDER_API.DETAIL(id), data)
}

export function deleteOrder(id) {
  return del(ORDER_API.DETAIL(id))
}

export function convertQuoteToOrder(quoteId) {
  return post(ORDER_API.CONVERT_FROM_QUOTE(quoteId))
}

export default {
  getOrders,
  getOrder,
  createOrder,
  updateOrder,
  deleteOrder,
  convertQuoteToOrder
}
