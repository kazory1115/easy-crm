import { createApp } from 'vue'
import { createPinia } from 'pinia'
import './style.css'
import App from './App.vue'
import router from './router'
import { initializeStorage } from './utils/dataManager'

// 初始化 LocalStorage 資料
initializeStorage()

// 建立應用程式實例
const app = createApp(App)

// 建立 Pinia 實例
const pinia = createPinia()

// 安裝插件
app.use(pinia)    // Pinia 狀態管理
app.use(router)   // Vue Router

// 掛載應用程式
app.mount('#app')
