export * from './composables/useOrder'
export * as orderApi from './api/orderApi'
export { default as orderRoutes } from './routes'

export const moduleInfo = {
  id: 'order',
  name: '訂單管理',
  version: '1.0.0',
  description: '訂單建立、編輯、列表與轉單流程'
}
