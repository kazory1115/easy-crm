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
 * 使用 Vite 的 import.meta.glob 實現
 */
function loadModuleRoutes() {
  const moduleRoutes = [];
  // 使用 import.meta.glob 取得所有模組的 routes.js
  // eager: true 會直接載入模組，而不是回傳一個動態 import 函式
  const modules = import.meta.glob('@/modules/*/routes.js', { eager: true });

  // 取得已啟用的模組 ID 列表
  const enabledModuleIds = getModules(true).map(m => m.id);

  for (const path in modules) {
    const moduleIdMatch = path.match(/modules\/(.*?)\/routes\.js$/);
    if (!moduleIdMatch) continue;
    
    const moduleId = moduleIdMatch[1];
    
    // 檢查模組是否在啟用列表中
    if (enabledModuleIds.includes(moduleId)) {
      const routes = modules[path].default;
      if (routes) {
        moduleRoutes.push(...routes);
      } else {
        console.warn(`模組 ${moduleId} 的路由檔案未正確匯出: ${path}`);
      }
    }
  }

  return moduleRoutes;
}

/**
 * 基礎路由配置
 */
const routes = [
  {
    path: '/login',
    name: 'Login',
    component: () => import('@/views/Login.vue'),
    meta: {
      title: '登入'
    }
  },
  {
    path: '/',
    component: MainLayout,
    meta: { requiresAuth: true }, // 保護整個主佈局
    children: [
      {
        path: '',
        redirect: '/profile' // 登入後預設導向個人資料頁
      },
      {
        path: 'profile',
        name: 'Profile',
        component: () => import('@/views/Profile.vue'),
        meta: {
          title: '個人資料',
          breadcrumb: [{ name: '首頁', path: '/' }, { name: '個人資料' }]
        }
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

  const isLoggedIn = authStore.isLoggedIn;

  // 如果已登入，且要前往登入頁，則導向首頁
  if (isLoggedIn && to.name === 'Login') {
    return next({ name: 'Profile' });
  }

  // 檢查路由是否需要認證
  if (to.matched.some(record => record.meta.requiresAuth)) {
    if (!isLoggedIn) {
      // 若未登入，導向登入頁
      return next({
        name: 'Login',
        query: { redirect: to.fullPath }
      });
    }
  }

  // 檢查權限 (保留原有邏輯)
  if (to.meta.permissions) {
    const hasPermission = authStore.hasPermission(to.meta.permissions);
    if (!hasPermission) {
      appStore.showNotification({
        type: 'error',
        message: '您沒有權限訪問此頁面'
      })
      return next(from.path || '/');
    }
  }

  next();
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
