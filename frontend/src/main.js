import { createApp } from 'vue'
import { createPinia } from 'pinia'
import './style.css'
import App from './App.vue'
import router from './router'
import { initializeStorage } from './utils/dataManager'

// FontAwesome 配置
import { library } from '@fortawesome/fontawesome-svg-core'
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import {
  faFileLines,
  faUsers,
  faBox,
  faBriefcase,
  faChartBar,
  faPlus,
  faScroll,
  faClipboard,
  faClipboardList,
  faUser,
  faBook,
  faUserPlus,
  faCircleUser,
  faClockRotateLeft,
  faCircleCheck,
  faCircleXmark,
  faTriangleExclamation,
  faCircleInfo,
  faMagnifyingGlass,
  faArrowLeft,
  faHouse,
  faFolder
} from '@fortawesome/free-solid-svg-icons'

// 將圖標加入 library
library.add(
  faFileLines,            // 報價單
  faUsers,                // 客戶管理 / 員工管理
  faBox,                  // 進銷存
  faBriefcase,            // 員工
  faChartBar,             // 報表中心
  faPlus,                 // 新增
  faScroll,               // 歷史紀錄
  faClipboard,            // 範本
  faClipboardList,        // 操作紀錄
  faUser,                 // 使用者
  faBook,                 // 範本管理
  faUserPlus,             // 新增員工
  faCircleUser,           // 員工列表
  faClockRotateLeft,      // 操作紀錄
  faCircleCheck,          // 成功通知
  faCircleXmark,          // 錯誤通知
  faTriangleExclamation,  // 警告通知
  faCircleInfo,           // 資訊通知
  faMagnifyingGlass,      // 搜尋
  faArrowLeft,            // 返回
  faHouse,                // 首頁
  faFolder                // 資料夾
)

// 初始化 LocalStorage 資料
initializeStorage()

// 建立應用程式實例
const app = createApp(App)

// 建立 Pinia 實例
const pinia = createPinia()

// 註冊 FontAwesome 元件
app.component('font-awesome-icon', FontAwesomeIcon)

// 安裝插件
app.use(pinia)    // Pinia 狀態管理
app.use(router)   // Vue Router

// 掛載應用程式
app.mount('#app')
