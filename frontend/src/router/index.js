import { createRouter, createWebHistory } from 'vue-router'
import MainLayout from '@/layouts/MainLayout.vue'
import { getModules } from '@/config/modules'
import { useAuthStore } from '@/stores/auth'
import { useAppStore } from '@/stores/app'

function loadModuleRoutes() {
  const moduleRoutes = []
  const modules = import.meta.glob('@/modules/*/routes.js', { eager: true })
  const enabledModuleIds = getModules(true).map((module) => module.id)

  for (const path in modules) {
    const match = path.match(/modules\/(.*?)\/routes\.js$/)
    if (!match) continue

    const moduleId = match[1]
    if (!enabledModuleIds.includes(moduleId)) continue

    const routes = modules[path].default
    if (routes) {
      moduleRoutes.push(...routes)
    }
  }

  return moduleRoutes
}

const routes = [
  {
    path: '/login',
    name: 'Login',
    component: () => import('@/views/Login.vue'),
    meta: { title: '登入' }
  },
  {
    path: '/forgot-password',
    name: 'ForgotPassword',
    component: () => import('@/views/ForgotPassword.vue'),
    meta: { title: '忘記密碼' }
  },
  {
    path: '/reset-password',
    name: 'ResetPassword',
    component: () => import('@/views/ResetPassword.vue'),
    meta: { title: '重設密碼' }
  },
  {
    path: '/',
    component: MainLayout,
    meta: { requiresAuth: true },
    children: [
      {
        path: '',
        redirect: '/profile'
      },
      {
        path: 'profile',
        name: 'Profile',
        component: () => import('@/views/Profile.vue'),
        meta: {
          title: '個人資料',
          breadcrumb: [
            { name: '首頁', path: '/' },
            { name: '個人資料' }
          ]
        }
      },
      ...loadModuleRoutes(),
      {
        path: '/:pathMatch(.*)*',
        name: 'NotFound',
        component: () => import('@/views/NotFound.vue'),
        meta: {
          title: '找不到頁面'
        }
      }
    ]
  }
]

const router = createRouter({
  history: createWebHistory(),
  routes,
  scrollBehavior(to, from, savedPosition) {
    if (savedPosition) {
      return savedPosition
    }

    return { top: 0 }
  }
})

router.beforeEach(async (to, from) => {
  const authStore = useAuthStore()
  const appStore = useAppStore()

  appStore.setLoading(true)

  await authStore.ensureAuthChecked()
  const isLoggedIn = authStore.isLoggedIn

  if (isLoggedIn && ['Login', 'ForgotPassword', 'ResetPassword'].includes(String(to.name))) {
    return { name: 'Profile' }
  }

  if (to.matched.some((record) => record.meta.requiresAuth) && !isLoggedIn) {
    return {
      name: 'Login',
      query: { redirect: to.fullPath }
    }
  }

  if (to.meta.permissions) {
    const hasPermission = authStore.hasPermission(to.meta.permissions)
    if (!hasPermission) {
      appStore.showError('你沒有權限進入此頁面')
      return from.path || '/'
    }
  }

  return true
})

router.afterEach((to) => {
  const appStore = useAppStore()

  appStore.setLoading(false)

  if (to.meta.title) {
    document.title = `${to.meta.title} - Easy CRM`
  }

  if (to.meta.breadcrumb) {
    appStore.setBreadcrumbs(to.meta.breadcrumb)
  } else {
    appStore.setBreadcrumbs([])
  }
})

export default router
