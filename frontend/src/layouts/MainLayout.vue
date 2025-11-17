<template>
  <div class="min-h-screen bg-gray-100">
    <!-- Header -->
    <header class="bg-white shadow-sm sticky top-0 z-40">
      <div class="flex items-center justify-between px-4 py-3">
        <!-- Logo & Toggle -->
        <div class="flex items-center space-x-4">
          <button
            @click="toggleSidebar"
            class="p-2 rounded-lg hover:bg-gray-100 transition-colors"
            title="切換側邊欄"
          >
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
          </button>

          <div class="flex items-center space-x-2">
            <span class="text-2xl">📊</span>
            <h1 class="text-xl font-bold text-gray-800">{{ appName }}</h1>
          </div>
        </div>

        <!-- User Info -->
        <div class="flex items-center space-x-4">
          <!-- Notifications -->
          <button class="p-2 rounded-lg hover:bg-gray-100 relative" title="通知">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
            </svg>
            <span v-if="unreadNotifications > 0" class="absolute top-1 right-1 bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center">
              {{ unreadNotifications }}
            </span>
          </button>

          <!-- User Menu -->
          <div class="relative">
            <button @click="toggleUserMenu" class="flex items-center space-x-2 cursor-pointer hover:bg-gray-100 rounded-lg p-2">
              <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center text-white font-semibold">
                {{ userInitial }}
              </div>
              <span class="text-sm font-medium text-gray-700">{{ displayName }}</span>
              <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
            </button>

            <!-- Dropdown Menu -->
            <transition
              enter-active-class="transition ease-out duration-100"
              enter-from-class="transform opacity-0 scale-95"
              enter-to-class="transform opacity-100 scale-100"
              leave-active-class="transition ease-in duration-75"
              leave-from-class="transform opacity-100 scale-100"
              leave-to-class="transform opacity-0 scale-95"
            >
              <div v-if="isUserMenuOpen" class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-50 ring-1 ring-black ring-opacity-5">
                <div class="px-4 py-2 text-sm text-gray-700 border-b">
                  <p class="font-semibold">{{ displayName }}</p>
                  <p class="truncate text-gray-500">{{ userEmail }}</p>
                </div>
                <router-link to="/profile" @click="isUserMenuOpen = false" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">個人資料</router-link>
                <a href="#" @click.prevent="handleLogout" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">登出</a>
              </div>
            </transition>
          </div>
        </div>
      </div>
    </header>

    <div class="flex">
      <!-- Sidebar -->
      <aside
        :class="[
          'bg-white shadow-lg transition-all duration-300 ease-in-out',
          sidebarCollapsed ? 'w-20' : 'w-64'
        ]"
        class="min-h-[calc(100vh-64px)] sticky top-16 z-30"
      >
        <nav class="py-4">
          <ul class="space-y-1 px-2">
            <li v-for="item in menuItems" :key="item.id">
              <!-- Item with no children -->
              <router-link
                v-if="!item.children"
                :to="item.path"
                class="flex items-center space-x-3 px-4 py-3 rounded-lg transition-colors hover:bg-gray-100"
                :class="{
                  'bg-blue-50 text-blue-600': isActive(item.path),
                  'text-gray-700': !isActive(item.path)
                }"
                :title="sidebarCollapsed ? item.name : ''"
              >
                <span class="text-2xl w-8 text-center">{{ item.icon }}</span>
                <span v-if="!sidebarCollapsed" class="font-medium">{{ item.name }}</span>
                <span
                  v-if="item.badge && !sidebarCollapsed"
                  class="ml-auto text-xs px-2 py-1 rounded-full"
                  :style="{ backgroundColor: item.color + '20', color: item.color }"
                >
                  {{ item.badge }}
                </span>
              </router-link>

              <!-- Item with children -->
              <div v-else>
                <button
                  @click="toggleSubMenu(item.id)"
                  class="w-full flex items-center justify-between space-x-3 px-4 py-3 rounded-lg transition-colors hover:bg-gray-100"
                  :class="{
                    'bg-blue-50 text-blue-600': isActive(item.path),
                    'text-gray-700': !isActive(item.path)
                  }"
                >
                  <div class="flex items-center space-x-3">
                    <span class="text-2xl w-8 text-center">{{ item.icon }}</span>
                    <span v-if="!sidebarCollapsed" class="font-medium">{{ item.name }}</span>
                  </div>
                  <svg
                    v-if="!sidebarCollapsed"
                    class="w-5 h-5 transition-transform"
                    :class="{ 'rotate-180': isSubMenuOpen(item.id) }"
                    fill="none" stroke="currentColor" viewBox="0 0 24 24"
                  >
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                  </svg>
                </button>
                <ul v-if="isSubMenuOpen(item.id) && !sidebarCollapsed" class="mt-1 space-y-1 pl-8 pr-2">
                  <template v-for="child in item.children" :key="child.id">
                    <li v-if="!child.permission || authStore.hasPermission(child.permission)">
                      <router-link
                        :to="child.path"
                        class="flex items-center space-x-3 px-4 py-2 rounded-lg transition-colors hover:bg-gray-100 text-sm"
                        :class="{
                          'bg-blue-50 text-blue-700 font-semibold': route.path === child.path,
                          'text-gray-600': route.path !== child.path
                        }"
                      >
                        <span>{{ child.icon }}</span>
                        <span>{{ child.name }}</span>
                      </router-link>
                    </li>
                  </template>
                </ul>
              </div>
            </li>
          </ul>
        </nav>
      </aside>

      <!-- Main Content -->
      <main class="flex-1 p-6">
        <!-- Breadcrumbs -->
        <nav v-if="breadcrumbs.length > 0" class="mb-4">
          <ol class="flex items-center space-x-2 text-sm text-gray-600">
            <li v-for="(crumb, index) in breadcrumbs" :key="index" class="flex items-center">
              <router-link
                v-if="crumb.path"
                :to="crumb.path"
                class="hover:text-blue-600 transition-colors"
              >
                {{ crumb.name }}
              </router-link>
              <span v-else class="text-gray-900 font-medium">{{ crumb.name }}</span>
              <svg
                v-if="index < breadcrumbs.length - 1"
                class="w-4 h-4 mx-2"
                fill="currentColor"
                viewBox="0 0 20 20"
              >
                <path
                  fill-rule="evenodd"
                  d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                  clip-rule="evenodd"
                />
              </svg>
            </li>
          </ol>
        </nav>

        <!-- Page Content -->
        <div class="bg-white rounded-lg shadow-sm p-6">
          <router-view />
        </div>
      </main>
    </div>

    <!-- Global Notifications -->
    <div class="fixed top-20 right-4 z-50 space-y-2">
      <transition-group name="notification">
        <div
          v-for="notification in notifications"
          :key="notification.id"
          :class="[
            'px-6 py-4 rounded-lg shadow-lg max-w-md',
            notificationClass(notification.type)
          ]"
        >
          <div class="flex items-center space-x-3">
            <span class="text-2xl">{{ notificationIcon(notification.type) }}</span>
            <p class="flex-1 text-sm font-medium">{{ notification.message }}</p>
            <button
              @click="removeNotification(notification.id)"
              class="text-gray-500 hover:text-gray-700"
            >
              <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                <path
                  fill-rule="evenodd"
                  d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                  clip-rule="evenodd"
                />
              </svg>
            </button>
          </div>
        </div>
      </transition-group>
    </div>

    <!-- Loading Overlay -->
    <div
      v-if="loading"
      class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
    >
      <div class="bg-white rounded-lg p-6 flex items-center space-x-4">
        <svg class="animate-spin h-8 w-8 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        <span class="text-gray-700 font-medium">載入中...</span>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import { useRoute } from 'vue-router'
import { useAppStore } from '@/stores/app'
import { useAuthStore } from '@/stores/auth'

// Stores
const appStore = useAppStore()
const authStore = useAuthStore()
const route = useRoute()

// State from stores
const sidebarCollapsed = computed(() => appStore.sidebarCollapsed)
const loading = computed(() => appStore.loading)
const notifications = computed(() => appStore.notifications)
const breadcrumbs = computed(() => appStore.breadcrumbs)
const menuItems = computed(() => appStore.sidebarMenuItems)
const displayName = computed(() => authStore.displayName)
const userEmail = computed(() => authStore.user?.email)

// Local State
const isUserMenuOpen = ref(false)
const openMenus = ref([])

// Computed
const appName = 'Easy CRM'
const unreadNotifications = computed(() => 0) // TODO: 實際通知數量
const userInitial = computed(() => {
  if (!displayName.value) return '?'
  return displayName.value.charAt(0).toUpperCase()
})

// Watch for route changes to auto-open parent menus
watch(route, (newRoute) => {
  const activeParent = menuItems.value.find(item => 
    item.children && item.children.some(child => newRoute.path.startsWith(child.path))
  );
  if (activeParent && !openMenus.value.includes(activeParent.id)) {
    openMenus.value.push(activeParent.id);
  }
}, { immediate: true });


// Methods
function toggleSidebar() {
  appStore.toggleSidebar()
}

function toggleUserMenu() {
  isUserMenuOpen.value = !isUserMenuOpen.value
}

async function handleLogout() {
  isUserMenuOpen.value = false
  await authStore.logout()
}

function isActive(path) {
  return route.path.startsWith(path)
}

function toggleSubMenu(menuId) {
  const index = openMenus.value.indexOf(menuId);
  if (index === -1) {
    openMenus.value.push(menuId);
  } else {
    openMenus.value.splice(index, 1);
  }
}

function isSubMenuOpen(menuId) {
  return openMenus.value.includes(menuId);
}

function removeNotification(id) {
  appStore.removeNotification(id)
}

function notificationClass(type) {
  const classes = {
    success: 'bg-green-100 text-green-800 border-l-4 border-green-500',
    error: 'bg-red-100 text-red-800 border-l-4 border-red-500',
    warning: 'bg-yellow-100 text-yellow-800 border-l-4 border-yellow-500',
    info: 'bg-blue-100 text-blue-800 border-l-4 border-blue-500'
  }
  return classes[type] || classes.info
}

function notificationIcon(type) {
  const icons = {
    success: '✅',
    error: '❌',
    warning: '⚠️',
    info: 'ℹ️'
  }
  return icons[type] || icons.info
}
</script>

<style scoped>
/* Notification animations */
.notification-enter-active,
.notification-leave-active {
  transition: all 0.3s ease;
}

.notification-enter-from {
  transform: translateX(100%);
  opacity: 0;
}

.notification-leave-to {
  transform: translateX(100%);
  opacity: 0;
}
</style>
