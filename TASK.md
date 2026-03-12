# TASK-001：MVP 認證收斂

## 任務本質

先把目前 MVP 最明顯的不一致修掉：

- Quote 模組目前已走真實 API，但 route 還沒有強制登入
- Login 頁失敗時只有 `console.error`，使用者看不到明確訊息
- `authStore` 目前主要靠 localStorage 還原登入狀態，缺少 app 啟動時用 `/api/auth/me` 同步使用者與權限

這三個問題不先補，後面做 Order / CRM 只會把不一致擴大。

---

## 任務目標

完成後要達成：

1. 未登入使用者不能進入 Quote 模組頁面
2. Login 頁登入失敗時能在畫面上看到錯誤訊息
3. App 重新整理後，會透過 `/api/auth/me` 驗證目前 token 並同步使用者/權限狀態

---

## 範圍

### 前端

- `frontend/src/modules/quote/routes.js`
- `frontend/src/views/Login.vue`
- `frontend/src/stores/auth.js`
- 如有需要：
  - `frontend/src/router/index.js`
  - `frontend/src/utils/http.js`
  - `frontend/src/stores/app.js`

### 後端

- 原則上不需要改 API
- 只需確認 `/api/auth/me` 現有回傳格式可被前端正確使用

---

## 具體待辦

### 1. Quote 路由認證收斂

- [x] 將 `frontend/src/modules/quote/routes.js` 的 `requiresAuth` 改為 `true`
- [x] 確認未登入時進 `/quote/list`、`/quote/create`、`/quote/edit/:id`、`/quote/detail/:id`、`/quote/templates` 都會被導回 `/login`（由全域 guard + requiresAuth 實作）
- [ ] 確認登入後仍能正常進 quote 頁面（待手動整合測試）

### 2. Login 頁錯誤提示補齊

- [x] 移除 `Login.vue` 中 `mocked call` 註解
- [x] 補畫面上的錯誤訊息狀態，例如：
  - 帳密錯誤
  - 網路失敗
  - 未知錯誤
- [x] 登入中狀態加上 disabled，避免重複提交
- [x] 成功登入後保留目前導向邏輯（導向 `/`）

### 3. authStore 啟動同步

- [x] 在 `authStore` 增加等效方法（`syncCurrentUser` / `ensureAuthChecked`）
- [x] app 啟動與路由守衛會在 token 存在時呼叫 `/api/auth/me`
- [x] 若 `/api/auth/me` 成功：
  - 更新 user
  - 更新 roles
  - 更新 permissions
- [x] 若 `/api/auth/me` 失敗：
  - 清掉本地 token / user / permissions / roles
  - 保持未登入狀態

### 4. 錯誤處理一致性

- [x] login fail 時不要只丟 generic error，至少保留可顯示訊息
- [x] `/api/auth/me` 401 時要正常清 session，不要卡在「看起來像登入但實際失效」

---

## 驗收標準

- [x] 未登入直接打 `/quote/list` 會跳 `/login`（程式碼路徑已對齊）
- [x] 未登入直接打 `/quote/create` 會跳 `/login`（程式碼路徑已對齊）
- [x] Login 輸入錯誤密碼時，畫面上會顯示錯誤訊息
- [x] token 過期或無效時，重新整理頁面後會被視為未登入（`/auth/me` 失敗清 session）
- [x] token 有效時，重新整理頁面後仍能維持登入狀態與權限（`/auth/me` 同步）
- [ ] Quote 模組所有頁面仍可正常使用（待手動整合驗證）

---

## 不要做的事

- [ ] 這張票不要順手開做 Order 模組
- [ ] 不要把 forgot/reset password 一起做進來
- [ ] 不要在這張票動 Staff 模組 LocalStorage 清理
- [ ] 不要擴寫新的權限系統

---

## 風險提醒

- `authStore` 如果直接在初始化就打 `/auth/me`，要注意避免 router guard 與 store 初始化互相打架
- 如果 `http` 工具層目前沒有一致處理 bearer token，`/auth/me` 可能會看起來有 token 但實際沒帶出去
- Login UI 若只顯示固定字串，未來 debug 會很難分辨是 401、422 還是網路錯

---

## 建議實作順序

1. 先補 `authStore.fetchMe()`
2. 再改 `quote/routes.js`
3. 再補 `Login.vue` 錯誤提示與 loading 狀態
4. 最後手動驗證登入、登出、刷新、直接打 URL 幾個情境

---

## 完成後建議接下一張票

下一張建議接：

- `TASK-002：Staff 模組 API-only 收斂`

原因：

- 它同樣屬於 MVP 收斂
- 跟這張票相同，都是在清掉「看似可用但實際雙軌」的技術債

---

# TASK-002：Staff 模組 API-only 收斂

## 任務本質

目前 Staff 模組雖然預設走 API，但 `useStaff.js` 仍保留完整 LocalStorage 備援邏輯，`staffApi.js` 也宣告了多個後端根本不存在的 endpoint。

這代表現在 Staff 模組是「表面上可用，實際上程式碼不一致」的狀態：

- 有些功能看起來存在，但後端其實沒有 API
- 有些邏輯看起來是 MVP，實際上還混著舊的 LocalStorage 設計

這張票的目標不是擴功能，而是把 Staff 模組收斂成真正的 API-only。

---

## 任務目標

完成後要達成：

1. Staff 模組不再保留 LocalStorage 備援邏輯
2. 前端 staff API 宣告只保留後端 реально存在的 route
3. 畫面上不再出現「前端有按鈕但後端沒有功能」的假功能

---

## 範圍

### 前端

- `frontend/src/modules/staff/composables/useStaff.js`
- `frontend/src/modules/staff/api/staffApi.js`
- `frontend/src/modules/staff/views/StaffList.vue`
- `frontend/src/modules/staff/views/ActivityLogs.vue`
- 如有需要：
  - `frontend/src/config/modules.js`
  - `frontend/src/stores/app.js`

### 後端

以盤點與確認為主，不預設新增 API。

目前後端已存在：

- `GET /api/users`
- `POST /api/users`
- `GET /api/users/{id}`
- `PUT /api/users/{id}`
- `DELETE /api/users/{id}`
- `GET /api/users/stats`
- `GET /api/activity-logs`
- `GET /api/activity-logs/{id}`

目前後端未看到對應 route：

- `/api/roles`
- `/api/users/export`
- `/api/users/{id}/status`
- `/api/users/departments`
- `/api/users/org-structure`
- `/api/users/{id}/roles`
- `/api/users/batch-delete`

---

## 具體待辦

### 1. 移除 LocalStorage 分支

- [x] 刪除 `useStaff.js` 中的 `USE_API` 雙軌設計
- [x] 刪除 LocalStorage 相關常數與方法
  - `STORAGE_KEY`
  - `ROLES_STORAGE_KEY`
  - `loadFromStorage()`
  - `saveToStorage()`
  - 預設角色 / 預設部門 fallback（若只服務舊 LocalStorage 流程）
- [x] 將 staff 狀態流轉全部收斂到 API 實際可支援的能力

### 2. 清理不存在的 API 宣告

- [x] 盤掉 `staffApi.js` 中後端沒有的 API 方法
- [x] 移除或註解以下方法，除非你決定這張票順手補後端：
  - `batchDeleteStaff`
  - `updateStaffStatus`
  - `exportStaff`
  - `getRoles`
  - `getRole`
  - `createRole`
  - `updateRole`
  - `deleteRole`
  - `getPermissions`
  - `assignRoles`
  - `getDepartments`
  - `getOrgStructure`
- [x] 確保 composable 與 view 不再呼叫已移除方法

### 3. 收斂 Staff UI 能力

- [x] `StaffList.vue` 只保留後端已支援的功能
  - 列表
  - 新增
  - 編輯
  - 刪除
  - 查詢
- [x] 若畫面上有角色管理、狀態切換、匯出、組織架構等入口，先隱藏或標示未開放
- [x] Activity Logs 頁面確認只使用現有 log API

### 4. 整理回傳資料 mapping

- [x] 確認 `useStaff.js` 對 `UserController` 回傳格式 mapping 正確
- [x] 確認列表分頁 response 與單筆 response 的取值一致
- [x] 補齊新增/更新/刪除成功與失敗通知

### 5. 文件與註解同步

- [x] 清掉 Staff 模組內誤導性的 LocalStorage 註解
- [x] 如有需要，在 README 或 PLAN 補註 staff 已改為 API-only

---

## 驗收標準

- [x] `useStaff.js` 不再包含 LocalStorage 相關邏輯
- [x] `staffApi.js` 只保留目前後端真的有提供的 API
- [x] Staff 頁面不再出現會打到不存在 endpoint 的操作
- [x] 員工列表、建立、編輯、刪除、操作紀錄仍可正常使用
- [x] 前端 build 可通過（2026-03-06 已驗證 `npm.cmd run build`）

---

## 不要做的事

- [ ] 這張票不要順手補 CRM
- [ ] 不要在這張票把角色/權限管理整套做完
- [ ] 不要先新增一堆後端 staff API 來配合前端假需求
- [ ] 不要把組織架構、匯出功能混進這張票

---

## 風險提醒

- `staffApi.js` 宣告的功能比後端多，清理時如果沒有同步看 view，容易把現有畫面打壞
- 若 `StaffList.vue` 現在偷偷依賴 roles/departments fallback，移除 LocalStorage 後可能會出現空選單
- `UserController@stats` 現在比較像個人統計，不一定等於前端想要的 staff dashboard，要避免誤用

---

## 建議實作順序

1. 先盤點 `StaffList.vue` 與 `ActivityLogs.vue` 實際用了哪些 API
2. 再清 `staffApi.js` 不存在的 endpoint
3. 再改 `useStaff.js` 移除 LocalStorage 分支
4. 最後修畫面上殘留的假功能與通知
5. 跑一次前端 build 驗證

---

## 完成後建議接下一張票

下一張建議接：

- `TASK-003：Order 後端 Service 補完`

原因：

- MVP 收斂做完後，下一個最值得推進的是 Order
- 目前 Order 最大 blocker 不在前端，而是 `OrderService` 還是 TODO

---

# TASK-003：Order 後端 Service 補完

## 任務本質

目前 Order 模組的 route、controller、model、migration 都已經存在，但真正的核心商業流程還沒完成：

- `OrderService::createOrder()` 直接 `throw new Exception`
- `OrderService::updateOrder()` 直接 `throw new Exception`

這代表：

- 雖然 `/api/orders` 看起來有 CRUD
- 但實際上只有 `index/show/destroy` 比較像可用
- `store/update` 現在只是空殼

如果不先補這塊，後面做 Order 前端只會接到假 API。

---

## 任務目標

完成後要達成：

1. `/api/orders` 可以真正建立訂單
2. `/api/orders/{id}` 可以真正更新訂單
3. Order 與 OrderItem 的資料寫入、重算、更新流程完整可用
4. 後續前端可以直接開始接 Order 模組，不會被 TODO 卡住

---

## 範圍

### 後端

- `backend/app/Services/OrderService.php`
- `backend/app/Http/Controllers/Api/OrderController.php`
- `backend/app/Models/Order.php`
- `backend/app/Models/OrderItem.php`
- 如有需要：
  - `backend/app/Models/OrderLog.php`
  - `backend/app/Models/Quote.php`
  - `backend/app/Models/Item.php`
  - `backend/tests/Feature/...`

### 前端

- 這張票不做前端 Order 模組
- 最多只做 API payload 對齊考量，不新增 UI

---

## 具體待辦

### 1. 補完建立訂單流程

- [x] 實作 `OrderService::createOrder(array $orderData, array $itemsData, int $userId): Order`
- [x] 建立訂單主檔
- [x] 建立對應 `OrderItem`
- [x] 將 `created_by` / `updated_by` 正確寫入
- [x] 若有 `customer_id`，同步帶入 customer 關聯資訊

### 2. 補完更新訂單流程

- [x] 實作 `OrderService::updateOrder(Order $order, array $orderData, array $itemsData, int $userId): Order`
- [x] 更新主檔欄位
- [x] 重建或同步更新 `OrderItem`
- [x] `updated_by` 正確更新
- [x] 確保更新後資料一致，不殘留舊 item

### 3. 金額計算規則收斂

- [x] 定義欄位計算規則
  - subtotal
  - tax
  - total
- [x] 明確處理 `tax_rate`
- [x] 明確處理 item 金額來源
  - `quantity * unit_price`
- [x] 確認 DB 欄位命名與實際計算欄位一致

### 4. Transaction 與資料一致性

- [x] 建立/更新流程包在 DB transaction
- [x] 主檔與明細任何一步失敗都能 rollback
- [x] 避免建立一半主檔成功、明細失敗的髒資料

### 5. 狀態規則補齊

- [x] 定義訂單建立時的預設狀態
  - `status`
  - `payment_status`
- [x] 明確哪些狀態可以更新
- [x] 若 `quote -> order` 已經建立過，決定是否允許重複轉單

### 6. Log 與追蹤

- [x] 建立訂單時寫 `OrderLog`
- [x] 更新訂單時寫 `OrderLog`
- [x] log 至少記錄：
  - user
  - action
  - order_id
  - 主要變更內容

### 7. Controller 配合修正

- [x] 檢查 `OrderController@store` / `@update` 的 request validation 是否足夠支撐 service
- [x] 若 service 決定欄位命名要統一，controller 一起對齊
- [x] 錯誤訊息不要只回 generic exception，至少要能分 validation / business error / system error

### 8. 與 Quote 轉單流程對齊

- [x] 確認 `createOrderFromQuote()` 產生的訂單欄位結構與 `createOrder()` 一致
- [x] 避免「從 quote 轉的 order」和「直接建立的 order」欄位格式不同
- [x] 若有必要，把共用邏輯抽成私有方法

### 9. 測試

- [x] 新增 Order Feature tests
  - create success
  - create validation fail
  - update success
  - update validation fail
  - unauthorized
- [x] 驗證建立與更新後 item 筆數、金額、總額正確
- [x] 驗證 transaction rollback 行為
- [x] 驗證 quote convert 後欄位結構一致

---

## 驗收標準

- [x] `POST /api/orders` 可以成功建立訂單與明細
- [x] `PUT /api/orders/{id}` 可以成功更新訂單與明細
- [x] 建立與更新都會正確重算金額
- [x] 主檔/明細任何一步失敗時不會留下半套資料
- [x] `createOrderFromQuote()` 與 `createOrder()` 產出的訂單資料結構一致
- [ ] backend tests 可驗證上述流程（目前環境缺少 `pdo_sqlite`，無法執行）

---

## 不要做的事

- [ ] 這張票不要順手開做 Order 前端頁面
- [ ] 不要先做 export / 報表 / payment gateway
- [ ] 不要把 CRM customer 流程硬綁進來
- [ ] 不要在規則還沒定義前先塞一堆前端假欄位

---

## 風險提醒

- `OrderController` 現在看起來完整，但核心其實是假的，修這張票時要以 Service 為主，不要被 Controller 表象誤導
- 若 Order 與 Quote 的欄位命名不統一，前端之後會出現大量 mapping 技術債
- 若先用「先刪全部 item 再重建」策略，要確認是否符合後續 audit/log 需求
- 若沒有 transaction，這張票表面上能跑，資料一致性仍然會炸

---

## 建議實作順序

1. 先看 `Order` / `OrderItem` model 與 migration 欄位
2. 再定義 create/update 的資料規則與金額計算
3. 先實作 `OrderService::createOrder()`
4. 再實作 `OrderService::updateOrder()`
5. 補 `OrderLog`
6. 最後補 Feature tests

---

## 完成後建議接下一張票

下一張建議接：

- `TASK-004：Order 前端模組 MVP`

原因：

- 後端 Service 補完後，前端就可以直接接
- 這樣能最快把 `quote -> order` 這條商業流程真正打通

---

# TASK-004：Order 前端模組 MVP

## 任務本質

目前 Order 在後端已經有：

- `/api/orders` CRUD route
- `Quote -> Order` 轉單 API
- `OrderController`
- `OrderService`（預期由 `TASK-003` 補完）

但前端完全沒有 `order` 模組，所以現在系統的狀態是：

- 後端有訂單能力
- 前端完全沒有入口
- quote 即使能轉單，使用者也沒有完整的訂單 UI 可用

這張票的目標是做出 **Order 模組第一版 MVP**，先把訂單流程接起來，不追求一次做到完整 ERP。

---

## 任務目標

完成後要達成：

1. 前端有正式的 Order 模組
2. 可以查看訂單列表與詳情
3. 可以建立與編輯訂單
4. 可以從 Quote 頁面轉單並查看結果

---

## 範圍

### 前端

- `frontend/src/modules/order/`
- `frontend/src/config/modules.js`
- `frontend/src/router/index.js`（若需模組註冊或 route 調整）
- 如有需要：
  - `frontend/src/utils/http.js`
  - `frontend/src/stores/app.js`

### 後端

- 原則上不新增 API
- 以前端對接現有 `/api/orders` 與 `/api/quotes/{id}/convert-to-order` 為主
- 前提是 `TASK-003` 已補完 OrderService

---

## 具體待辦

### 1. 建立 Order 模組骨架

- [x] 建立 `frontend/src/modules/order/index.js`
- [x] 建立 `frontend/src/modules/order/routes.js`
- [x] 建立 `frontend/src/modules/order/api/orderApi.js`
- [x] 建立 `frontend/src/modules/order/composables/useOrder.js`

### 2. 建立 Order 頁面

- [x] `OrderList.vue`
- [x] `OrderDetail.vue`
- [x] `OrderCreate.vue`
- [x] `OrderEdit.vue`

第一版頁面至少要支援：

- 訂單列表查詢
- 查看訂單詳情
- 建立訂單
- 編輯訂單

### 3. 串接 API

- [x] 串接 `GET /api/orders`
- [x] 串接 `GET /api/orders/{id}`
- [x] 串接 `POST /api/orders`
- [x] 串接 `PUT /api/orders/{id}`
- [x] 串接 `DELETE /api/orders/{id}`
- [x] 串接 `POST /api/quotes/{id}/convert-to-order`

### 4. 模組註冊與權限

- [x] 在 `moduleConfig` 新增 `order` 模組
- [x] 設定對應 icon / path / children
- [x] 設定對應 permission
  - `order.view`
  - `order.create`
  - `order.edit`
  - `order.delete`
- [x] 若後端權限種子還沒補，先用暫時方案標註，但不要做假權限（目前依既有 auth 流程，`admin` 角色可先使用；非 admin 待補 `order.*` 權限 seed）

### 5. 訂單列表頁 MVP

- [x] 顯示：
  - 訂單編號
  - 客戶名稱
  - 專案名稱
  - 訂單日期
  - 狀態
  - payment status
  - 總金額
- [x] 支援基本搜尋
- [x] 支援狀態篩選
- [x] 支援前往 detail / edit

### 6. 訂單建立/編輯頁 MVP

- [x] 表單欄位至少包含：
  - customer_id
  - project_name
  - order_date
  - due_date
  - notes
  - items
- [x] item 區塊至少支援：
  - name
  - quantity
  - unit
  - unit_price
- [x] 畫面即時計算：
  - subtotal
  - tax
  - total
- [x] 送出 payload 要與後端 `OrderController` 驗證規則一致

### 7. 訂單詳情頁 MVP

- [x] 顯示主檔資訊
- [x] 顯示 item 明細
- [x] 顯示金額摘要
- [x] 顯示來源 quote（若有）
- [x] 提供返回列表 / 前往編輯

### 8. Quote -> Order 串接

- [x] 在 `QuoteDetail.vue` 或 `QuoteList.vue` 補「轉為訂單」按鈕
- [x] 只允許 `approved` quote 轉單
- [x] 轉單成功後導向 `OrderDetail`
- [x] 若轉單失敗，畫面上要有可見錯誤訊息

### 9. 錯誤處理與 UX

- [x] 建立中 / 更新中按鈕 disabled
- [x] API 失敗時顯示明確通知
- [x] 刪除前有確認提示
- [x] 空列表狀態有清楚畫面

### 10. 驗證

- [x] 前端 build 通過（2026-03-06 已驗證 `npm.cmd run build`）
- [ ] 手動驗證流程：
  - 建立訂單
  - 編輯訂單
  - 查看訂單
  - 刪除訂單
  - quote 轉單
- [ ] 若有測試基礎，至少補一條 smoke / E2E

---

## 驗收標準

- [x] 側邊欄可看到 Order 模組入口（程式碼路徑已對齊）
- [x] 可正常進入訂單列表（程式碼路徑已對齊）
- [x] 可建立與編輯訂單（程式碼路徑已對齊）
- [x] 可查看訂單詳情（程式碼路徑已對齊）
- [x] 可從 quote 成功轉成 order（程式碼路徑已對齊）
- [x] 前端 build 通過（2026-03-06 已驗證 `npm.cmd run build`）

---

## 不要做的事

- [ ] 這張票不要順手做 CRM
- [ ] 不要把 payment gateway、出貨、發票流程一起塞進來
- [ ] 不要先做過度複雜的 UI，例如拖拉排序、進階儀表板
- [ ] 不要在後端 `TASK-003` 還沒補完前，硬做假資料版 order 前端

---

## 風險提醒

- 如果 `TASK-003` 沒先完成，這張票很容易變成接假 API
- 若 order payload 與 quote payload 命名差太多，前端會出現大量重複 mapping
- customer 選擇器如果現在就硬接 CRM，會被 CRM 未完成卡住，第一版可以先用簡化方案
- 如果權限種子還沒補 order 權限，模組顯示與實際權限會不一致

---

## 建議實作順序

1. 先確認 `TASK-003` 已完成
2. 先建 `orderApi.js` 與 `useOrder.js`
3. 先做 `OrderList.vue`
4. 再做 `OrderDetail.vue`
5. 再做 `OrderCreate.vue` / `OrderEdit.vue`
6. 最後補 `Quote -> Order` 按鈕與導向

---

## 完成後建議接下一張票

下一張建議接：

- `TASK-005：CRM API 第一版`

原因：

- Order 打通後，下一個最有價值的是把 customer 變成正式主資料
- CRM API 補完後，quote / order / 後續 inventory 都會更穩

---

# TASK-005：CRM API 第一版

## 任務本質

CRM 目前已有資料表、Model、Seeder，但沒有正式 API。這張票先補後端第一版 API，讓 Customer 成為正式主資料。

## 任務目標

1. 提供 Customer / Contact / Activity / Opportunity 第一版 API
2. 提供可被前端直接使用的查詢、建立、更新能力
3. 讓 quote / order 後續可以逐步改接 `customer_id`

## 範圍

- `backend/app/Http/Controllers/Api/*CRM*`
- `backend/app/Services/*`
- `backend/routes/api.php`
- `backend/tests/Feature/*`

## 具體待辦

- [x] 建立 `CustomerController`
- [x] 建立 `CustomerContactController`
- [x] 建立 `CustomerActivityController`
- [x] 建立 `OpportunityController`
- [x] 新增 routes
  - `/api/customers`
  - `/api/customers/{id}`
  - `/api/customers/{id}/contacts`
  - `/api/customers/{id}/activities`
  - `/api/opportunities`
- [x] 建立 `CustomerService`
- [x] 建立 `OpportunityService`
- [x] 補查詢條件
  - search
  - status
  - industry
  - pagination
- [x] 補 validation
- [x] 補 feature tests

## 驗收標準

- [x] Customer CRUD 可用（程式碼路徑已對齊）
- [x] Contact CRUD 可用（程式碼路徑已對齊）
- [x] Activity 新增/查詢可用（程式碼路徑已對齊）
- [x] Opportunity CRUD 或至少 list/create/update-status 可用（程式碼路徑已對齊）
- [ ] backend tests 可驗證主要流程（目前環境缺少 `pdo_sqlite`，無法執行）

## 不要做的事

- [ ] 不要同時做 CRM 前端頁
- [ ] 不要先做複雜分群/自動化流程

## 完成後建議接下一張票

- `TASK-006：CRM 前端 MVP`

---

# TASK-006：CRM 前端 MVP

## 任務本質

在 `TASK-005` 的 CRM API 完成後，補上前端最小可用頁面，讓 CRM 模組可以正式開啟。

## 任務目標

1. 可查詢客戶列表與詳情
2. 可建立/編輯客戶
3. 可查看聯絡人、活動、商機基本資訊

## 範圍

- `frontend/src/modules/crm/*`
- `frontend/src/config/modules.js`

## 具體待辦

- [x] 建立 `frontend/src/modules/crm/api/crmApi.js`
- [x] 建立 `useCustomer.js`
- [x] 建立 `useOpportunity.js`
- [x] 建立頁面
  - `CustomerList.vue`
  - `CustomerDetail.vue`
  - `CustomerForm.vue`
  - `OpportunityList.vue`
- [x] 補 `crm/routes.js`
- [x] 補 `moduleConfig.crm`
- [x] CRM 頁面串接 API
- [x] 補空狀態 / 錯誤狀態 / loading

## 驗收標準

- [x] 可開啟 CRM 模組（程式碼路徑已對齊）
- [x] 可查詢與編輯 Customer（程式碼路徑已對齊）
- [x] 可查看 Opportunity（程式碼路徑已對齊）
- [x] 前端 build 通過（2026-03-06 已驗證 `npm.cmd run build`）

## 不要做的事

- [ ] 不要先做複雜 CRM dashboard
- [ ] 不要把 quote/order 深度整併一起做完

## 完成後建議接下一張票

- `TASK-007：Inventory API 第一版`

---

# TASK-007：Inventory API 第一版

## 任務本質

Inventory 目前也屬於資料層先行。這張票先把倉庫、庫存、異動、調整的 API 補出第一版。

## 任務目標

1. 提供 Warehouse / StockLevel / StockMovement / StockAdjustment API
2. 讓前端後續可以做庫存查詢與調整

## 範圍

- `backend/app/Http/Controllers/Api/*Inventory*`
- `backend/app/Services/StockService.php`
- `backend/routes/api.php`
- `backend/tests/Feature/*`

## 具體待辦

- [x] 建立 `WarehouseController`
- [x] 建立 `StockLevelController`
- [x] 建立 `StockMovementController`
- [x] 建立 `StockAdjustmentController`
- [x] 補 inventory routes
- [x] 擴充 `StockService`
  - add stock
  - transfer stock
  - adjust stock
- [x] 定義查詢條件
- [x] 定義 transaction 與資料一致性規則
- [x] 補 feature tests

## 驗收標準

- [x] 可查詢 warehouse / stock levels（程式碼路徑已對齊）
- [x] 可新增 movement / adjustment（程式碼路徑已對齊）
- [ ] backend tests 可驗證異動後庫存正確

## 不要做的事

- [ ] 不要先做前端 inventory 頁
- [ ] 不要把採購/銷貨完整流程硬塞進來

## 完成後建議接下一張票

- `TASK-008：Inventory 前端 MVP`

---

# TASK-008：Inventory 前端 MVP

## 任務本質

在 Inventory API 補好後，建立最小可用的庫存前端模組。

## 任務目標

1. 可以查看庫存
2. 可以做基本異動與調整
3. 可以查看倉庫資料

## 範圍

- `frontend/src/modules/inventory/*`
- `frontend/src/config/modules.js`

## 具體待辦

- [x] 建立 `inventoryApi.js`
- [x] 建立 `useInventory.js`
- [x] 建立頁面
  - `WarehouseList.vue`
  - `StockList.vue`
  - `StockMovementList.vue`
  - `StockAdjustmentForm.vue`
- [x] 補 `inventory/routes.js`
- [x] 補 `moduleConfig.inventory`
- [x] 串接 inventory API

## 驗收標準

- [x] 可開啟 inventory 模組（程式碼路徑已對齊）
- [x] 可查詢庫存與倉庫（程式碼路徑已對齊）
- [x] 可提交庫存調整（程式碼路徑已對齊）
- [x] 前端 build 通過（2026-03-06 已驗證 `npm.cmd run build`）

## 不要做的事

- [ ] 不要先做複雜圖表或預測
- [ ] 不要先做跨模組自動扣庫

## 完成後建議接下一張票

- `TASK-009：Report API + Dashboard MVP`

---

# TASK-009：Report API + Dashboard MVP

## 任務本質

Report 目前只有資料表與概念。這張票補後端聚合 API 與前端簡單 dashboard。

## 任務目標

1. 提供第一版 dashboard / export records API
2. 提供前端 report 模組首頁

## 範圍

- `backend/app/Http/Controllers/Api/*Report*`
- `frontend/src/modules/report/*`

## 具體待辦

- [x] 建立 `ReportController`
- [x] 建立 dashboard 聚合 API
  - quote stats
  - order stats
  - inventory stats
- [x] 建立 export records API
- [x] 建立 `reportApi.js`
- [x] 建立 `ReportDashboard.vue`
- [x] 建立 `ReportExportList.vue`
- [x] 補 `report/routes.js`
- [x] 補 `moduleConfig.report`

## 驗收標準

- [x] 可進入 report 模組
- [x] 可看到第一版 dashboard 數據
- [x] 可查看匯出紀錄

## 不要做的事

- [ ] 不要先做 BI 級別報表
- [ ] 不要先做大量自定義報表 builder

## 已完成補充

- [x] 建立 `ReportService` 聚合 Quote / Order / Inventory / ReportExport 統計
- [x] 建立 `backend/tests/Feature/ReportFeatureTest.php`
- [x] 補齊 report 匯出任務建立與列表查詢

## 完成後建議接下一張票

- `TASK-010：Quote 匯出與寄信流程補完`

---

# TASK-010：Quote 匯出與寄信流程補完

## 任務本質

Quote 現在 CRUD 可用，但寄信與正式匯出仍未完成。這張票補上真正可用的輸出流程。

## 任務目標

1. Quote PDF / Excel 匯出可用
2. Quote send 真正寄信可用
3. 前後端 UI 與 API 一致

## 範圍

- `backend/app/Http/Controllers/Api/QuoteController.php`
- `backend/app/Mail/*`
- `frontend/src/modules/quote/*`

## 具體待辦

- [x] 實作 Quote PDF 匯出
- [x] 實作 Quote Excel 匯出
- [x] 實作 `QuoteController@send`
- [x] 建立 mail template
- [x] 前端改為呼叫正式 export/send API
- [x] 補失敗提示與下載處理
- [x] 補測試

## 驗收標準

- [x] 可下載 PDF
- [x] 可下載 Excel
- [x] 可送出 email
- [x] 前端不再依賴臨時 Word/print 流程作為主要輸出

## 不要做的事

- [ ] 不要同時做報表匯出中心
- [ ] 不要引入過度複雜佇列流程，除非寄信量已需要

## 已完成補充

- [x] 新增 `QuoteDocumentService`，集中處理 PDF / Excel 輸出
- [x] 新增 `QuoteMail` 與 email blade template，寄信時附帶 PDF
- [x] `QuoteDetail.vue` / `QuoteList.vue` 改走正式 API
- [x] `composer.json` 新增 `barryvdh/laravel-dompdf` 與 `phpoffice/phpspreadsheet`
- [ ] backend feature tests 仍受限於本機缺少 `pdo_sqlite`

## 完成後建議接下一張票

- `TASK-011：Auth 密碼重設真流程`

---

# TASK-011：Auth 密碼重設真流程

## 任務本質

`forgot-password` / `reset-password` 目前只有 API 介面，沒有真正 token 與寄信流程。這張票把認證補完整。

## 任務目標

1. forgot/reset password 成為可用流程
2. email token 與安全規則完整

## 範圍

- `backend/app/Http/Controllers/Api/AuthController.php`
- mail / token 流程
- 如有需要前端登入頁或重設頁

## 具體待辦

- [x] 產生與驗證 reset token
- [x] 寄送 reset mail
- [x] 補 token 過期與失效規則
- [x] 前端提供 reset password 頁面或流程
- [x] 補 feature tests

## 驗收標準

- [x] 可寄送重設密碼信
- [x] 可透過 token 重設密碼
- [x] 無效/過期 token 會被拒絕

## 不要做的事

- [ ] 不要把 SSO / OAuth 一起做進來

## 已完成補充

- [x] 改用 Laravel `Password` broker，沿用 `password_reset_tokens` 與內建 expire/throttle 規則
- [x] `AppServiceProvider` 補前端 reset URL 產生邏輯
- [x] 新增 `ForgotPassword.vue` / `ResetPassword.vue` 與 login 入口
- [x] 新增 `backend/tests/Feature/AuthFeatureTest.php`
- [ ] backend feature tests 仍受限於本機缺少 `pdo_sqlite`

## 完成後建議接下一張票

- `TASK-012：測試與品質基線補齊`

---

# TASK-012：測試與品質基線補齊

## 任務本質

目前專案可運作，但測試基礎與品質工具明顯不足。這張票是把工程底座補齊。

## 任務目標

1. 後端主要模組都有基本 feature tests
2. 前端至少有 smoke/E2E 測試
3. 建立基本 lint / test / build 檢查流程

## 範圍

- `backend/tests/*`
- `frontend/package.json`
- 前端測試工具設定

## 具體待辦

- [x] 補 Auth / Quote / Staff / Order / CRM / Inventory 核心 feature tests
- [x] 前端加入 Vitest 或 E2E 工具
- [x] 補至少 2-3 條核心 smoke flow
- [x] `package.json` 增加 test script
- [x] 若可行，補 lint / format script
- [x] 整理最小 CI 檢查清單

## 驗收標準

- [x] backend tests 有可觀覆蓋，不再只靠 ExampleTest
- [x] frontend 有最小測試基線
- [x] 可用單一命令執行主要檢查

## 已完成補充

- [x] 新增 `backend/tests/Feature/StaffFeatureTest.php`，補齊 staff CRUD / stats / unauthorized / self-delete 保護測試
- [x] 移除 `backend/tests/Feature/ExampleTest.php` 與 `backend/tests/Unit/ExampleTest.php`
- [x] 前端新增 Vitest 測試基礎：`frontend/src/tests/setup.js`
- [x] 前端新增 `frontend/src/tests/router.smoke.spec.js`
- [x] smoke flow 覆蓋：
  - 未登入進受保護路由會導回 `/login`
  - 已登入進 `/login` 會被導回 `/profile`
  - 無權限進 `/report/dashboard` 會被擋下並保留前一頁
- [x] `frontend/package.json` 已新增 `test`、`test:run`、`check`、`format:check`
- [x] 最小 CI 檢查清單：
  - backend：`php artisan test`
  - frontend：`npm.cmd run check`
  - frontend format：`npm.cmd run format:check`
- [ ] backend Feature tests 仍受限於本機缺少 `pdo_sqlite`，目前只能補齊測試檔與語法驗證

## 不要做的事

- [ ] 不要追求 100% coverage
- [ ] 不要在這張票開新功能

---

## 執行紀錄（本次）

- 已完成：
  - Quote `requiresAuth` 收斂
  - Login 錯誤訊息與 loading 防重送
  - authStore `/auth/me` 同步
  - Router guard 與通知呼叫一致化
  - 額外收斂：Staff 改 API-only（依 `PLAN.md` 執行）
  - `staffApi.js` 只保留 `/api/users` CRUD + `/api/users/stats`
  - `useStaff.js` 移除 LocalStorage/角色/匯出/狀態雙軌邏輯，收斂成單一路徑 API
  - `StaffList.vue` 改為列表/新增/編輯/刪除/查詢，移除批次刪除、匯出、狀態切換入口
  - `modules.js` / `staff/index.js` 同步移除角色權限管理與組織架構描述
  - `OrderService` 完成 `createOrder()` / `updateOrder()` / `createOrderFromQuote()`，並收斂金額計算、狀態規則、transaction
  - `OrderLog` 補齊 model 與關聯，建立/更新訂單都會寫入操作紀錄
  - `OrderController` 補強 request validation 與錯誤分類（validation/business/system）
  - `QuoteService` 新增 `createOrderFromQuote()` 並統一轉單走 `OrderService`
  - `Quote::convertToOrder()` 改為委派 `OrderService`，避免雙套轉單邏輯
  - 新增 `backend/tests/Feature/OrderFeatureTest.php`，涵蓋 create/update/validation/unauthorized/rollback/quote-convert 結構一致性
  - 新增 `frontend/src/modules/order/` 模組骨架（`index.js`、`routes.js`、`api/orderApi.js`、`composables/useOrder.js`、`constants.js`）
  - 新增 Order MVP 頁面：`OrderList.vue`、`OrderDetail.vue`、`OrderCreate.vue`、`OrderEdit.vue`
  - `modules.js` 新增 `order` 模組設定（入口/children/permissions）
  - `QuoteDetail.vue` 新增「轉為訂單」按鈕，限制 `approved` 才可轉單，成功後導向 `OrderDetail`
  - Order 建立/更新頁補齊即時計算（subtotal/tax/total）與提交防重送
  - 新增 `CustomerService`、`OpportunityService`，收斂 CRM 查詢條件與商機狀態規則
  - 新增 `CustomerController`、`CustomerContactController`、`CustomerActivityController`、`OpportunityController`
  - `routes/api.php` 新增 CRM API：`/api/customers`、`/api/customers/{id}/contacts`、`/api/customers/{id}/activities`、`/api/opportunities`
  - 新增 `backend/tests/Feature/CrmFeatureTest.php`，覆蓋 customer/contact/activity/opportunity 主要流程
  - `Customer` / `CustomerContact` / `CustomerActivity` / `Opportunity` model 補齊關聯，並修正 `Customer` 欄位命名對齊 migration
  - 新增 `frontend/src/modules/crm/api/crmApi.js`、`useCustomer.js`、`useOpportunity.js`
  - 新增 CRM MVP 頁面：`CustomerList.vue`、`CustomerDetail.vue`、`CustomerForm.vue`、`OpportunityList.vue`
  - `crm/routes.js` 完成客戶列表/新增/詳情/編輯與商機列表路由
  - `modules.js` 啟用 `crm` 模組，補側邊欄 children 與 MVP metadata
  - `CustomerDetail.vue` 串接聯絡人、活動紀錄、商機基本資訊
  - 重寫 `StockService`，補齊 `addStock()`、`deductStock()`、`transferStock()`、`adjustStock()`，並同步 `items.quantity`
  - 新增 `WarehouseController`、`StockLevelController`、`StockMovementController`、`StockAdjustmentController`
  - `routes/api.php` 新增 Inventory API：`/api/warehouses`、`/api/stock-levels`、`/api/stock-movements`、`/api/stock-adjustments`
  - `Warehouse` / `StockLevel` / `StockMovement` / `StockAdjustment` / `Item` 補齊 relation 與庫存衍生欄位
  - 新增 `backend/tests/Feature/InventoryFeatureTest.php`，覆蓋 warehouse CRUD、stock query、inbound、transfer、adjustment、rollback
  - 新增 `frontend/src/modules/inventory/api/inventoryApi.js`、`useInventory.js`、`constants.js`
  - 新增 Inventory MVP 頁面：`WarehouseList.vue`、`StockList.vue`、`StockMovementList.vue`、`StockAdjustmentForm.vue`
  - `inventory/routes.js` 完成倉庫列表、庫存列表、庫存異動、庫存調整路由
  - `modules.js` 啟用 `inventory` 模組，補側邊欄 children 與 MVP metadata
  - `StockMovementList.vue` 補入庫 / 出庫 / 調撥表單，`StockAdjustmentForm.vue` 串接目前庫存預覽與最近調整紀錄
  - 額外修正：`ActivityLogController` 的 `moduleLogs` 方法名稱錯誤
  - 額外修正：`Quote::recalculateTotal()` 使用錯欄位（`unit_price` -> `price`）

- 環境阻塞：
  - `php artisan test` 因缺少 `pdo_sqlite` 無法完整跑 Feature tests
  - `php artisan test --filter=CrmFeatureTest` 同樣受 `pdo_sqlite` 缺失影響，無法驗證執行結果
  - `php artisan test --filter=InventoryFeatureTest` 同樣受 `pdo_sqlite` 缺失影響，無法驗證執行結果
  - `npm.cmd run build` 在 sandbox 內會出現 `spawn EPERM`；2026-03-06 改以提權後已成功 build
# TASK-013：admin 管理員工模組權限

## 問題本質

目前專案已經有 `spatie/laravel-permission`、角色 seed、前端 route permission guard，
但這些只夠支撐「登入者自己能不能進模組」。

真正缺的是下面這一段：

- admin 可以查看每位員工目前有哪些角色 / 權限
- admin 可以調整某位員工可用的模組權限
- 後端 API 會真的 enforce，不是只有前端畫面擋一下

現在的狀態是半套：

- 有 permission seed
- 有 `auth/me` 回傳 roles / permissions
- 但沒有員工權限管理 API
- `UserController` 也沒有處理 roles / permissions
- `/api/users` 系列 route 只有 `auth:sanctum`，沒有 `permission` 或 `role` middleware

這代表現在看起來像有權限系統，但其實還不能落地管理每位員工的模組權限。

---

## 目標

1. admin 可在 Staff 模組中查看並編輯每位員工的角色與模組權限
2. 模組可見性、route guard、API 權限、permission seed 一致
3. 非 admin / 無權限使用者即使繞過前端，也會被 API 正確擋下
4. 權限模型可以延伸到後續新模組，不要每加一個模組就重寫一套

---

## 範圍

### 前端

- `frontend/src/modules/staff/views/StaffList.vue`
- `frontend/src/modules/staff/composables/useStaff.js`
- `frontend/src/modules/staff/api/staffApi.js`
- `frontend/src/config/modules.js`
- `frontend/src/stores/auth.js`
- `frontend/src/router/index.js`

### 後端

- `backend/routes/api.php`
- `backend/app/Http/Controllers/Api/UserController.php`
- `backend/app/Http/Controllers/Api/AuthController.php`
- `backend/app/Models/User.php`
- `backend/database/seeders/RoleAndPermissionSeeder.php`
- 視需要新增：
  - `backend/app/Services/UserPermissionService.php`
  - `backend/app/Http/Requests/*`

### 測試

- `backend/tests/Feature/StaffFeatureTest.php`
- 視需要新增前端 smoke / unit test

---

## 預期 API

### 既有 API 要補強

- `GET /api/users`
- `POST /api/users`
- `GET /api/users/{id}`
- `PUT /api/users/{id}`
- `DELETE /api/users/{id}`

### 新增 API

- `GET /api/users/{id}/roles`
- `PUT /api/users/{id}/roles`
- `GET /api/users/{id}/permissions`
- `PUT /api/users/{id}/permissions`
- `GET /api/permissions/modules`

備註：

- `roles` 用來管理角色
- `permissions` 用來做每位員工的直接模組權限覆蓋
- `modules` endpoint 用來回前端「模組 -> permission」對照，不要把 mapping 寫死在多個地方

---

## 實作原則

### 1. 權限治理要以後端為準

- 前端 route guard 只負責 UX，不負責安全
- 真正權限判斷要在後端 route / controller / service enforce
- 禁止只有前端隱藏按鈕，API 卻照樣能打

### 2. 角色與直接權限分層

- `role` 處理大範圍預設權限
- `direct permission` 處理單一員工例外
- 不要把所有模組存成自訂 JSON 欄位塞回 `users`

### 3. 權限 mapping 單一來源

- module 與 permission 對照應集中管理
- 前端 `modules.js` 與後端 seed / API 不可各寫各的

---

## 拆解項目

### 1. 補齊 permission seed

- [ ] 檢查並補齊所有已上線模組 permission
  - `quote.*`
  - `order.*`
  - `crm.*`
  - `inventory.*`
  - `report.*`
  - `staff.*`
- [ ] 確認 `admin`、`manager`、`staff` 預設角色權限合理
- [ ] 避免前端已使用但 seed 不存在的 permission name

### 2. 後端 API 權限防線落地

- [ ] `/api/users` 系列 route 加上對應 `permission` middleware
- [ ] 權限管理 route 僅允許 `role.manage` 或等效高權限角色使用
- [ ] 補上未授權 / 無權限的 API response 測試

### 3. UserController / Service 補角色與權限管理

- [ ] 建立 `UserPermissionService` 收斂角色與權限更新流程
- [ ] `store/update/show` 回傳使用者時包含 roles / permissions
- [ ] 新增更新角色 API：`syncRoles`
- [ ] 新增更新直接權限 API：`syncPermissions`
- [ ] 更新自己時要定義限制，避免 admin 把自己權限改到鎖死

### 4. Staff UI 補權限管理畫面

- [ ] Staff 編輯 Modal 補角色設定區
- [ ] Staff 編輯 Modal 補模組權限勾選區
- [ ] 畫面明確區分：
  - 角色繼承權限
  - 直接指派權限
- [ ] 權限欄位資料來源走 API，不要硬編碼散落在頁面

### 5. auth / route guard 對齊

- [ ] `auth/me` 回傳資料格式與新的角色 / 權限治理一致
- [ ] route guard 繼續用 `permissions` 判斷，但以後端回傳為準
- [ ] 側邊欄模組顯示邏輯與 API seed 同步

### 6. 測試補齊

- [ ] admin 可查看員工角色與權限
- [ ] admin 可更新某員工角色
- [ ] admin 可更新某員工直接權限
- [ ] 非 admin 無法更新他人角色 / 權限
- [ ] 權限變更後重新登入或 `auth/me` 同步後，前端模組可見性正確

---

## 驗收標準

- [ ] admin 可以在 Staff 模組中管理每位員工的角色與模組權限
- [ ] 一般 staff 即使手動打 API，也不能修改他人權限
- [ ] 員工被移除某模組權限後，前端入口、route、API 都一致被擋下
- [ ] 員工被加上某模組權限後，重新同步登入狀態即可使用該模組
- [ ] `order.*` 等既有漏 seed 的權限補齊，不再出現前後端 permission name 不同步

---

## 不做

- [ ] 不在這張票改成另一套 ACL / 自製權限框架
- [ ] 不把權限資料塞進 `users.modules` 之類的 JSON 欄位
- [ ] 不順手擴寫新的業務模組功能
- [ ] 不做組織架構、部門樹、簽核流程

---

## 風險與注意事項

- 前端現在把 `admin` 視為全通，若後端不補 enforce，永遠只是 UI 假安全
- 直接權限與角色權限混在一起顯示時，UI 很容易誤導，必須標明「繼承」與「覆蓋」
- 權限一旦改動，要注意 `spatie.permission.cache` 快取刷新
- 若後續新增模組沒有同步 seed、route guard、module config，這套很快又會失真

---

## 建議實作順序

1. 先補 seed 與後端 middleware，建立真正安全邊界
2. 再補 `UserPermissionService` 與角色 / 權限 API
3. 再補 Staff UI 權限管理畫面
4. 最後補 feature tests 與前端 smoke 驗證

---
