# Easy CRM 專案現況盤點

## 1. 問題本質

這個專案目前不是「什麼都沒有」，而是已經有一個 **可運作的 MVP 骨架**，但整體狀態是：

- 前端已完成 `quote`、`staff`、`auth/profile` 主流程
- 後端已完成 `auth`、`quote`、`quote-items`、`templates`、`users`、`activity-logs`、`orders` API
- `CRM / Inventory / Report / Order 前端` 仍停在「資料層或結構已經存在，但 UI/API/流程沒有全接起來」
- 專案內仍有一些「看起來像完成，但實際上還沒收斂」的地方，後續若不整理，會在權限、登入、模組啟用、API 對接一致性上出問題

---

## 2. 目前前端有什麼

前端技術：

- Vue 3
- Vite
- Pinia
- Vue Router
- Axios

前端主要結構：

- `frontend/src/views`
  - `Login.vue`
  - `Profile.vue`
  - `NotFound.vue`
- `frontend/src/layouts`
  - `MainLayout.vue`
- `frontend/src/modules`
  - `quote`
  - `staff`
  - `crm`
  - `inventory`
  - `report`

### 2.1 已可用的前端模組

#### Quote 模組

檔案位置：

- `frontend/src/modules/quote`

目前已有：

- 報價單列表
- 建立報價單
- 編輯報價單
- 報價單詳情
- 範本管理
- 一般項目載入
- API 串接已改為單一來源，不再走 LocalStorage/mock JSON

已接 API：

- `/api/quotes`
- `/api/quote-items`
- `/api/templates`

目前狀態判斷：

- 這是目前前端完成度最高的模組
- 主要 CRUD 路徑已接上後端
- 但仍有幾個未收斂點：
  - `frontend/src/modules/quote/routes.js` 的 `requiresAuth` 已改為 `true`
  - 匯出按鈕目前是前端產 Word/列印，不是正式串後端 PDF/Excel 匯出
  - 發送報價單流程前端若要完整落地，還需要搭配後端實作寄信

#### Staff 模組

檔案位置：

- `frontend/src/modules/staff`

目前已有：

- 員工列表
- 操作紀錄頁
- staff API / log API
- 權限判斷整合

已接 API：

- `/api/users`
- `/api/activity-logs`

目前狀態判斷：

- 已可當 MVP 用
- `useStaff.js` 已收斂為 API-only
- `staffApi.js` 已清理為只保留後端目前จริง有的 route
- 目前剩餘缺口在於：
  - staff 額外功能（角色、部門、匯出、組織架構）尚未開發
  - staff 測試仍不足

#### Auth / Profile

目前已有：

- 登入頁
- 個人資料頁
- Sanctum token-based 流程
- Router 全域認證守衛
- 權限判斷

目前狀態判斷：

- 可以登入、進主系統、做權限檢查
- Login 頁已補 loading 與錯誤訊息顯示
- auth store 已補 app 啟動時透過 `/api/auth/me` 同步登入狀態
- 目前剩餘缺口在於：
  - auth flow 測試尚未補齊
  - forgot/reset password 仍未落地

### 2.2 已建立結構但未啟用的前端模組

#### CRM 模組

檔案位置：

- `frontend/src/modules/crm/routes.js`

目前狀態：

- 只有空 routes
- `moduleConfig` 中標記 `enabled: false`
- 沒有頁面、沒有 composable、沒有 API 封裝

#### Inventory 模組

檔案位置：

- `frontend/src/modules/inventory/routes.js`

目前狀態：

- 只有空 routes
- `moduleConfig` 中標記 `enabled: false`
- 沒有頁面、沒有 composable、沒有 API 封裝

#### Report 模組

檔案位置：

- `frontend/src/modules/report/routes.js`

目前狀態：

- 只有空 routes
- `moduleConfig` 中標記 `enabled: false`
- 沒有頁面、沒有 API 對接

### 2.3 前端目前缺什麼

#### 必補

- 統一 API 錯誤處理與通知格式
- auth / quote / staff 的測試補齊
- Login redirect 與 token 失效情境再補手動驗證

#### 中期要補

- Order 前端模組
  - 後端已經有 `/api/orders`
  - 前端目前完全沒有訂單模組
- CRM 前端頁面
  - 客戶列表
  - 客戶詳情
  - 聯絡人
  - 客戶活動
  - 商機列表
- Inventory 前端頁面
  - 倉庫
  - 庫存查詢
  - 庫存異動
  - 庫存調整
- Report 前端頁面
  - 儀表板
  - 匯出紀錄

#### 工程面要補

- 前端測試機制目前幾乎沒有
- `package.json` 只有 `dev/build/preview`
- 沒有 Vitest
- 沒有 Playwright / Cypress
- 沒有 lint / format script

---

## 3. 目前後端有什麼

後端技術：

- Laravel 12
- MySQL 8
- Sanctum
- Spatie Permission

主要 API Controller：

- `AuthController`
- `QuoteController`
- `ItemController`
- `TemplateController`
- `UserController`
- `ActivityLogController`
- `OrderController`

主要 Service：

- `QuoteService`
- `OrderService`
- `StockService`

主要 Model 已存在：

- User
- Customer
- CustomerContact
- CustomerActivity
- Opportunity
- Item
- Quote
- QuoteItem
- Template
- TemplateField
- Order
- OrderItem
- Warehouse
- StockLevel
- StockMovement
- StockAdjustment
- ReportExport
- 各模組對應 Log Model

### 3.1 已有且可用的後端 API

#### Auth

已有：

- `POST /api/auth/login`
- `POST /api/auth/logout`
- `POST /api/auth/logout-all`
- `GET /api/auth/me`
- `POST /api/auth/change-password`
- `POST /api/auth/forgot-password`
- `POST /api/auth/reset-password`

現況：

- login / logout / me / change-password 可當前使用
- forgot/reset password 仍是半成品，只有接口，沒有真正 token + mail flow

#### Quote / Quote Items / Templates

已有：

- 報價單 CRUD
- 狀態流轉
- 批次刪除
- 統計
- 轉訂單
- 一般項目 CRUD
- 範本 CRUD

現況：

- 這是目前後端最完整的一塊
- `QuoteService` 已處理報價單與項目資料組裝
- 可支撐目前 quote 前端

未完成：

- `send()` 只改狀態，沒有真的寄信
- README 提到的 PDF/Excel 匯出仍未完全落地

#### Users / Activity Logs

已有：

- 使用者 CRUD
- 使用者統計
- 活動紀錄查詢

現況：

- 可支援 staff 模組
- 權限與角色已有基礎設計

#### Orders

已有：

- 訂單 CRUD API
- 報價轉訂單 API
- Order / OrderItem / OrderLog 資料表與模型

現況：

- 後端存在，但前端沒有對應模組
- Service 中仍有 TODO，代表商業流程尚未完全成熟

### 3.2 已有資料表與 Seeder，但 API 還沒補齊的後端區塊

#### CRM 相關

已有：

- `customers`
- `customer_contacts`
- `customer_activities`
- `opportunities`
- `opportunity_logs`

Seeder 也有：

- `CustomerContactSeeder`
- `CustomerActivitySeeder`
- `OpportunitySeeder`
- `OpportunityLogSeeder`

但目前缺：

- CustomerController / CRM API routes
- Contact API
- Opportunity API
- 前端 CRM 對應頁面

#### Inventory 相關

已有：

- `warehouses`
- `stock_levels`
- `stock_movements`
- `stock_adjustments`

Seeder 也有：

- `WarehouseSeeder`
- `StockLevelSeeder`
- `StockMovementSeeder`
- `StockAdjustmentSeeder`

但目前缺：

- WarehouseController / StockController / AdjustmentController
- Inventory API routes
- 前端 inventory 模組

#### Report 相關

已有：

- `report_exports` 資料表
- `ReportExportSeeder`

但目前缺：

- Report API
- Dashboard 聚合 API
- 前端報表頁

### 3.3 後端目前缺什麼

#### 必補

- CRM API 全套 Controller / Route / Request 驗證
- Inventory API 全套 Controller / Route / Service
- Report API
- Order 流程補完整

#### 安全與一致性要補

- Seeder 預設帳號與密碼規則要統一
- `users.email` 建議加唯一索引與資料清理策略
- 角色/權限種子與前端模組權限定義要對齊

#### 業務流程要補

- forgot/reset password 真實 token 流程
- quote send 真實寄信
- quote PDF / Excel 匯出
- order 建立/更新的完整商業規則

---

## 4. 目前前後端對接狀態

### 已對接完成或接近完成

- Auth
- Quote
- Quote Items
- Templates
- Users
- Activity Logs

補充：

- Quote 前端已改為 API-only，且 route 已要求登入
- Staff 前端已改為 API-only，且只保留後端現有 API
- Auth 前端已支援登入後 localStorage 還原 + `/auth/me` 同步

### 後端有、前端沒有

- Orders

### DB/Model 有、但 API 與前端都還沒完成

- CRM
- Inventory
- Report

### 前端有標示完成，但實際仍需修正

- Quote 模組
  - 邏輯已走 API
  - 但 route meta 還沒鎖認證
- Staff 模組
  - MVP 可用
  - 但仍有 LocalStorage 備援殘留

---

## 5. 已知風險與壞味道

### 5.1 模組啟用狀態與實際完成度不一致

- `moduleConfig` 把 `quote` 標為已完成
- quote route 原本未鎖認證，現已修正為 `requiresAuth: true`
- 目前主要剩餘問題不在 quote 認證，而在匯出/寄信等後續流程尚未完成

### 5.2 前後端雙軌殘留

- quote 模組的 LocalStorage 已清掉
- staff 模組的 LocalStorage 備援也已清掉
- 目前主要風險已從雙軌邏輯，轉為「前端宣告能力是否與後端實際 API 完全一致」

### 5.3 資料層先做太多，但 API/前端沒補

- CRM / Inventory / Report / Order 都有這個現象
- 資料表、Model、Seeder 已存在
- 但沒有完整 API 或前端入口
- 後果是資料結構先固化，實際流程卻還沒驗證

### 5.4 測試覆蓋不足

目前後端測試只有：

- `ExampleTest`
- `QuoteCalculationTest`

目前缺：

- Auth feature tests
- Quote CRUD feature tests
- Template / Item feature tests
- User / Activity Log feature tests
- Order feature tests
- 權限與未授權情境測試

前端則沒有看到正式測試基礎。

---

## 6. 建議補強順序

### 第一階段：先收斂現有 MVP

目標：不要再擴功能，先把目前可用模組做實

1. Quote route 改為必須登入
   - 已完成
2. Staff composable 移除 LocalStorage 分支
   - 已完成
3. Login / Auth flow 的 UI 訊息補齊
   - 部分完成：UI 錯誤提示、loading、`/auth/me` 同步已補
4. Seeder、README、預設帳號資訊統一
   - 部分完成：預設帳號密碼已統一，仍待文件與資料清理策略完全對齊
5. 補 Auth / Quote / Staff / Activity Log 的 Feature tests
   - 尚未完成

### 第二階段：補齊後端已存在但前端沒有的區塊

目標：把已經有資料結構的模組接起來

1. 建立 Order 前端模組
2. 補 CRM API
3. 建 CRM 前端 MVP
4. 補 Inventory API
5. 建 Inventory 前端 MVP

### 第三階段：補商業流程與輸出能力

目標：讓系統從「可 CRUD」變成「可實務使用」

1. Quote 郵件發送
2. Quote PDF / Excel 匯出
3. Forgot / Reset password 真流程
4. Report API + Dashboard

---

## 7. 建議你接下來優先做的事

如果目標是把這專案從 MVP 骨架推到可持續開發，我建議優先順序如下：

1. 收斂現有模組一致性
   - quote/staff/auth 的 API-only、requiresAuth、seed/account 說明、錯誤處理全部對齊
2. 先做 Order 前端
   - 因為後端已經有 API，補前端成本最低，且能直接驗證 quote-to-order 流程
3. 再做 CRM API + 前端 MVP
   - 因為客戶是 CRM 核心資料，後續 quote/order/inventory 都會依賴它
4. Inventory 與 Report 放後面
   - 因為這兩塊目前更像資料結構儲備，不是立即能上線的主流程

---

## 8. 結論

這個專案目前最真實的狀態是：

- **不是從 0 開始**
- **也不是完整 CRM**
- **而是一個「報價 + 員工 + 認證」可運作、其他模組有資料基礎但尚未完成的 Laravel/Vue CRM 骨架**

最需要補的不是再新增更多資料表，而是：

- 收斂現有模組一致性
- 補齊缺失 API 與前端入口
- 把測試與認證/權限流程補上
- 讓 `資料表已存在` 真正變成 `功能可用`

---

## 9. MVP 收斂待辦

### 9.0 目前進度

已完成：

- [x] Quote route 已改為必須登入
- [x] Login.vue 已補 loading 與錯誤訊息
- [x] auth store 已補 `/api/auth/me` 同步目前登入者
- [x] Staff 模組已移除 LocalStorage 分支，收斂為 API-only
- [x] `staffApi.js` 已清理為僅保留後端目前存在的 route

尚未完成：

- [ ] Login redirect 情境完整驗證
- [ ] Auth / Quote / Staff / ActivityLog 測試補齊
- [ ] 統一 notification / error handling 策略
- [ ] 預設帳號說明與資料清理策略完全同步

### 9.1 目標

先把目前已經可用的 `auth + quote + staff` 收斂成一致、可維護、可驗證的 MVP，不再讓同一功能存在半套邏輯。

### 9.2 範圍

- Auth
- Quote
- Staff
- Seeder / 權限 / 文件
- 基本測試

### 9.3 待辦清單

#### A. 認證與路由一致性

- [ ] 將 `frontend/src/modules/quote/routes.js` 的 `requiresAuth` 從 `false` 改成 `true`
- [ ] 檢查 `QuoteList / QuoteCreate / QuoteEdit / QuoteDetail / TemplateManage` 是否都能在未登入時正確跳轉 `/login`
- [ ] `Login.vue` 移除 `mocked call` 註解，改成符合目前真實 API 流程
- [ ] `Login.vue` 補齊登入失敗訊息顯示，不要只 `console.error`
- [ ] app 啟動時補 `/api/auth/me` 同步權限與使用者狀態，避免只靠 localStorage 還原

#### B. Quote 模組收斂

- [ ] 檢查 Quote 前端所有操作是否都只走 API，不再殘留任何 mock / fallback
- [ ] 匯出功能拆清楚
  - 現況：前端自行產 Word / print
  - 目標：若後端 PDF/Excel 尚未完成，文件上要明確標註目前支援範圍
- [ ] `QuoteDetail.vue`、`QuoteList.vue` 補核對與 API response 欄位 mapping 是否一致
- [ ] `QuoteController@send` 未完成，前端若有送信入口，先改成 disabled 或標示「待實作」

#### C. Staff 模組收斂

- [ ] `frontend/src/modules/staff/composables/useStaff.js` 移除 LocalStorage 備援分支
- [ ] `staffApi.js` 中未對應後端路由的方法要盤掉
  - 目前前端宣告了 `/roles`、`/users/export`、`/users/departments`、`/users/org-structure`
  - 後端目前沒有對應 route
- [ ] 只保留目前後端真的有提供的 staff API
- [ ] 若角色管理尚未做，前端 UI 先隱藏或標註未開放

#### D. Seeder / 帳號 / 權限一致性

- [ ] 管理員與測試帳號預設密碼說明統一
- [ ] `UserSeeder`、`DatabaseSeeder`、README、登入頁預設值要一致
- [ ] 補一個資料清理策略
  - 檢查 `users.email` 是否已唯一
  - 若歷史資料已有重複 email，要先清資料再補唯一索引
- [ ] 前端 `moduleConfig` 權限與後端 `RoleAndPermissionSeeder` 權限名稱逐一核對

#### E. 錯誤處理與通知一致性

- [ ] 統一 `appStore.showNotification` / `showError` / `showSuccess` 呼叫方式
- [ ] API validation error 顯示規格一致
- [ ] 401 / 403 / 422 / 500 的前端處理策略寫清楚

#### F. 測試補齊

- [ ] Backend 補 `Auth` Feature tests
  - login success
  - login fail
  - logout
  - me
- [ ] Backend 補 `Quote` Feature tests
  - index
  - store
  - update
  - delete
  - status update
- [ ] Backend 補 `Item / Template / User / ActivityLog` Feature tests
- [ ] 補未授權與權限不足情境測試
- [ ] 前端至少補一層 smoke test 或 E2E 流程

### 9.4 驗收標準

- 未登入使用者不能進 quote/staff 頁面
- Login 錯誤訊息可在畫面看到
- Staff 模組不再有 LocalStorage 分支
- 文件與 seed 帳號資訊一致
- backend test 不只剩 ExampleTest

### 9.5 這階段最容易炸的點

- 前端宣告的 staff API 比後端 route 多，清理時要避免 UI 還偷偷呼叫不存在 endpoint
- `authStore` 目前 heavily 依賴 localStorage 還原，若直接改太快，可能造成刷新後權限丟失
- `users.email` 若補唯一索引前不先清歷史資料，migration 會直接失敗

---

## 10. Order 模組開發待辦

### 10.1 目標

把目前「只有後端 API、沒有前端入口」的 Order 補成可操作的第一版，並驗證 Quote -> Order 流程真的能用。

### 10.2 現況判斷

- 後端已有：
  - `OrderController`
  - `OrderService`
  - `Order / OrderItem / OrderLog` model 與 migration
  - `POST /api/quotes/{id}/convert-to-order`
  - `/api/orders` CRUD route
- 但 `OrderService::createOrder()` / `updateOrder()` 還是 TODO，直接建立/更新訂單其實還不能用
- 前端目前完全沒有 `order` 模組

### 10.3 待辦清單

#### A. 後端先補齊

- [ ] 完成 `OrderService::createOrder()`
- [ ] 完成 `OrderService::updateOrder()`
- [ ] 明確定義訂單金額計算規則
  - subtotal
  - tax
  - total
  - payment_status
- [ ] 補訂單建立/更新 transaction
- [ ] 補 OrderItem 寫入與重建邏輯
- [ ] 補 OrderLog 紀錄
- [ ] 定義取消/完成等狀態流轉規則

#### B. 後端 API 規格補強

- [ ] 補 Order Request 驗證一致性
- [ ] 定義列表查詢參數
  - search
  - status
  - payment_status
  - date range
- [ ] 定義回傳 payload 結構，和 quote 模組保持一致
- [ ] 補 `OrderController` 錯誤處理，不要只回 generic exception message

#### C. 前端建立新模組

- [ ] 建立 `frontend/src/modules/order`
- [ ] 新增：
  - `routes.js`
  - `api/orderApi.js`
  - `composables/useOrder.js`
  - `views/OrderList.vue`
  - `views/OrderDetail.vue`
  - `views/OrderCreate.vue`
  - `views/OrderEdit.vue`
- [ ] `moduleConfig` 補 `order` 模組
- [ ] 設定對應 permission
  - `order.view`
  - `order.create`
  - `order.edit`
  - `order.delete`

#### D. Quote 與 Order 串接

- [ ] 在 QuoteDetail 或 QuoteList 補「轉為訂單」入口
- [ ] 只允許 `approved` quote 轉單
- [ ] 轉單成功後可導向 OrderDetail
- [ ] 避免同一張 quote 重複轉單的規則要定義清楚

#### E. UI 第一版功能

- [ ] 訂單列表
- [ ] 訂單詳情
- [ ] 建立訂單
- [ ] 編輯訂單
- [ ] 狀態顯示
- [ ] payment status 顯示
- [ ] 關聯來源 quote 顯示

#### F. 測試

- [ ] Backend 補 Order Feature tests
  - create
  - update
  - delete
  - convert from quote
  - unauthorized
- [ ] 前端至少補一條 E2E
  - quote approve -> convert to order -> open detail

### 10.4 驗收標準

- 可直接建立訂單
- 可更新訂單
- 可從 quote 轉單
- 訂單列表與詳情頁可正常查詢
- 權限可控制是否顯示模組與操作按鈕

### 10.5 風險

- 若先做前端再補後端，最後很容易被 `OrderService` TODO 卡死
- quote 與 order 欄位命名若不統一，前端 mapping 會越來越亂
- payment 流程若現在先亂做，未來串實際金流或對帳會重工

---

## 11. CRM 模組 API + 前端待辦

### 11.1 目標

把目前已經有資料表與 seed 的 CRM 補成第一版可用流程，至少能支撐：

- 客戶資料管理
- 聯絡人管理
- 客戶活動紀錄
- 商機管理

### 11.2 現況判斷

後端已有資料模型：

- `Customer`
- `CustomerContact`
- `CustomerActivity`
- `Opportunity`
- `OpportunityLog`

後端已有 seed：

- `CustomerContactSeeder`
- `CustomerActivitySeeder`
- `OpportunitySeeder`
- `OpportunityLogSeeder`

但目前沒有：

- CustomerController
- CRM routes
- CRM API resource / request / service
- CRM 前端頁面

### 11.3 建議先做的 MVP 範圍

先不要一次做完整 CRM，第一版只做：

1. 客戶列表
2. 客戶詳情
3. 聯絡人 CRUD
4. 客戶活動列表 / 新增
5. 商機列表 / 新增 / 更新狀態

不要第一版就做：

- 客戶分群規則引擎
- 複雜報表
- 自動化提醒
- 跟進排程工作流

### 11.4 待辦清單

#### A. 後端 API 設計

- [ ] 建立 `CustomerController`
- [ ] 建立 `CustomerContactController`
- [ ] 建立 `CustomerActivityController`
- [ ] 建立 `OpportunityController`
- [ ] 規劃 API routes
  - `/api/customers`
  - `/api/customers/{id}`
  - `/api/customers/{id}/contacts`
  - `/api/customers/{id}/activities`
  - `/api/opportunities`

#### B. 後端 Service / Repository 邏輯

- [ ] 建立 CustomerService
- [ ] 建立 OpportunityService
- [ ] 客戶建立/更新流程集中，不要把聯絡人、活動、商機邏輯塞進 controller
- [ ] 客戶刪除策略定義
  - 實刪
  - 軟刪
  - 禁刪有關聯資料
- [ ] Opportunity 狀態流轉規則定義
  - new
  - contacted
  - proposal
  - won
  - lost

#### C. 驗證與查詢能力

- [ ] Customer index 支援：
  - search
  - status
  - industry
  - pagination
- [ ] Opportunity index 支援：
  - status
  - owner
  - customer
  - date range
- [ ] 補 Request validation classes 或集中驗證規則

#### D. 前端模組建置

- [ ] 建立 `frontend/src/modules/crm/api/crmApi.js`
- [ ] 建立 `frontend/src/modules/crm/composables/useCustomer.js`
- [ ] 建立 `frontend/src/modules/crm/composables/useOpportunity.js`
- [ ] 建立頁面：
  - `CustomerList.vue`
  - `CustomerDetail.vue`
  - `CustomerForm.vue`
  - `OpportunityList.vue`
- [ ] `crm/routes.js` 不再是空檔
- [ ] `moduleConfig.crm.enabled` 開啟前，先確認前後端 MVP 已可用

#### E. 與現有模組整合

- [ ] Quote 建立頁可選 customer，避免只輸入 `customer_name`
- [ ] Order 模組可連 customer 詳情
- [ ] Activity logs 補 CRM 模組操作紀錄

#### F. 測試

- [ ] Backend 補 Customer Feature tests
- [ ] Backend 補 Contact / Activity / Opportunity Feature tests
- [ ] 補關聯刪除與驗證錯誤情境
- [ ] 前端至少補 customer list / detail / create smoke test

### 11.5 驗收標準

- 可建立、編輯、查詢客戶
- 可管理客戶聯絡人
- 可新增客戶活動
- 可查看與更新商機狀態
- Quote / Order 能逐步接 customer_id，不再只靠 customer_name 字串

### 11.6 風險

- 目前 Customer 已被 quote/order/stats 依賴，CRM API 設計如果亂改欄位，會連動炸既有模組
- 客戶刪除規則若不先定義，之後 quote/order foreign key 很容易出事
- Opportunity 若先硬寫 enum 與流程，未來 sales 實際使用時可能要重做

---

## 12. Sprint Backlog 格式

以下不是理想化規劃，而是依照目前專案現況，拆成比較合理的 3 個 Sprint。

### Sprint 1：MVP 收斂

#### Sprint 目標

把 `auth + quote + staff` 收斂成一致可用的 MVP，先把現有功能做實。

#### 待辦項目

1. Quote 路由認證收斂
   - 將 `quote/routes.js` 的 `requiresAuth` 改為 `true`
   - 驗證未登入跳轉流程

2. Staff API-only 收斂
   - 移除 `useStaff.js` LocalStorage 分支
   - 清理前端宣告但後端不存在的 staff API 方法

3. Login / Auth UX 修正
   - Login 錯誤訊息可見化
   - 移除 mocked call 註解
   - app start 補 `auth/me` 同步

4. Seeder / README / 預設帳號資訊統一
   - 預設帳號密碼一致
   - 文件一致
   - 檢查 email 唯一性策略

5. Backend 測試補強
   - Auth feature tests
   - Quote feature tests
   - Staff/User feature tests
   - Activity log feature tests

#### 交付物

- 可登入且權限/跳轉正常
- Quote/Staff 不再有殘留雙軌邏輯
- 測試基礎可跑
- README / Seeder 說明一致

#### 完成判定

- 未登入不可進 quote/staff
- Staff 不再依賴 LocalStorage
- backend 測試不只剩 ExampleTest / QuoteCalculationTest

### Sprint 2：Order 模組 MVP

#### Sprint 目標

把後端已存在但未完成的 Order 補到可以真的操作，並打通 Quote -> Order。

#### 待辦項目

1. Order 後端核心邏輯
   - 完成 `OrderService::createOrder()`
   - 完成 `OrderService::updateOrder()`
   - 補 transaction / item 重建 / log

2. Order API 規格收斂
   - 補 request validation
   - 定義 payload 結構
   - 統一錯誤處理

3. Order 前端模組
   - 建立 routes / api / composable
   - 建立 list / detail / create / edit 頁
   - moduleConfig 補 order 模組

4. Quote -> Order 串接
   - Quote 頁面加入轉單入口
   - 僅允許 approved quote 轉單
   - 轉單後導向訂單詳情

5. Order 測試
   - backend order feature tests
   - 一條 quote-to-order E2E

#### 交付物

- 可建立訂單
- 可更新訂單
- 可從 quote 轉成 order
- 可查看訂單列表與詳情

#### 完成判定

- 前後端都可操作 order
- quote-to-order 流程完整打通

### Sprint 3：CRM MVP

#### Sprint 目標

讓 CRM 從「只有資料表與 seed」變成第一版可用功能。

#### 待辦項目

1. CRM 後端 API
   - CustomerController
   - CustomerContactController
   - CustomerActivityController
   - OpportunityController

2. CRM Service / 驗證
   - CustomerService
   - OpportunityService
   - 查詢條件與驗證規則整理

3. CRM 前端模組
   - crmApi
   - useCustomer / useOpportunity
   - CustomerList / CustomerDetail / CustomerForm / OpportunityList
   - crm routes 補齊

4. 與 Quote / Order 整合
   - Quote 支援選 customer_id
   - Order 支援連到 customer
   - 補 CRM activity log

5. CRM 測試
   - customer / contact / activity / opportunity feature tests
   - 前端 smoke test

#### 交付物

- 可建立/查詢/編輯客戶
- 可管理聯絡人
- 可管理商機
- Quote / Order 逐步接 customer_id

#### 完成判定

- CRM 模組可正式打開 `enabled: true`
- 客戶成為 quote/order 的正式關聯來源

---

## 13. 分層實作順序

這一段是工程落地順序，不是排期順序。原則是：

1. 先補資料與商業規則
2. 再補 API 對外介面
3. 再補前端頁面
4. 最後補測試與驗收

### 13.1 MVP 收斂分層順序

#### Controller 層

1. 檢查 `AuthController` 的 login / me / logout 回傳格式是否穩定
2. 檢查 `QuoteController` 各 action 回傳格式是否一致
3. 檢查 `UserController` 目前 route 與前端實際呼叫是否一致

#### Service / Store / 規則層

1. `authStore` 增加 app start 同步 `me` 流程
2. `useStaff.js` 移除 LocalStorage 分支
3. 統一通知與 API 錯誤處理策略

#### Frontend 層

1. Quote route `requiresAuth: true`
2. Login 錯誤訊息顯示
3. Staff 頁面移除不存在功能入口
4. README / 畫面預設帳號說明一致化

#### Test 層

1. Auth feature tests
2. Quote feature tests
3. User / ActivityLog feature tests
4. 至少一條登入到 QuoteList smoke test

### 13.2 Order 模組分層順序

#### Service 層

1. 先完成 `OrderService::createOrder()`
2. 再完成 `OrderService::updateOrder()`
3. 定義金額計算、狀態流轉、重複轉單規則

#### Controller 層

1. `OrderController` request validation 收斂
2. response payload 與 Quote 對齊
3. `convertToOrder` 的錯誤處理與限制條件補齊

#### Frontend 層

1. 建 `orderApi.js`
2. 建 `useOrder.js`
3. 建 `OrderList.vue`
4. 建 `OrderDetail.vue`
5. 建 `OrderCreate.vue`
6. 建 `OrderEdit.vue`
7. 最後再接 Quote 頁面的「轉為訂單」按鈕

#### Test 層

1. backend order create/update/delete tests
2. backend convert-to-order tests
3. 前端 quote-to-order E2E

### 13.3 CRM 模組分層順序

#### Service 層

1. CustomerService
2. OpportunityService
3. 定義客戶刪除策略與商機狀態規則

#### Controller 層

1. CustomerController
2. CustomerContactController
3. CustomerActivityController
4. OpportunityController
5. API route 與查詢條件整理

#### Frontend 層

1. `crmApi.js`
2. `useCustomer.js`
3. `useOpportunity.js`
4. `CustomerList.vue`
5. `CustomerDetail.vue`
6. `CustomerForm.vue`
7. `OpportunityList.vue`
8. 補 `crm/routes.js`
9. 最後才打開 `moduleConfig.crm.enabled`

#### Test 層

1. Customer feature tests
2. Contact feature tests
3. Activity feature tests
4. Opportunity feature tests
5. 前端 customer list/detail/create smoke test

---

## 14. 實作順序總結

如果你要真的開始動工，建議順序就是：

1. 先做 `Sprint 1 / MVP 收斂`
2. 再做 `Sprint 2 / Order`
3. 最後做 `Sprint 3 / CRM`

如果你要照工程分層開：

1. 先補 Service / 商業規則
2. 再補 Controller / API
3. 再補 Frontend 頁面
4. 最後補 Test

這樣做的原因很簡單：

- 先做前端最容易被假 API 或 TODO Service 卡死
- 先做 Controller 不先定義規則，最後 API 會一直改
- 不先補測試，後面 CRM / Order 一接上去就很難回歸驗證
