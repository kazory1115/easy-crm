# Easy CRM - 企業客戶關係管理系統

<div align="center">

![Version](https://img.shields.io/badge/version-3.0.0--dev-blue.svg)
![License](https://img.shields.io/badge/license-MIT-green.svg)
![Vue](https://img.shields.io/badge/Vue-3.5.13-brightgreen.svg)
![Laravel](https://img.shields.io/badge/Laravel-12.x-red.svg)
![PostgreSQL](https://img.shields.io/badge/PostgreSQL-16-blue.svg)
![Progress](https://img.shields.io/badge/backend-quote%20API%2075%25-success.svg)

**一個現代化、模組化的企業 CRM 解決方案**

[快速開始](#-快速開始) • [功能特色](#-功能特色) • [技術架構](#-技術架構) • [文件](#-文件)

</div>

---

## 📋 專案概述

Easy CRM 是一個基於 **Vue 3 + Laravel + PostgreSQL** 的全功能企業客戶關係管理系統，採用模組化設計，提供：

- 📝 **報價單管理** - 靈活的自定義報價範本系統
- 📦 **進銷存管理** - 完整的庫存與交易記錄
- 👥 **客戶關係管理** - 客戶資料、互動歷程追蹤
- 👔 **員工管理** - 角色權限、員工資料管理
- 📊 **報表中心** - 多維度數據分析與視覺化

---

## 🚀 功能特色

### ✨ 核心功能

| 模組                      | 功能描述                                | 後端狀態          | 前端狀態      |
| ------------------------- | --------------------------------------- | ----------------- | ------------- |
| 🧾 **報價單 (Quote)**     | CRUD API、狀態管理、統計、批次操作      | 🟢 完成 75%       | 🟡 LocalStorage |
|                           | ✅ 完整 API、自動編號、操作紀錄         |                   |               |
|                           | 🔴 PDF/Excel 匯出待實作                 |                   |               |
| 📋 **範本管理 (Template)** | 自定義範本、動態欄位（5 種類型）        | 🟢 完成 100%      | 🟡 LocalStorage |
| 📦 **商品管理 (Item)**     | 商品資料、價格、單位、分類              | 🟡 資料表完成     | 🟡 LocalStorage |
| 👥 **客戶管理 (Customer)** | 客戶資料、聯絡資訊、統編                | 🟡 資料表完成     | 🔲 規劃中     |
| 📦 **進銷存 (Inventory)**  | 庫存追蹤、進銷記錄、盤點                | 🔲 規劃中         | 🔲 規劃中     |
| 👔 **員工管理 (Staff)**    | 組織架構、角色權限、績效追蹤            | 🔲 規劃中         | 🔲 規劃中     |
| 📊 **報表中心 (Report)**   | 銷售報表、庫存報表、自定義報表          | 🔲 規劃中         | 🔲 規劃中     |

### 🎯 技術亮點

- 🏗️ **模組化架構** - 前後端分離，模組獨立開發與部署
- 🔐 **完整權限控制** - 基於角色的訪問控制（RBAC）
- 📱 **響應式設計** - 完美支援桌面與移動裝置
- 🚀 **高效能** - PostgreSQL + Redis 快取加速
- 🐳 **容器化部署** - Docker Compose 一鍵啟動
- 🔒 **環境變數管理** - 安全的配置管理方案
- 📝 **完整操作紀錄** - 雙層日誌系統（模組 Log + 全域 ActivityLog）✨ **NEW**
- 🔄 **自動化追蹤** - LogsActivity Trait 自動記錄 CRUD 操作 ✨ **NEW**
- 🗑️ **軟刪除機制** - 資料可恢復，操作可追溯 ✨ **NEW**

---

## 🛠️ 技術架構

### 前端技術棧

- **框架**: Vue 3.5.13 (Composition API)
- **建置工具**: Vite 6.3.5
- **路由**: Vue Router 4.5.1
- **狀態管理**: Pinia (計畫中)
- **UI 框架**: Tailwind CSS
- **HTTP 客戶端**: Axios

### 後端技術棧

- **框架**: Laravel 12.x
- **資料庫**: PostgreSQL 16 Alpine
- **API 風格**: RESTful API
- **認證**: Laravel Sanctum
- **快取**: Redis (計畫中)

### 基礎設施

- **容器化**: Docker + Docker Compose
- **Web Server**: Nginx
- **PHP 版本**: PHP 8.2-FPM
- **資料庫管理**: pgAdmin 4

---

## 🔌 API 端點 (已完成)

### 報價單 API

| 方法   | 端點                          | 描述               | 狀態 |
| ------ | ----------------------------- | ------------------ | ---- |
| GET    | `/api/quotes`                 | 取得報價單列表     | ✅   |
| POST   | `/api/quotes`                 | 建立報價單         | ✅   |
| GET    | `/api/quotes/{id}`            | 取得單一報價單     | ✅   |
| PUT    | `/api/quotes/{id}`            | 更新報價單         | ✅   |
| DELETE | `/api/quotes/{id}`            | 刪除報價單         | ✅   |
| POST   | `/api/quotes/batch-delete`    | 批次刪除           | ✅   |
| PATCH  | `/api/quotes/{id}/status`     | 更新狀態           | ✅   |
| POST   | `/api/quotes/{id}/send`       | 發送報價單         | ✅   |
| GET    | `/api/quotes/stats`           | 取得統計資料       | ✅   |
| GET    | `/api/quotes/{id}/pdf`        | 匯出 PDF           | 🔴   |
| GET    | `/api/quotes/{id}/excel`      | 匯出 Excel         | 🔴   |
| POST   | `/api/quotes/batch-export`    | 批次匯出           | 🔴   |

### 範本 API

| 方法   | 端點                    | 描述           | 狀態 |
| ------ | ----------------------- | -------------- | ---- |
| GET    | `/api/templates`        | 取得範本列表   | ✅   |
| POST   | `/api/templates`        | 建立範本       | ✅   |
| GET    | `/api/templates/{id}`   | 取得單一範本   | ✅   |
| PUT    | `/api/templates/{id}`   | 更新範本       | ✅   |
| DELETE | `/api/templates/{id}`   | 刪除範本       | ✅   |

### API 功能特色

- ✅ **分頁與排序** - 支援自定義 per_page、sort_by、sort_order
- ✅ **搜尋與篩選** - 支援關鍵字搜尋、狀態篩選、日期範圍
- ✅ **關聯載入** - 自動載入關聯資料（客戶、項目、建立者等）
- ✅ **交易處理** - 使用資料庫 Transaction 確保資料一致性
- ✅ **錯誤處理** - 統一的錯誤回應格式
- ✅ **操作紀錄** - 自動記錄所有 CRUD 操作

---

## 📦 前置需求

在開始之前，請確保您的系統已安裝：

- ✅ [Docker Desktop](https://www.docker.com/products/docker-desktop) (Windows/Mac) 或 Docker Engine (Linux)
- ✅ [Docker Compose](https://docs.docker.com/compose/install/) v2.0+
- ✅ [Git](https://git-scm.com/downloads)

---

## 🚀 快速開始

### 1. 複製專案

```bash
git clone https://github.com/your-username/easy-crm.git
cd easy-crm
```

### 2. 環境設定

#### 2.1 設定主專案環境變數

```bash
# 複製環境變數範本
cp .env.example .env
```

編輯 `.env` 檔案，**務必修改以下密碼**：

```bash
# 🔒 資料庫密碼（必須修改！）
DB_PASSWORD=your_strong_password_here
DB_TEST_PASSWORD=your_strong_password_here
DB_ECIC_PASSWORD=your_strong_password_here

# 🔒 pgAdmin 密碼（必須修改！）
PGADMIN_PASSWORD=your_pgadmin_password_here
```

#### 2.2 設定 Laravel Backend 環境變數

```bash
# 複製 Laravel 環境變數範本
cp backend/.env.example backend/.env
```

編輯 `backend/.env`，確保資料庫密碼與主專案一致：

```bash
DB_CONNECTION=pgsql
DB_HOST=postgres
DB_PORT=5432
DB_DATABASE=easy_crm_db
DB_USERNAME=postgres
DB_PASSWORD=your_strong_password_here  # 與主專案 DB_PASSWORD 相同
```

### 3. 啟動服務

```bash
# 建置並啟動所有容器
docker-compose up -d --build
```

### 4. 初始化應用程式

```bash
# 生成 Laravel 應用程式金鑰
docker-compose exec php php /var/www/html/backend/artisan key:generate

# 執行資料庫遷移
docker-compose exec php php /var/www/html/backend/artisan migrate

# 執行資料填充（可選）
docker-compose exec php php /var/www/html/backend/artisan db:seed
```

### 5. 檢查服務狀態

```bash
docker-compose ps
```

所有服務應該顯示為 `Up` 狀態。

### 6. 訪問應用程式

- 🌐 **前端應用**: http://localhost:8180
- 🗄️ **pgAdmin**: http://localhost:5050
- 📡 **API 文件**: http://localhost:8180/api/documentation (計畫中)

---

## 🔧 常用指令

### Docker 容器管理

```bash
# 啟動所有服務
docker-compose up -d

# 停止所有服務
docker-compose down

# 重新建置容器
docker-compose up -d --build

# 查看容器狀態
docker-compose ps

# 查看日誌（所有服務）
docker-compose logs -f

# 查看特定服務日誌
docker-compose logs -f php
docker-compose logs -f postgres
docker-compose logs -f nginx
```

### Laravel Artisan 指令

```bash
# 執行資料庫遷移
docker-compose exec php php /var/www/html/backend/artisan migrate

# 建立新的 Model
docker-compose exec php php /var/www/html/backend/artisan make:model Customer -m

# 建立新的 Controller
docker-compose exec php php /var/www/html/backend/artisan make:controller Api/CustomerController --api

# 清除快取
docker-compose exec php php /var/www/html/backend/artisan cache:clear

# 查看路由列表
docker-compose exec php php /var/www/html/backend/artisan route:list
```

### 資料庫管理

```bash
# 進入 PostgreSQL 容器
docker-compose exec postgres bash

# 連線到資料庫
docker-compose exec postgres psql -U postgres easy_crm_db

# 備份資料庫
docker-compose exec postgres pg_dump -U postgres easy_crm_db > backup_$(date +%Y%m%d_%H%M%S).sql

# 還原資料庫
docker-compose exec -T postgres psql -U postgres easy_crm_db < backup_20250101_120000.sql
```

### 前端開發

```bash
# 進入前端目錄
cd frontend

# 安裝依賴
npm install

# 啟動開發伺服器
npm run dev

# 建置生產版本
npm run build

# 預覽生產建置
npm run preview
```

---

## 📚 專案結構

```
easy-crm/
├── frontend/                    # Vue 3 前端專案
│   ├── src/
│   │   ├── modules/            # 模組化功能
│   │   │   ├── quote/          # 報價單模組
│   │   │   ├── inventory/      # 進銷存模組
│   │   │   ├── crm/            # 客戶管理模組
│   │   │   ├── staff/          # 員工管理模組
│   │   │   └── report/         # 報表中心模組
│   │   ├── components/         # 共用元件
│   │   ├── layouts/            # 版面配置
│   │   ├── router/             # 路由配置
│   │   ├── stores/             # Pinia 狀態管理
│   │   └── utils/              # 工具函式
│   └── package.json
│
├── backend/                     # Laravel 後端 API
│   ├── app/
│   │   ├── Http/
│   │   │   └── Controllers/
│   │   │       └── Api/
│   │   │           ├── QuoteController.php       ✨ NEW - 報價單 API
│   │   │           └── TemplateController.php    ✨ NEW - 範本 API
│   │   ├── Models/             # Eloquent 模型
│   │   │   ├── Quote.php, QuoteItem.php, QuoteLog.php      ✨ NEW
│   │   │   ├── Template.php, TemplateField.php, TemplateLog.php ✨ NEW
│   │   │   ├── Customer.php, CustomerLog.php     ✨ NEW
│   │   │   ├── Item.php, ItemLog.php             ✨ NEW
│   │   │   └── ActivityLog.php, UserLog.php      ✨ NEW
│   │   ├── Traits/             ✨ NEW
│   │   │   └── LogsActivity.php # 自動操作紀錄追蹤
│   │   └── Services/           # 業務邏輯服務
│   ├── config/
│   │   └── cors.php            ✨ NEW - CORS 跨域配置
│   ├── database/
│   │   ├── migrations/         # 資料庫遷移
│   │   │   ├── *_create_quotes_table.php         ✨ NEW
│   │   │   ├── *_create_templates_table.php      ✨ NEW
│   │   │   ├── *_create_customers_table.php      ✨ NEW
│   │   │   ├── *_create_items_table.php          ✨ NEW
│   │   │   └── *_create_activity_logs_table.php  ✨ NEW (共 12 張表)
│   │   └── seeders/            # 資料填充
│   └── routes/
│       └── api.php             # API 路由定義 (已配置報價單、範本路由)
│
├── docker-compose.yml           # Docker 編排配置
├── dockerfile                   # PHP 容器定義
├── nginx.conf                   # Nginx 配置
├── .env.example                # 環境變數範本
├── .gitignore                  # Git 忽略清單
├── README.md                   # 本文件
└── project.md                  # 專案詳細計畫書
```

---

## 🔐 安全性注意事項

### ⚠️ 重要提醒

1. **絕對不要提交 `.env` 檔案到版本控制**

   - `.gitignore` 已設定忽略所有 `.env` 檔案
   - 確認：`git status` 不應顯示 `.env`

2. **使用強密碼**

   - 至少 16 個字元
   - 包含大小寫字母、數字、特殊符號
   - 不要在多個服務使用相同密碼

3. **生產環境設定**

   ```bash
   APP_ENV=production
   APP_DEBUG=false
   ```

4. **定期更新依賴**

   ```bash
   # 更新 Composer 套件
   docker-compose exec php composer update

   # 更新 NPM 套件
   cd frontend && npm update
   ```

---

## 🐛 故障排除

### 常見問題

<details>
<summary><b>問題：Port 已被占用</b></summary>

**解決方法**：修改 `.env` 中的 port 設定

```bash
NGINX_PORT=8090
PGADMIN_PORT=5051
DB_EXTERNAL_PORT=5435
```

然後重啟服務：

```bash
docker-compose down
docker-compose up -d
```

</details>

<details>
<summary><b>問題：容器無法啟動</b></summary>

**解決方法**：

```bash
# 查看詳細錯誤訊息
docker-compose logs

# 完全清理並重建
docker-compose down -v
docker-compose up -d --build
```

</details>

<details>
<summary><b>問題：資料庫連線失敗</b></summary>

**檢查清單**：

1. 確認 `.env` 和 `backend/.env` 密碼一致
2. 等待資料庫完全啟動（約 10-15 秒）
3. 檢查容器狀態：`docker-compose ps`
4. 查看資料庫日誌：`docker-compose logs postgres`
5. 檢查 healthcheck：`docker inspect easy-crm-postgres`
</details>

<details>
<summary><b>問題：Laravel 提示 "No application encryption key"</b></summary>

**解決方法**：

```bash
docker-compose exec php php /var/www/html/backend/artisan key:generate
```

</details>

---

## 📖 文件

- 📘 [專案詳細計畫書](./project.md) - 完整的開發規劃與架構設計
- 📗 [前端開發指南](./frontend/README.md) - Vue 3 模組化開發說明
- 📕 [後端 API 文件](./backend/README.md) - Laravel API 設計規範
- 📙 [部署指南](./DEPLOYMENT.md) - 生產環境部署說明（計畫中）

---

## 🗺️ 開發路線圖

### v3.0.0 (開發中) - CRM 核心功能 🚀

#### ✅ Phase 1: 基礎架構 (已完成)

- [x] **前端模組化架構**
  - [x] Vue 3 + Vite 模組化設計
  - [x] 報價單前端 UI (LocalStorage 模式)
  - [x] 前端認證流程與 UI (模擬)

- [x] **後端 API 基礎建設** ✨ **NEW**
  - [x] Laravel 12 專案架構
  - [x] PostgreSQL 資料庫連接
  - [x] CORS 跨域配置
  - [x] 認證授權系統（Laravel Sanctum）

- [x] **操作紀錄系統** ✨ **NEW**
  - [x] LogsActivity Trait 自動追蹤
  - [x] 雙層日誌架構 (模組 Log + ActivityLog)
  - [x] IP、User Agent 記錄
  - [x] 軟刪除與資料恢復

#### 🟢 Phase 2: 報價單模組 (後端完成 75%)

- [x] **資料庫設計** ✨ **NEW**
  - [x] quotes, quote_items, quote_logs 資料表
  - [x] 自動編號機制
  - [x] 狀態管理 (draft/sent/approved/rejected)

- [x] **核心 API** ✨ **NEW**
  - [x] 完整 CRUD API
  - [x] 批次操作 (批次刪除)
  - [x] 狀態管理 API
  - [x] 統計資料 API
  - [x] 自動金額計算

- [ ] **進階功能**
  - [ ] PDF 匯出 (待實作)
  - [ ] Excel 匯出 (待實作)
  - [ ] 批次匯出 (待實作)
  - [ ] 郵件發送整合
  - [ ] LocalStorage → API 遷移 (前端)
  - [ ] 多人協作功能
  - [ ] 版本控制

#### 🟢 Phase 3: 範本管理 (後端完成 100%) ✨ **NEW**

- [x] **範本系統**
  - [x] templates, template_fields, template_logs 資料表
  - [x] 完整 CRUD API
  - [x] 動態欄位管理 (支援 5 種類型)
  - [x] 分類與類型管理
  - [x] 使用次數統計

#### 🟡 Phase 4: 基礎資料模組 (資料表完成)

- [x] **客戶管理資料表** ✨ **NEW**
  - [x] customers, customer_logs 資料表
  - [x] Model 與關聯定義
  - [ ] CustomerController API (待實作)

- [x] **商品管理資料表** ✨ **NEW**
  - [x] items, item_logs 資料表
  - [x] Model 與關聯定義
  - [ ] ItemController API (待實作)

### v3.1.0 - 進銷存系統

- [ ] 商品管理
- [ ] 庫存追蹤
- [ ] 進銷記錄
- [ ] 盤點功能

### v3.2.0 - 員工與報表

- [ ] 員工管理
- [ ] 角色權限
- [ ] 報表中心
- [ ] 數據視覺化

---

## 🤝 貢獻指南

我們歡迎所有形式的貢獻！

1. Fork 本專案
2. 建立功能分支 (`git checkout -b feature/AmazingFeature`)
3. 提交變更 (`git commit -m 'Add some AmazingFeature'`)
4. 推送到分支 (`git push origin feature/AmazingFeature`)
5. 開啟 Pull Request

---

## 📄 授權條款

本專案採用 MIT 授權條款 - 詳見 [LICENSE](LICENSE) 檔案

---

## 📞 聯絡方式

- **專案負責人**: kazlab
- **Email**: your-email@example.com
- **Issues**: [GitHub Issues](https://github.com/your-username/easy-crm/issues)

---

## 🙏 致謝

感謝所有為本專案做出貢獻的開發者！

---

<div align="center">

**[⬆ 回到頂部](#easy-crm---企業客戶關係管理系統)**

Made with ❤️ by Easy CRM Team

_最後更新: 2025-01-26_
_v3.0.0-dev: 報價單後端 API 完成 75%，範本管理完成 100%_

</div>
