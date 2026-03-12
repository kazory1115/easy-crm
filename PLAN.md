# Easy CRM Engineering Plan

## 1. 計畫目的

這份文件不是任務流水帳，也不是變更紀錄。

這份 `PLAN.md` 的用途是：

- 定義目前專案處於哪個工程階段
- 說明已經完成到什麼程度
- 列出真正阻塞交付的風險
- 排出接下來應該先做什麼

逐張任務細節仍以 [TASK.md](C:\Users\kazo\Desktop\localhostDB\website\easy-crm\TASK.md) 為準。

## 2. 專案目標

Easy CRM 的短期目標不是做完整 ERP，而是建立一套可持續開發的 CRM MVP 基礎。

目前工程上的核心目標有四個：

1. 主要模組都要有可用的前後端主流程
2. 關鍵商業流程要從假資料 / fallback 收斂到真 API
3. 測試、建置、文件要足夠支撐持續交付
4. 後續新功能要建立在一致的模組結構與 Service 邏輯上

## 3. 目前完成度

### 3.1 已完成里程碑

- 認證收斂
  - Quote 路由已受 auth guard 保護
  - `authStore` 已支援 `/api/auth/me` 同步
  - forgot/reset password 已改為 Laravel 正式流程

- Staff 模組收斂
  - 已移除 localStorage fallback
  - 已改為 API-only
  - staff / activity logs 已回到現有後端能力範圍

- Order 模組打通
  - backend `OrderService` 已補完
  - frontend order 模組已建立
  - quote 可轉 order

- CRM / Inventory / Report 已補到 MVP
  - CRM API + 前端頁面可用
  - Inventory API + 前端頁面可用
  - Report dashboard / export records 可用

- Quote 文件流程已補完
  - PDF 匯出
  - Excel 匯出
  - email send

- 測試與品質基線已建立
  - frontend 已有 Vitest smoke tests
  - backend 已補 Auth / Quote / Staff / Order / CRM / Inventory / Report 核心 Feature tests
  - frontend 已有 `build` / `test:run` / `check` / `format:check`

### 3.2 目前可視為可用的模組

- Auth
- Quote
- Staff
- Order
- CRM
- Inventory
- Report

### 3.3 目前不能誤判成已完成的事

- backend 測試環境還沒真正跑通
- 權限與角色 seed 還沒有完整治理
- 前後端整合驗收還沒做完
- 格式化與 lint 只建立了最小基線，還沒全面收斂舊碼

## 4. 工程現況判斷

### 4.1 目前階段

目前不是「從 0 到 1」階段，而是「MVP 主幹已成形，進入可交付化補強階段」。

意思是：

- 功能骨架大致齊了
- 主要流程已不再只是 stub
- 接下來最重要的不是再堆新模組
- 而是把測試環境、權限治理、整合驗收補齊

### 4.2 現在最容易誤踩的錯

- 看到模組可開，就誤以為已可正式交付
- 看到 test 檔很多，就誤以為測試環境已健全
- 看到 admin 可操作，就誤以為權限模型已完成
- 看到 build 可過，就誤以為整合驗收已做完

## 5. 關鍵風險

### 5.1 Backend 測試環境未打通

目前最大的 blocker 是本機 PHP 缺 `pdo_sqlite`。

影響：

- `php artisan test`
- `php artisan test --filter=...`

都會失敗在 sqlite driver，而不是測試案例本身。

這代表：

- 測試檔已補
- 但還沒形成真正可持續驗證的 backend testing baseline

### 5.2 權限治理仍偏 MVP

目前模組雖然大多已可用，但 `order.*`、`crm.*`、`inventory.*`、`report.*` 是否完整由 seed 與 permission model 支撐，還不夠穩。

風險是：

- admin 可用
- 非 admin 行為不一致
- module config、route guard、permission seed 可能不同步

### 5.3 整合驗收不足

雖然多數模組各自完成，但跨模組流程還沒有系統性驗證，例如：

- quote export / send
- quote convert to order
- CRM customer 與 quote / order 關聯
- inventory 異動後對資料一致性的影響

## 6. 接下來的工程優先順序

### Priority 1: 修復 backend testing environment

目標：

- 讓 backend Feature tests 真正可在本機與 CI 跑起來

完成條件：

- PHP 啟用 `pdo_sqlite`
- `php artisan test` 可執行
- 至少核心 test suite 可穩定跑完

### Priority 2: 補齊權限與角色治理

目標：

- 讓模組可見性、route 保護、API 權限、seed 一致

完成條件：

- 補 `RoleAndPermissionSeeder`
- 補主要模組權限
- 驗證 admin / 非 admin 行為一致

### Priority 3: 進行前後端整合驗收

目標：

- 驗證已完成模組真的能串成可交付流程

建議驗收清單：

- quote create -> approve -> export/send
- quote -> order convert -> order detail
- CRM customer -> quote / order 關聯
- inventory movement / adjustment -> stock level 結果

## 7. 建議里程碑

### Milestone A: 測試環境可執行

輸出：

- backend tests 可跑
- frontend check 維持可跑

### Milestone B: 權限模型可落地

輸出：

- permission seed 穩定
- 模組入口與 API 保護一致

### Milestone C: 核心流程整合驗收完成

輸出：

- quote / order / CRM / inventory 至少有一輪整合驗收
- README / PLAN / TASK 三份文件同步

## 8. 建議下一張票

如果以工程風險排序，下一張最合理的是：

- `TASK-013：Backend 測試環境修復（pdo_sqlite / testing DB）`

如果你想先補產品治理而不是測試環境，第二順位是：

- `TASK-013：權限與角色 seed 補齊`

## 9. 文件分工

- [README.md](C:\Users\kazo\Desktop\localhostDB\website\easy-crm\README.md)
  - 專案入口說明
  - 啟動方式
  - 常用命令

- [PLAN.md](C:\Users\kazo\Desktop\localhostDB\website\easy-crm\PLAN.md)
  - 工程計畫
  - 現況判斷
  - 風險與里程碑

- [TASK.md](C:\Users\kazo\Desktop\localhostDB\website\easy-crm\TASK.md)
  - 任務逐張紀錄
  - 完成項與驗收標準
