import { get, post } from '@/utils/http'

export function getDashboard(params = {}) {
  return get('/reports/dashboard', params)
}

export function getExportRecords(params = {}) {
  return get('/reports/exports', params)
}

export function createExportRecord(payload = {}) {
  return post('/reports/exports', payload)
}
