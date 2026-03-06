import { del, get, patch, post, put } from '@/utils/http'

const CRM_API = {
  CUSTOMERS: '/customers',
  CUSTOMER_DETAIL: (id) => `/customers/${id}`,
  CUSTOMER_CONTACTS: (customerId) => `/customers/${customerId}/contacts`,
  CUSTOMER_ACTIVITIES: (customerId) => `/customers/${customerId}/activities`,
  OPPORTUNITIES: '/opportunities',
  OPPORTUNITY_DETAIL: (id) => `/opportunities/${id}`,
  OPPORTUNITY_STATUS: (id) => `/opportunities/${id}/status`
}

export function getCustomers(params = {}) {
  return get(CRM_API.CUSTOMERS, params)
}

export function getCustomer(id) {
  return get(CRM_API.CUSTOMER_DETAIL(id))
}

export function createCustomer(data) {
  return post(CRM_API.CUSTOMERS, data)
}

export function updateCustomer(id, data) {
  return put(CRM_API.CUSTOMER_DETAIL(id), data)
}

export function deleteCustomer(id) {
  return del(CRM_API.CUSTOMER_DETAIL(id))
}

export function getCustomerContacts(customerId, params = {}) {
  return get(CRM_API.CUSTOMER_CONTACTS(customerId), params)
}

export function getCustomerActivities(customerId, params = {}) {
  return get(CRM_API.CUSTOMER_ACTIVITIES(customerId), params)
}

export function getOpportunities(params = {}) {
  return get(CRM_API.OPPORTUNITIES, params)
}

export function getOpportunity(id) {
  return get(CRM_API.OPPORTUNITY_DETAIL(id))
}

export function updateOpportunityStatus(id, data) {
  return patch(CRM_API.OPPORTUNITY_STATUS(id), data)
}

export default {
  getCustomers,
  getCustomer,
  createCustomer,
  updateCustomer,
  deleteCustomer,
  getCustomerContacts,
  getCustomerActivities,
  getOpportunities,
  getOpportunity,
  updateOpportunityStatus
}
