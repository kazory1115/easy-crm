/**
 * 報價單模組入口
 *
 * 統一匯出報價單模組的所有功能
 */

// Composables
export * from './composables/useQuote'

// API
export * as quoteApi from './api/quoteApi'

// 模組配置
export { default as quoteRoutes } from './routes'

// 模組資訊
export const moduleInfo = {
  id: 'quote',
  name: '報價單',
  version: '2.0.0',
  description: '報價單建立、編輯、匯出與歷史記錄'
}
