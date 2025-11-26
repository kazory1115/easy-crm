# Easy CRM 安裝說明

## 📋 前置需求

- Docker Desktop (Windows/Mac) 或 Docker Engine (Linux)
- Docker Compose v2.0+
- Git

## 🚀 快速開始

### 1. 複製專案

```bash
git clone <repository-url>
cd easy-crm
```

### 2. 設定環境變數

#### 2.1 設定主專案環境變數

複製環境變數範本：

```bash
cp .env.example .env
```

編輯 `.env` 檔案，修改以下**重要設定**：

```bash
# 🔒 資料庫密碼（必須修改！）
DB_PASSWORD=your_strong_password_here
DB_TEST_PASSWORD=your_strong_password_here
DB_ECIC_PASSWORD=your_strong_password_here

# 🔒 pgAdmin 密碼（必須修改！）
PGADMIN_PASSWORD=your_pgadmin_password_here

# 🔧 可選：自訂 Port（如果預設 port 被占用）
NGINX_PORT=8180
PGADMIN_PORT=5050
DB_EXTERNAL_PORT=5432
DB_TEST_EXTERNAL_PORT=5433
DB_ECIC_EXTERNAL_PORT=5434
```

#### 2.2 設定 Laravel Backend 環境變數

```bash
cp backend/.env.example backend/.env
```

編輯 `backend/.env`，確保資料庫設定與主專案一致：

```bash
DB_CONNECTION=pgsql
DB_HOST=postgres
DB_PORT=5432
DB_DATABASE=easy_crm_db
DB_USERNAME=postgres
DB_PASSWORD=your_strong_password_here  # 與主專案 DB_PASSWORD 相同
```

生成 Laravel 應用程式金鑰：

```bash
docker-compose run --rm php php /var/www/html/backend/artisan key:generate
```

### 3. 啟動服務

```bash
docker-compose up -d
```

### 4. 檢查服務狀態

```bash
docker-compose ps
```

所有服務應該顯示為 `Up` 狀態。

### 5. 執行資料庫遷移（Laravel）

```bash
docker-compose exec php php /var/www/html/backend/artisan migrate
```

### 6. 存取服務

- **前端應用程式**: http://localhost:8080
- **pgAdmin (資料庫管理)**: http://localhost:5050

#### 6.1 登入 pgAdmin

1. 開啟瀏覽器訪問 http://localhost:5050
2. 使用您在 `.env` 中設定的 `PGADMIN_EMAIL` 和 `PGADMIN_PASSWORD` 登入
3. 點擊「Add New Server」新增伺服器連線
4. 在 General 頁籤輸入名稱（例如：Easy CRM Main）
5. 在 Connection 頁籤輸入：
   - Host name/address: `postgres`
   - Port: `5432`
   - Username: `postgres`
   - Password: 您設定的 `DB_PASSWORD`

## 🔧 常見操作

### 停止服務

```bash
docker-compose down
```

### 停止服務並刪除資料

⚠️ **警告：這會刪除所有資料庫資料！**

```bash
docker-compose down -v
```

### 重新建置容器

當修改 `dockerfile` 或需要更新依賴時：

```bash
docker-compose up -d --build
```

### 查看日誌

查看所有服務日誌：
```bash
docker-compose logs -f
```

查看特定服務日誌：
```bash
docker-compose logs -f php
docker-compose logs -f postgres
docker-compose logs -f nginx
```

### 進入容器終端

進入 PHP 容器：
```bash
docker-compose exec php bash
```

進入 PostgreSQL 容器：
```bash
docker-compose exec postgres bash
```

### 執行 Laravel Artisan 指令

```bash
docker-compose exec php php /var/www/html/backend/artisan <command>
```

範例：
```bash
# 執行資料庫遷移
docker-compose exec php php /var/www/html/backend/artisan migrate

# 建立新的遷移檔案
docker-compose exec php php /var/www/html/backend/artisan make:migration create_users_table

# 清除快取
docker-compose exec php php /var/www/html/backend/artisan cache:clear
```

### 備份資料庫

備份主資料庫：
```bash
docker-compose exec postgres pg_dump -U postgres easy_crm_db > backup_$(date +%Y%m%d_%H%M%S).sql
```

### 還原資料庫

```bash
docker-compose exec -T postgres psql -U postgres easy_crm_db < backup_20250101_120000.sql
```

## ⚠️ 注意事項

### 安全性

1. **絕對不要提交 `.env` 檔案到 Git**
   - `.gitignore` 已經設定忽略 `.env`
   - 檢查：`git status` 確保 `.env` 不在追蹤列表中

2. **務必修改所有預設密碼**
   - 使用強密碼（至少 16 個字元，包含大小寫字母、數字、特殊符號）
   - 不要在多個服務中使用相同密碼

3. **生產環境設定**
   - 設定 `APP_DEBUG=false`
   - 設定 `APP_ENV=production`
   - 使用 HTTPS

### Port 衝突

如果遇到 port 已被占用的錯誤，修改 `.env` 中的 port 設定：

```bash
# 範例：改用其他 port
NGINX_PORT=8090
PGADMIN_PORT=5051
DB_EXTERNAL_PORT=5435
```

修改後重新啟動：
```bash
docker-compose down
docker-compose up -d
```

### 資料庫連線失敗

1. 檢查 `.env` 和 `backend/.env` 中的資料庫設定是否一致
2. 確認資料庫容器已啟動：`docker-compose ps`
3. 查看資料庫日誌：`docker-compose logs postgres`
4. 檢查 healthcheck 狀態：`docker inspect easy-crm-postgres`

## 🐛 故障排除

### 問題：容器無法啟動

**解決方法**：
```bash
# 查看詳細錯誤訊息
docker-compose logs

# 重新建置
docker-compose down
docker-compose up -d --build
```

### 問題：Permission denied 錯誤

**解決方法**：
```bash
# 修正權限
docker-compose exec php chown -R www-data:www-data /var/www/html
```

### 問題：資料庫連線被拒絕

**解決方法**：
1. 確認資料庫容器正在運行：`docker-compose ps`
2. 等待資料庫完全啟動（約 10-15 秒）
3. 檢查 healthcheck：`docker inspect easy-crm-postgres | grep Health`

### 問題：Laravel 提示 "No application encryption key"

**解決方法**：
```bash
docker-compose exec php php /var/www/html/backend/artisan key:generate
```

## 📊 系統架構

```
┌─────────────┐
│   Browser   │
└──────┬──────┘
       │ :8180
       ▼
┌─────────────┐
│    Nginx    │
└──────┬──────┘
       │
       ▼
┌─────────────┐      ┌──────────────────┐
│  PHP-FPM    │─────▶│  PostgreSQL (x3) │
│  (Laravel)  │      │  - Main: 5432    │
└─────────────┘      │  - Test: 5433    │
                     │  - ECIC: 5434    │
                     └──────────────────┘
                              │
                              ▼
                     ┌──────────────────┐
                     │     pgAdmin      │
                     │       :5050      │
                     └──────────────────┘
```

## 📚 延伸閱讀

- [Docker Compose 文件](https://docs.docker.com/compose/)
- [PostgreSQL 官方文件](https://www.postgresql.org/docs/)
- [Laravel 文件](https://laravel.com/docs)
- [pgAdmin 文件](https://www.pgadmin.org/docs/)

## 🆘 需要協助？

如果遇到問題，請：

1. 查看日誌：`docker-compose logs -f`
2. 檢查容器狀態：`docker-compose ps`
3. 參考本文件的「故障排除」章節
4. 提交 Issue 到專案 GitHub 頁面

---

_文件版本: 1.0_
_最後更新: 2025-11-07_
_維護者: Easy CRM Team_
