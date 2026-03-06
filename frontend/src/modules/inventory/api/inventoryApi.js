import { get, post } from '@/utils/http'

const INVENTORY_API = {
  WAREHOUSES: '/warehouses',
  STOCK_LEVELS: '/stock-levels',
  STOCK_MOVEMENTS: '/stock-movements',
  STOCK_ADJUSTMENTS: '/stock-adjustments',
  ITEMS: '/quote-items'
}

export function getWarehouses(params = {}) {
  return get(INVENTORY_API.WAREHOUSES, params)
}

export function getStockLevels(params = {}) {
  return get(INVENTORY_API.STOCK_LEVELS, params)
}

export function getStockMovements(params = {}) {
  return get(INVENTORY_API.STOCK_MOVEMENTS, params)
}

export function createStockMovement(data) {
  return post(INVENTORY_API.STOCK_MOVEMENTS, data)
}

export function getStockAdjustments(params = {}) {
  return get(INVENTORY_API.STOCK_ADJUSTMENTS, params)
}

export function createStockAdjustment(data) {
  return post(INVENTORY_API.STOCK_ADJUSTMENTS, data)
}

export function getItemOptions(params = {}) {
  return get(INVENTORY_API.ITEMS, params)
}

export default {
  getWarehouses,
  getStockLevels,
  getStockMovements,
  createStockMovement,
  getStockAdjustments,
  createStockAdjustment,
  getItemOptions
}
