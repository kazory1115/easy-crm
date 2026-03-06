/**
 * 模組配置檔案
 *
 * 用於控制各模組的啟用狀態、權限、路由等
 *
 * @module ModuleConfig
 */

/**
 * 模組定義
 * @typedef {Object} ModuleDefinition
 * @property {string} id - 模組唯一識別碼
 * @property {string} name - 模組顯示名稱
 * @property {string} icon - 模組圖示（可用 emoji 或 icon class）
 * @property {boolean} enabled - 是否啟用
 * @property {string} path - 路由路徑
 * @property {string} description - 模組描述
 * @property {string[]} permissions - 需要的權限列表
 * @property {number} order - 排序（側邊欄顯示順序）
 * @property {string} version - 模組版本
 * @property {Object} meta - 其他元資料
 */

/**
 * 模組配置
 *
 * 🔧 如何啟用/停用模組：
 * 將 enabled 設為 true/false
 *
 * 🔐 權限控制：
 * permissions 陣列定義需要的權限
 *
 * 📋 顯示順序：
 * order 數字越小越前面
 */
export const moduleConfig = {
  // 報價單模組
  quote: {
    id: 'quote',
    name: '報價單',
    icon: 'file-lines',
    enabled: true, // ✅ 已啟用（目前正在遷移）
    path: '/quote',
    description: '報價單建立、編輯、匯出與歷史記錄',
    permissions: ['quote.view', 'quote.create', 'quote.edit', 'quote.delete'],
    order: 1,
    version: '2.0.0',
    meta: {
      color: '#3b82f6',
      badge: '已完成',
      features: [
        '自定義範本',
        '動態欄位',
        '匯出 Word/PDF',
        '歷史記錄'
      ]
    },
    children: [
      {
        id: 'quote-create',
        name: '新增報價單',
        path: '/quote/create',
        icon: 'plus',
        permission: 'quote.create'
      },
      {
        id: 'quote-list',
        name: '歷史紀錄',
        path: '/quote/list',
        icon: 'scroll',
        permission: 'quote.view'
      },
      {
        id: 'quote-templates',
        name: '範本管理',
        path: '/quote/templates',
        icon: 'book',
        permission: 'quote.template.manage'
      }
    ]
  },

  // 訂單管理模組
  order: {
    id: 'order',
    name: '訂單管理',
    icon: 'clipboard-list',
    enabled: true, // ✅ 已啟用（MVP 版本）
    path: '/order',
    description: '訂單建立、編輯、詳情與轉單流程',
    permissions: ['order.view', 'order.create', 'order.edit', 'order.delete'],
    order: 2,
    version: '1.0.0',
    meta: {
      color: '#2563eb',
      badge: 'MVP',
      features: [
        '訂單列表與篩選',
        '建立與編輯',
        '訂單詳情',
        '報價轉單'
      ]
    },
    children: [
      {
        id: 'order-create',
        name: '新增訂單',
        path: '/order/create',
        icon: 'plus',
        permission: 'order.create'
      },
      {
        id: 'order-list',
        name: '訂單列表',
        path: '/order/list',
        icon: 'scroll',
        permission: 'order.view'
      }
    ]
  },

  // 客戶管理模組
  crm: {
    id: 'crm',
    name: '客戶管理',
    icon: 'users',
    enabled: true, // ✅ 已啟用（MVP 版本）
    path: '/crm',
    description: '客戶資料、聯絡歷程、商機管理',
    permissions: ['crm.view', 'crm.create', 'crm.edit', 'crm.delete'],
    order: 3,
    version: '1.0.0',
    meta: {
      color: '#10b981',
      badge: 'MVP',
      features: [
        '客戶資料管理',
        '聯絡歷程追蹤',
        '商機管理'
      ]
    },
    children: [
      {
        id: 'crm-customer-list',
        name: '客戶列表',
        path: '/crm/customers',
        icon: 'address-book',
        permission: 'crm.view'
      },
      {
        id: 'crm-customer-create',
        name: '新增客戶',
        path: '/crm/customers/create',
        icon: 'user-plus',
        permission: 'crm.create'
      },
      {
        id: 'crm-opportunity-list',
        name: '商機列表',
        path: '/crm/opportunities',
        icon: 'chart-line',
        permission: 'crm.view'
      }
    ]
  },

  // 進銷存模組
  inventory: {
    id: 'inventory',
    name: '進銷存',
    icon: 'box',
    enabled: true, // ✅ 已啟用（MVP 版本）
    path: '/inventory',
    description: '倉庫、庫存、異動與調整管理',
    permissions: ['inventory.view', 'inventory.create', 'inventory.edit', 'inventory.delete'],
    order: 4,
    version: '1.0.0',
    meta: {
      color: '#f59e0b',
      badge: 'MVP',
      features: [
        '倉庫列表',
        '庫存追蹤',
        '進銷記錄',
        '庫存調整'
      ]
    },
    children: [
      {
        id: 'inventory-stock-list',
        name: '庫存列表',
        path: '/inventory/stock',
        icon: 'boxes-stacked',
        permission: 'inventory.view'
      },
      {
        id: 'inventory-warehouse-list',
        name: '倉庫列表',
        path: '/inventory/warehouses',
        icon: 'warehouse',
        permission: 'inventory.view'
      },
      {
        id: 'inventory-movements',
        name: '庫存異動',
        path: '/inventory/movements',
        icon: 'right-left',
        permission: 'inventory.edit'
      },
      {
        id: 'inventory-adjustments',
        name: '庫存調整',
        path: '/inventory/adjustments',
        icon: 'sliders',
        permission: 'inventory.edit'
      }
    ]
  },

  // 員工管理模組
  staff: {
    id: 'staff',
    name: '員工管理',
    icon: 'briefcase',
    enabled: true, // ✅ 已啟用（MVP 版本）
    path: '/staff',
    description: '員工資料管理、操作紀錄',
    permissions: ['staff.view', 'staff.create', 'staff.edit', 'staff.delete'],
    order: 5,
    version: '1.0.0',
    meta: {
      color: '#8b5cf6',
      badge: 'MVP',
      features: [
        '員工資料管理',
        '操作紀錄查詢'
      ]
    },
    children: [
      {
        id: 'staff-list',
        name: '員工列表',
        path: '/staff/list',
        icon: 'circle-user',
        permission: 'staff.view'
      },
      {
        id: 'activity-logs',
        name: '操作紀錄',
        path: '/staff/logs',
        icon: 'clock-rotate-left',
        permission: 'staff.view'
      }
    ]
  },

  // 報表中心模組
  report: {
    id: 'report',
    name: '報表中心',
    icon: 'chart-bar',
    enabled: true, // ✅ 已啟用（MVP 版本）
    path: '/report',
    description: '報價、訂單、庫存 dashboard 與匯出任務追蹤',
    permissions: ['report.view', 'report.export'],
    order: 6,
    version: '1.0.0',
    meta: {
      color: '#0f766e',
      badge: 'MVP',
      features: [
        '儀表板',
        '報價與訂單摘要',
        '低庫存監控',
        '匯出任務紀錄'
      ]
    },
    children: [
      {
        id: 'report-dashboard',
        name: '報表總覽',
        path: '/report/dashboard',
        icon: 'chart-bar',
        permission: 'report.view'
      },
      {
        id: 'report-exports',
        name: '匯出紀錄',
        path: '/report/exports',
        icon: 'scroll',
        permission: 'report.view'
      }
    ]
  }
}

/**
 * 取得已啟用的模組列表
 * @returns {Array<ModuleDefinition>} 已啟用的模組
 */
export function getEnabledModules() {
  return Object.values(moduleConfig).filter(module => module.enabled)
}

/**
 * 取得指定模組配置
 * @param {string} moduleId - 模組 ID
 * @returns {ModuleDefinition|null} 模組配置
 */
export function getModuleById(moduleId) {
  return moduleConfig[moduleId] || null
}

/**
 * 檢查模組是否啟用
 * @param {string} moduleId - 模組 ID
 * @returns {boolean} 是否啟用
 */
export function isModuleEnabled(moduleId) {
  const module = getModuleById(moduleId)
  return module ? module.enabled : false
}

/**
 * 取得模組列表（按順序排列）
 * @param {boolean} onlyEnabled - 是否只返回已啟用的模組
 * @returns {Array<ModuleDefinition>} 模組列表
 */
export function getModules(onlyEnabled = false) {
  const modules = Object.values(moduleConfig)
  const filtered = onlyEnabled ? modules.filter(m => m.enabled) : modules
  return filtered.sort((a, b) => a.order - b.order)
}

/**
 * 檢查使用者是否有模組權限
 * @param {string} moduleId - 模組 ID
 * @param {Array<string>} userPermissions - 使用者權限列表
 * @returns {boolean} 是否有權限
 */
export function hasModulePermission(moduleId, userPermissions = []) {
  const module = getModuleById(moduleId)
  if (!module) return false

  // 如果模組沒有定義權限要求，則允許訪問
  if (!module.permissions || module.permissions.length === 0) {
    return true
  }

  // 檢查使用者是否至少擁有一個模組要求的權限
  return module.permissions.some(permission =>
    userPermissions.includes(permission)
  )
}

/**
 * 動態設定模組啟用狀態（僅供開發/管理員使用）
 * ⚠️ 注意：實際生產環境應該由後端控制
 *
 * @param {string} moduleId - 模組 ID
 * @param {boolean} enabled - 啟用狀態
 */
export function setModuleEnabled(moduleId, enabled) {
  if (moduleConfig[moduleId]) {
    moduleConfig[moduleId].enabled = enabled
    console.log(`[Module] ${moduleId} ${enabled ? '已啟用' : '已停用'}`)
  }
}

// 導出預設配置
export default moduleConfig
