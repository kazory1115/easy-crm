# Easy CRM Frontend 模組化架構說明

## 📋 架構概覽

本專案已從單體架構重構為**模組化架構**，支援模組動態啟用/停用，以便於功能管理和團隊協作。

---

## 🏗️ 目錄結構

```
frontend/src/
├── config/                      # 配置檔案
│   └── modules.js               # ✅ 模組配置（控制模組開關）
│
├── stores/                      # Pinia 全域狀態管理
│   ├── index.js                 # ✅ Store 入口
│   ├── app.js                   # ✅ 應用程式 Store
│   └── auth.js                  # ✅ 認證 Store
│
├── modules/                     # 模組化功能區
│   └── quote/                   # ✅ 報價單模組（已建立基礎架構）
│       ├── views/               # 頁面視圖（待遷移）
│       ├── components/          # 模組專用元件（待遷移）
│       ├── composables/
│       │   └── useQuote.js      # ✅ 報價單 Composable
│       ├── api/
│       │   └── quoteApi.js      # ✅ API 封裝
│       ├── store/               # 模組 Store（可選）
│       ├── routes.js            # ✅ 模組路由配置
│       └── index.js             # ✅ 模組入口
│
├── components/                  # 全域共用元件
│   ├── common/                  # 通用元件（待建立）
│   ├── layout/                  # 佈局元件（待建立）
│   └── charts/                  # 圖表元件（待建立）
│
├── layouts/                     # 版面配置元件
│   └── MainLayout.vue           # 主版面（待建立）
│
├── router/                      # 路由配置
│   ├── index.js                 # 主路由（待更新）
│   └── modules/                 # 模組路由（待整合）
│
├── utils/                       # 工具函式
│   ├── http.js                  # ✅ HTTP 客戶端（Axios）
│   ├── dataManager.js           # 原有 LocalStorage 管理
│   └── ...                      # 其他工具
│
├── assets/                      # 靜態資源
├── data/                        # 原有資料檔案
├── App.vue                      # 根元件
└── main.js                      # 應用程式入口（待更新）
```

---

## 🔧 核心功能說明

### 1. 模組配置系統 (`config/modules.js`)

**功能**：
- 集中管理所有模組的啟用狀態
- 定義模組的基本資訊（名稱、圖示、路徑、權限）
- 提供工具函式檢查模組狀態

**如何啟用/停用模組**：

```javascript
// 在 config/modules.js 中
export const moduleConfig = {
  quote: {
    enabled: true,  // ✅ 啟用
    // ...
  },
  crm: {
    enabled: false, // ⏸️ 停用
    // ...
  }
}
```

**常用函式**：

```javascript
import { isModuleEnabled, getEnabledModules } from '@/config/modules'

// 檢查模組是否啟用
if (isModuleEnabled('quote')) {
  // ...
}

// 取得所有已啟用的模組
const modules = getEnabledModules()
```

---

### 2. Pinia 狀態管理

#### **App Store** (`stores/app.js`)

管理應用程式全域狀態：

```javascript
import { useAppStore } from '@/stores/app'

const appStore = useAppStore()

// 側邊欄控制
appStore.toggleSidebar()

// 通知訊息
appStore.showSuccess('操作成功')
appStore.showError('操作失敗')
appStore.showWarning('注意事項')

// 麵包屑
appStore.setBreadcrumbs([
  { name: '首頁', path: '/' },
  { name: '報價單', path: '/quote' }
])

// 取得已啟用模組（供側邊欄使用）
const menuItems = appStore.sidebarMenuItems
```

#### **Auth Store** (`stores/auth.js`)

管理使用者認證：

```javascript
import { useAuthStore } from '@/stores/auth'

const authStore = useAuthStore()

// 登入/登出
await authStore.login({ email, password })
await authStore.logout()

// 檢查認證狀態
if (authStore.isAuthenticated) {
  // ...
}

// 權限檢查
if (authStore.hasPermission('quote.create')) {
  // ...
}

// 角色檢查
if (authStore.hasRole('admin')) {
  // ...
}
```

---

### 3. HTTP 客戶端 (`utils/http.js`)

統一的 API 請求工具：

```javascript
import { get, post, put, del, download } from '@/utils/http'

// GET 請求
const data = await get('/api/quotes', { page: 1, per_page: 15 })

// POST 請求
const newQuote = await post('/api/quotes', quoteData)

// PUT 請求
const updated = await put('/api/quotes/1', updates)

// DELETE 請求
await del('/api/quotes/1')

// 下載檔案
await download('/api/quotes/1/pdf', 'quote.pdf')
```

**特色**：
- ✅ 自動加入 Auth Token
- ✅ 統一錯誤處理
- ✅ 自動顯示載入狀態
- ✅ 支援檔案上傳/下載

---

### 4. 報價單模組 (`modules/quote`)

#### **Composable** (`composables/useQuote.js`)

**雙模式支援**：
- **LocalStorage 模式**：離線使用（目前模式）
- **API 模式**：整合後端後使用

```javascript
import { useQuote } from '@/modules/quote'

const {
  quotes,              // 報價單列表
  currentQuote,        // 當前報價單
  loading,             // 載入狀態
  error,               // 錯誤資訊
  fetchQuotes,         // 取得列表
  fetchQuote,          // 取得單筆
  createQuote,         // 建立
  updateQuote,         // 更新
  deleteQuote,         // 刪除
  setDataSource,       // 切換資料來源
  getDataSource        // 取得目前資料來源
} = useQuote()

// 使用範例
await fetchQuotes({ search: '關鍵字', status: 'draft' })

// 切換到 API 模式（後端整合完成後）
setDataSource('api')
```

#### **API 封裝** (`api/quoteApi.js`)

完整的 RESTful API 封裝：

```javascript
import * as quoteApi from '@/modules/quote/api/quoteApi'

// 取得報價單列表
const quotes = await quoteApi.getQuotes({ page: 1 })

// 建立報價單
const newQuote = await quoteApi.createQuote(data)

// 匯出 PDF
await quoteApi.exportQuotePDF(quoteId, 'quote.pdf')

// 批次刪除
await quoteApi.batchDeleteQuotes([1, 2, 3])
```

#### **路由配置** (`routes.js`)

```javascript
// 自動整合到主路由
import quoteRoutes from '@/modules/quote/routes'

// 路由結構
/quote/list          - 報價單列表
/quote/create        - 建立報價單
/quote/edit/:id      - 編輯報價單
/quote/detail/:id    - 報價單詳情
/quote/templates     - 範本管理
```

---

## 🚀 接下來的步驟

### 1. 遷移現有頁面到模組 (優先)

**需要遷移的檔案**：
- `src/views/Home.vue` → `src/modules/quote/views/QuoteCreate.vue`
- `src/views/History.vue` → `src/modules/quote/views/QuoteList.vue`
- `src/views/Add.vue` → `src/modules/quote/views/TemplateManage.vue`

### 2. 安裝 Pinia

```bash
cd frontend
npm install pinia
```

### 3. 更新 `main.js`

```javascript
import { createApp } from 'vue'
import { createPinia } from 'pinia'
import App from './App.vue'
import router from './router'

const app = createApp(App)
const pinia = createPinia()

app.use(pinia)
app.use(router)
app.mount('#app')
```

### 4. 更新路由配置

整合模組路由到主路由系統。

### 5. 建立共用元件庫

建立 Button, Input, Modal, Table 等共用元件。

### 6. 建立主版面佈局

包含 Header, Sidebar, Footer 的主版面。

---

## 💡 開發指南

### 新增模組

1. 在 `src/modules/` 建立模組目錄
2. 在 `config/modules.js` 註冊模組
3. 建立模組的路由配置
4. 開發頁面、元件、API

### 切換資料來源

```javascript
// 在元件中
import { useQuote } from '@/modules/quote'

const { setDataSource } = useQuote()

// 切換到 API 模式
setDataSource('api')

// 切換回 LocalStorage
setDataSource('localStorage')
```

### 權限控制

```javascript
// 在路由 meta 中定義
meta: {
  requiresAuth: true,
  permissions: ['quote.create']
}

// 在元件中檢查
import { useAuthStore } from '@/stores/auth'

const authStore = useAuthStore()

if (authStore.hasPermission('quote.create')) {
  // 顯示建立按鈕
}
```

---

## 📚 參考資源

- [Vue 3 文件](https://vuejs.org/)
- [Pinia 文件](https://pinia.vuejs.org/)
- [Vue Router 文件](https://router.vuejs.org/)
- [Axios 文件](https://axios-http.com/)

---

_最後更新: 2025-11-07_
_版本: 1.0.0_
