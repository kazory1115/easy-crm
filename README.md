# Easy CRM

Easy CRM 是一個前後端分離的 CRM 專案。

- 前端：Vue 3 + Vite + Pinia + Vue Router
- 後端：Laravel 12 + Sanctum
- 資料庫：MySQL 8
- 部署開發：Docker Compose

這份 `README.md` 的用途是：

- 說明專案是什麼
- 告訴你怎麼把專案跑起來
- 提供常用開發與測試命令
- 指出重要目錄與環境限制

進度、已完成任務、下一步規劃不放在這裡，請看：

- [PLAN.md](C:\Users\kazo\Desktop\localhostDB\website\easy-crm\PLAN.md)
- [TASK.md](C:\Users\kazo\Desktop\localhostDB\website\easy-crm\TASK.md)

## 專案內容

目前專案已包含以下模組：

- Auth
- Quote
- Staff
- Order
- CRM
- Inventory
- Report

## 目錄結構

- [frontend](C:\Users\kazo\Desktop\localhostDB\website\easy-crm\frontend)
  - Vue 3 前端程式
- [backend](C:\Users\kazo\Desktop\localhostDB\website\easy-crm\backend)
  - Laravel API
- [docker-compose.yml](C:\Users\kazo\Desktop\localhostDB\website\easy-crm\docker-compose.yml)
  - 本機開發容器設定
- [nginx.conf](C:\Users\kazo\Desktop\localhostDB\website\easy-crm\nginx.conf)
  - Nginx 設定

## 本機啟動

### 1. 準備環境檔

```powershell
Copy-Item .env.example .env
Copy-Item backend\.env.example backend\.env
```

### 2. 啟動容器

```powershell
docker compose up -d --build
```

### 3. 初始化後端

```powershell
docker compose exec php php artisan key:generate
docker compose exec php php artisan migrate
docker compose exec php php artisan db:seed
```

### 4. 啟動前端開發伺服器

```powershell
cd frontend
npm install
npm run dev
```

## 常用命令

### Frontend

```powershell
cd frontend
npm.cmd run build
npm.cmd run test:run
npm.cmd run check
npm.cmd run format:check
```

### Backend

```powershell
cd backend
php artisan serve
php artisan test
```

## 主要環境限制

### Backend tests

目前 backend Feature tests 依賴 sqlite testing connection。

如果本機 PHP 沒有啟用 `pdo_sqlite`，以下命令會失敗：

```powershell
cd backend
php artisan test
```

這是測試環境問題，不是功能模組本身一定有錯。

## 開發原則

- Controller 盡量保持薄
- 商業流程收斂到 Service
- 前端模組走 `modules/*` 結構
- 新功能完成後同步更新 `TASK.md`

## 補充

- 專案進度看 [PLAN.md](C:\Users\kazo\Desktop\localhostDB\website\easy-crm\PLAN.md)
- 任務細節看 [TASK.md](C:\Users\kazo\Desktop\localhostDB\website\easy-crm\TASK.md)
