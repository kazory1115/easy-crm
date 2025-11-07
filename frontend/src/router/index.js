/**
 * Vue Router 配置
 * 整合模組化路由系統
 */

import { createRouter, createWebHistory } from 'vue-router'
import MainLayout from '@/layouts/MainLayout.vue'
import { moduleConfig, getModules } from '@/config/modules'
import { useAuthStore } from '@/stores/auth'
import { useAppStore } from '@/stores/app'

/**
 * 動態載入已啟用模組的路由
 */
function loadModuleRoutes() {
  const moduleRoutes = []
  const enabledModules = getModules(true) // 只取得已啟用的模組

  enabledModules.forEach(module => {
    try {
      // 動態匯入模組路由
      const routes = require(`@/modules/${module.id}/routes`).default
      moduleRoutes.push(...routes)
    } catch (error) {
      console.warn(`無法載入模組 ${module.id} 的路由:`, error)
    }
  })

  return moduleRoutes
}

/**
 * 基礎路由配置
 */
const routes = [
  {
    path: '/',
    component: MainLayout,
    children: [
      {
        path: '',
        redirect: '/quote/create' // 暫時重導向到報價單建立頁面
      },
      // 動態載入模組路由
      ...loadModuleRoutes(),
      {
        path: '/:pathMatch(.*)*',
        name: 'NotFound',
        component: () => import('@/views/NotFound.vue'),
        meta: {
          title: '頁面不存在'
        }
      }
    ]
  }
]

/**
 * 建立路由器實例
 */
const router = createRouter({
  history: createWebHistory(),
  routes,
  scrollBehavior(to, from, savedPosition) {
    if (savedPosition) {
      return savedPosition
    } else {
      return { top: 0 }
    }
  }
})

/**
 * 全域前置守衛 - 認證檢查
 */
router.beforeEach((to, from, next) => {
  const authStore = useAuthStore()
  const appStore = useAppStore()

  // 開始載入
  appStore.setLoading(true)

  // 檢查是否需要認證
  if (to.meta.requiresAuth && !authStore.isLoggedIn) {
    // 導向登入頁（目前暫時允許通過，待實作登入功能）
    // next({ name: 'Login', query: { redirect: to.fullPath } })
    next()
  } else if (to.meta.permissions) {
    // 檢查權限
    const hasPermission = authStore.hasPermission(to.meta.permissions)
    if (!hasPermission) {
      appStore.showNotification({
        type: 'error',
        message: '您沒有權限訪問此頁面'
      })
      next(from.path || '/')
    } else {
      next()
    }
  } else {
    next()
  }
})

/**
 * 全域後置守衛 - 更新頁面資訊
 */
router.afterEach((to, from) => {
  const appStore = useAppStore()

  // 結束載入
  appStore.setLoading(false)

  // 更新頁面標題
  if (to.meta.title) {
    document.title = `${to.meta.title} - Easy CRM`
  }

  // 更新麵包屑
  if (to.meta.breadcrumb) {
    appStore.setBreadcrumbs(to.meta.breadcrumb)
  } else {
    appStore.setBreadcrumbs([])
  }
})

export default router
