# Easy CRM (MVP)

Easy CRM 是一套以 Vue 3 + Laravel + MySQL 組成的輕量 CRM MVP，前端採模組化設計，後端提供 REST API 與活動紀錄。

## MVP 功能
- 報價管理：報價單 CRUD、狀態流轉（draft/sent/approved/rejected）
- 報價品項與範本：品項 CRUD、範本 CRUD
- 員工/帳號：使用者 CRUD、基本統計
- 活動紀錄查詢
- 驗證登入：登入/登出、變更密碼（Sanctum）

## 非 MVP / 規劃中
- 客戶管理（CRM）、庫存、報表模組：前端模組已預留但停用
- 報價 PDF/Excel 匯出、郵件發送：後端保留 TODO

## 技術棧
- Frontend: Vue 3, Vite, Pinia, Vue Router, Axios
- Backend: Laravel 12, MySQL 8, Sanctum
- Infra: Nginx, PHP-FPM, Docker Compose

## 目錄概覽
- `frontend/` 前端（modules 架構）
- `backend/` Laravel API
- `docker-compose.yml` / `dockerfile` / `nginx.conf`

## 快速啟動（Docker MVP）

### 1) 環境變數
```bash
cp .env.example .env
cp backend/.env.example backend/.env
```

請調整 `.env` 與 `backend/.env` 的 DB 密碼與連線設定。

### 2) 啟動服務
```bash
docker compose up -d --build
```

### 3) Laravel 初始化
```bash
docker compose exec php php artisan key:generate
docker compose exec php php artisan migrate
docker compose exec php php artisan db:seed
```

### 4) 前端
- 開發模式：`cd frontend && npm install && npm run dev`
- 生產模式：`npm run build`，Nginx 會讀取 `frontend/dist`

### 5) 入口
- API: http://localhost:8180/api/ping
- 前端開發：http://localhost:5173
- 前端（Nginx）：http://localhost:8180

## API 範圍（MVP）
- Auth: `POST /api/auth/login`, `POST /api/auth/logout`, `GET /api/auth/me`
- Quotes: `GET /api/quotes`, `POST /api/quotes`, `GET /api/quotes/{id}`, `PUT /api/quotes/{id}`, `DELETE /api/quotes/{id}`
- Quote Items: `GET /api/quote-items`, `POST /api/quote-items`, `PUT /api/quote-items/{id}`, `DELETE /api/quote-items/{id}`
- Templates: `GET /api/templates`, `POST /api/templates`, `PUT /api/templates/{id}`, `DELETE /api/templates/{id}`
- Users: `GET /api/users`, `POST /api/users`, `PUT /api/users/{id}`, `DELETE /api/users/{id}`
- Activity Logs: `GET /api/activity-logs`, `GET /api/activity-logs/{id}`

## Root .env 參數
- `TIMEZONE`
- `NGINX_PORT`
- `PHP_MEMORY_LIMIT`
- `PHP_UPLOAD_MAX_FILESIZE`
- `PHP_POST_MAX_SIZE`
- `DB_CONNECTION`
- `DB_HOST`
- `DB_PORT`
- `DB_DATABASE`
- `DB_USERNAME`
- `DB_PASSWORD`
- `DB_ROOT_PASSWORD`
- `DB_EXTERNAL_PORT`

## 開發備註
- 報價模組可切換 LocalStorage：`frontend/src/modules/quote/composables/useQuote.js` 的 `dataSource`
- 員工模組預設走 API：`frontend/src/modules/staff/composables/useStaff.js` 的 `USE_API = true`
