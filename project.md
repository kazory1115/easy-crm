# Easy CRM Docker 環境變數改造計畫

## 📋 專案概述

本計畫旨在將現有的 Docker 配置改造為使用環境變數（.env）來管理所有 port 設定和敏感資料，提升安全性與可維護性。

### 目前架構

- **Backend**: Laravel (PHP 8.2-FPM)
- **Frontend**: Vue 3 + Vite
- **Web Server**: Nginx
- **Database**: MariaDB 10.11.7 (3 個實例)
- **管理工具**: phpMyAdmin

---

## 🎯 改造目標

### 1. 安全性提升
- ✅ 將所有敏感資料（密碼、API Key）移至 `.env` 檔案
- ✅ 確保 `.env` 不被提交到版本控制系統
- ✅ 提供 `.env.example` 作為範本

### 2. 彈性配置
- ✅ 所有 port 設定可透過環境變數調整
- ✅ 資料庫配置（名稱、密碼、使用者）可彈性修改
- ✅ 時區設定可配置

### 3. 多環境支援
- ✅ 支援開發、測試、生產環境切換
- ✅ 不同環境可使用不同的配置

---

## 📁 檔案結構規劃

```
easy-crm/
├── .env                    # 實際使用的環境變數（不提交到 Git）
├── .env.example            # 環境變數範本（提交到 Git）
├── .gitignore              # 確保 .env 被忽略
├── docker-compose.yml      # 修改為使用環境變數
├── nginx.conf              # Nginx 配置（部分需要範本化）
├── nginx.conf.template     # Nginx 配置範本（新增）
├── dockerfile              # PHP 容器配置
├── docker-entrypoint.sh    # 容器啟動腳本（新增，用於處理環境變數）
├── backend/                # Laravel 後端
│   └── .env                # Laravel 專用環境變數
├── frontend/               # Vue 前端
└── www/                    # Web 根目錄
```

---

## 🔧 詳細實作步驟

### Step 1: 建立 `.env.example` 範本檔案

建立一個包含所有必要環境變數的範本檔案，供開發者參考。

**檔案位置**: `C:\Users\kazo\Desktop\easy-crm\.env.example`

**內容規劃**:

```bash
# ===========================================
# Docker Compose 環境變數配置
# ===========================================

# ------------------------------------
# 應用程式基本設定
# ------------------------------------
APP_NAME=EasyCRM
APP_ENV=local
APP_DEBUG=true
TIMEZONE=Asia/Taipei

# ------------------------------------
# Nginx 服務設定
# ------------------------------------
NGINX_PORT=8080

# ------------------------------------
# 資料庫設定 - 主資料庫 (MariaDB)
# ------------------------------------
DB_HOST=mariadb
DB_PORT=3306
DB_EXTERNAL_PORT=3306
DB_DATABASE=localhostDB
DB_USERNAME=root
DB_PASSWORD=your_secure_password_here
DB_CHARSET=utf8mb4
DB_COLLATION=utf8mb4_unicode_ci

# ------------------------------------
# 資料庫設定 - 測試資料庫 (MariaDB Test)
# ------------------------------------
DB_TEST_HOST=mariadb_test
DB_TEST_PORT=3306
DB_TEST_EXTERNAL_PORT=3308
DB_TEST_DATABASE=localhost_test
DB_TEST_USERNAME=root
DB_TEST_PASSWORD=your_secure_password_here

# ------------------------------------
# 資料庫設定 - ECIC 資料庫 (MariaDB ECIC)
# ------------------------------------
DB_ECIC_HOST=mariadbEcic
DB_ECIC_PORT=3306
DB_ECIC_EXTERNAL_PORT=3307
DB_ECIC_DATABASE=localhostEcic
DB_ECIC_USERNAME=root
DB_ECIC_PASSWORD=your_secure_password_here

# ------------------------------------
# phpMyAdmin 設定
# ------------------------------------
PMA_PORT=8081
PMA_HOST=mariadb
PMA_USER=root
PMA_PASSWORD=your_secure_password_here

# ------------------------------------
# PHP-FPM 設定
# ------------------------------------
PHP_MEMORY_LIMIT=256M
PHP_MAX_EXECUTION_TIME=300
PHP_UPLOAD_MAX_FILESIZE=20M
PHP_POST_MAX_SIZE=20M

# ------------------------------------
# Laravel Backend 設定
# ------------------------------------
BACKEND_APP_KEY=
BACKEND_APP_URL=http://localhost:8080

# ------------------------------------
# Frontend 設定
# ------------------------------------
VITE_API_BASE_URL=http://localhost:8080/api
```

---

### Step 2: 修改 `docker-compose.yml`

將所有硬編碼的值改為使用環境變數。

**修改重點**:

1. **引入環境變數檔案**
   ```yaml
   env_file:
     - .env
   ```

2. **Port 對應改為環境變數**
   ```yaml
   # 原本
   ports:
     - '8080:80'

   # 改為
   ports:
     - '${NGINX_PORT}:80'
   ```

3. **資料庫密碼改為環境變數**
   ```yaml
   # 原本
   MYSQL_ROOT_PASSWORD: 1234

   # 改為
   MYSQL_ROOT_PASSWORD: ${DB_PASSWORD}
   ```

4. **完整修改後的範例**（詳見下方完整檔案）

---

### Step 3: 修改 `nginx.conf`

Nginx 本身不直接支援環境變數讀取，需要透過以下兩種方式處理：

#### 方案 A: 使用 `envsubst` (建議)

1. 將 `nginx.conf` 改名為 `nginx.conf.template`
2. 在範本中使用環境變數語法：`${VARIABLE_NAME}`
3. 建立啟動腳本，在容器啟動時將範本轉換為實際配置

**nginx.conf.template 範例**:
```nginx
server {
    listen 80;
    server_name localhost;

    root /var/www/html;
    index index.php index.html index.htm;

    location / {
        try_files $uri $uri/ =404;
    }

    # Laravel 路徑
    location /laravelDB/ {
        try_files $uri $uri/ /laravelDB/public/index.php?$query_string;
    }

    location /testFB/ {
        fastcgi_pass php:9000;
        fastcgi_param SCRIPT_FILENAME /var/www/html/testFB$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ \.php$ {
        fastcgi_pass php:9000;
        fastcgi_param SCRIPT_FILENAME /var/www/html$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

**docker-compose.yml 中的 nginx 服務修改**:
```yaml
nginx:
  image: nginx:latest
  container_name: nginx
  restart: always
  environment:
    - NGINX_ENVSUBST_TEMPLATE_SUFFIX=.template
  volumes:
    - ./nginx.conf.template:/etc/nginx/templates/default.conf.template
    - ./www:/var/www/html
  ports:
    - '${NGINX_PORT}:80'
  depends_on:
    - php
```

#### 方案 B: 保持現有 nginx.conf 不變

如果當前配置沒有需要動態調整的變數，可以暫時保持不變。

---

### Step 4: 修改 `dockerfile`

確保環境變數能正確傳遞到 PHP 容器。

**修改重點**:

1. **接收環境變數**
   ```dockerfile
   ARG TIMEZONE=Asia/Taipei
   ENV TZ=${TIMEZONE}
   ```

2. **完整修改後的範例**（詳見下方完整檔案）

---

### Step 5: 建立 Laravel Backend 的 `.env` 整合

Laravel 後端也需要使用環境變數，需要確保 Docker 環境變數能傳遞給 Laravel。

**方法**:

1. 在 `docker-compose.yml` 的 php 服務中加入環境變數
2. Laravel 的 `.env` 可以直接使用這些變數

**docker-compose.yml 中的 php 服務修改**:
```yaml
php:
  build: .
  container_name: php
  restart: always
  environment:
    - DB_HOST=${DB_HOST}
    - DB_PORT=${DB_PORT}
    - DB_DATABASE=${DB_DATABASE}
    - DB_USERNAME=${DB_USERNAME}
    - DB_PASSWORD=${DB_PASSWORD}
  volumes:
    - ./www:/var/www/html
  depends_on:
    - mariadb
```

---

### Step 6: 修改 `.gitignore`

確保敏感資料不會被提交到版本控制。

**新增內容**:
```gitignore
# 環境變數檔案
.env

# Laravel
backend/.env

# 保留範本
!.env.example
!backend/.env.example
```

---

### Step 7: 建立使用說明文件

建立 `INSTALLATION.md` 說明如何設定環境。

**內容包含**:
- 如何複製 `.env.example` 為 `.env`
- 如何修改環境變數
- 如何啟動服務
- 常見問題排解

---

## 📝 完整修改後的檔案範例

### 1. `.env.example`（已在 Step 1 列出）

### 2. `docker-compose.yml`（修改後）

```yaml
services:
  nginx:
    image: nginx:latest
    container_name: nginx
    restart: always
    volumes:
      - ./nginx.conf:/etc/nginx/conf.d/default.conf
      - ./www:/var/www/html
    ports:
      - '${NGINX_PORT:-8080}:80'
    depends_on:
      - php
    networks:
      - app-network

  php:
    build: .
    container_name: php
    restart: always
    environment:
      - DB_HOST=${DB_HOST}
      - DB_PORT=${DB_PORT}
      - DB_DATABASE=${DB_DATABASE}
      - DB_USERNAME=${DB_USERNAME}
      - DB_PASSWORD=${DB_PASSWORD}
      - TZ=${TIMEZONE:-Asia/Taipei}
    volumes:
      - ./www:/var/www/html
    depends_on:
      - mariadb
    networks:
      - app-network

  mariadb:
    image: mariadb:10.11.7
    container_name: mariadb
    restart: always
    environment:
      MYSQL_ROOT_PASSWORD: ${DB_PASSWORD}
      MYSQL_DATABASE: ${DB_DATABASE}
      TZ: ${TIMEZONE:-Asia/Taipei}
    volumes:
      - mariadb_data:/var/lib/mysql
    ports:
      - '${DB_EXTERNAL_PORT:-3306}:3306'
    command: --character-set-server=${DB_CHARSET:-utf8mb4} --collation-server=${DB_COLLATION:-utf8mb4_unicode_ci} --default-time-zone=+08:00
    networks:
      - app-network

  mariadb_test:
    image: mariadb:10.11.7
    container_name: mariadb_test
    restart: always
    environment:
      MYSQL_ROOT_PASSWORD: ${DB_TEST_PASSWORD}
      MYSQL_DATABASE: ${DB_TEST_DATABASE}
      TZ: ${TIMEZONE:-Asia/Taipei}
    volumes:
      - mariadb_test_data:/var/lib/mysql
    ports:
      - '${DB_TEST_EXTERNAL_PORT:-3308}:3306'
    command: --character-set-server=${DB_CHARSET:-utf8mb4} --collation-server=${DB_COLLATION:-utf8mb4_unicode_ci}
    networks:
      - app-network

  mariadbEcic:
    image: mariadb:10.11.7
    container_name: mariadbEcic
    restart: always
    environment:
      MYSQL_ROOT_PASSWORD: ${DB_ECIC_PASSWORD}
      MYSQL_DATABASE: ${DB_ECIC_DATABASE}
      TZ: ${TIMEZONE:-Asia/Taipei}
    volumes:
      - mariadbEcic_data:/var/lib/mysql
    ports:
      - '${DB_ECIC_EXTERNAL_PORT:-3307}:3306'
    command: --character-set-server=${DB_CHARSET:-utf8mb4} --collation-server=${DB_COLLATION:-utf8mb4_unicode_ci}
    networks:
      - app-network

  phpmyadmin:
    image: phpmyadmin:latest
    container_name: phpmyadmin
    restart: always
    environment:
      PMA_HOST: ${PMA_HOST:-mariadb}
      PMA_USER: ${PMA_USER:-root}
      PMA_PASSWORD: ${PMA_PASSWORD}
    ports:
      - '${PMA_PORT:-8081}:80'
    depends_on:
      - mariadb
    networks:
      - app-network

networks:
  app-network:
    driver: bridge

volumes:
  mariadb_data:
  mariadbEcic_data:
  mariadb_test_data:
```

### 3. `dockerfile`（修改後）

```dockerfile
# 使用 PHP 8.2 FPM 作為基礎映像
FROM php:8.2-fpm

# 接收建置參數
ARG TIMEZONE=Asia/Taipei

# 安裝系統依賴，包括 Git、curl 和 PHP 擴展所需的開發包
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# 安裝 PHP 擴展
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# 安裝 Composer，將其從官方 Composer 映像中複製到容器
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 設置工作目錄，這是應用程式的根目錄
WORKDIR /var/www

# 將本地代碼複製到容器內的工作目錄中
COPY . /var/www

# 設置文件和目錄權限，確保容器內的 www-data 用戶擁有寫入權限
RUN chown -R www-data:www-data /var/www

# 設定時區
ENV TZ=${TIMEZONE}
RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone

# 暴露容器的 9000 端口（供 PHP-FPM 使用）
EXPOSE 9000

# 啟動 PHP-FPM，這是 PHP 的 FastCGI 進程管理器
CMD ["php-fpm"]
```

### 4. `INSTALLATION.md`（新增）

```markdown
# Easy CRM 安裝說明

## 📋 前置需求

- Docker
- Docker Compose
- Git

## 🚀 快速開始

### 1. 複製專案

\`\`\`bash
git clone <repository-url>
cd easy-crm
\`\`\`

### 2. 設定環境變數

複製環境變數範本：

\`\`\`bash
cp .env.example .env
\`\`\`

編輯 `.env` 檔案，修改以下重要設定：

- `DB_PASSWORD`: 資料庫密碼（**必須修改**）
- `DB_TEST_PASSWORD`: 測試資料庫密碼（**必須修改**）
- `DB_ECIC_PASSWORD`: ECIC 資料庫密碼（**必須修改**）
- `PMA_PASSWORD`: phpMyAdmin 密碼（**必須修改**）
- `NGINX_PORT`: Nginx 服務 port（預設 8080）
- `PMA_PORT`: phpMyAdmin port（預設 8081）

### 3. 啟動服務

\`\`\`bash
docker-compose up -d
\`\`\`

### 4. 檢查服務狀態

\`\`\`bash
docker-compose ps
\`\`\`

### 5. 存取服務

- **應用程式**: http://localhost:8080
- **phpMyAdmin**: http://localhost:8081

## 🔧 常見操作

### 停止服務

\`\`\`bash
docker-compose down
\`\`\`

### 重新建置容器

\`\`\`bash
docker-compose up -d --build
\`\`\`

### 查看日誌

\`\`\`bash
docker-compose logs -f
\`\`\`

## ⚠️ 注意事項

1. **不要提交 `.env` 檔案到 Git**
2. **務必修改預設密碼**
3. **生產環境請設定 `APP_DEBUG=false`**

## 🐛 常見問題

### Port 已被占用

修改 `.env` 中的 port 設定：

\`\`\`bash
NGINX_PORT=8090
PMA_PORT=8091
\`\`\`

### 資料庫連線失敗

檢查 `.env` 中的資料庫設定是否正確。
\`\`\`

---

## ✅ 實作檢查清單

- [ ] Step 1: 建立 `.env.example`
- [ ] Step 2: 修改 `docker-compose.yml`
- [ ] Step 3: 修改 `nginx.conf`（或建立 template）
- [ ] Step 4: 修改 `dockerfile`
- [ ] Step 5: 整合 Laravel Backend `.env`
- [ ] Step 6: 修改 `.gitignore`
- [ ] Step 7: 建立 `INSTALLATION.md`
- [ ] 測試：使用 `.env.example` 建立 `.env`
- [ ] 測試：啟動所有服務
- [ ] 測試：驗證環境變數是否正確傳遞
- [ ] 測試：修改 port 設定並重啟服務
- [ ] 文件：更新 README.md

---

## 🔒 安全性建議

### 1. 密碼強度

使用強密碼，建議包含：
- 至少 16 個字元
- 大小寫字母
- 數字
- 特殊符號

### 2. 環境隔離

為不同環境使用不同的 `.env` 檔案：
- `.env.development`
- `.env.staging`
- `.env.production`

### 3. 敏感資料管理

生產環境建議使用：
- Docker Secrets
- HashiCorp Vault
- AWS Secrets Manager

---

## 📊 效益評估

### 改造前

❌ 密碼硬編碼在 `docker-compose.yml`
❌ Port 設定分散在多個檔案
❌ 無法快速切換環境
❌ 容易誤提交敏感資料

### 改造後

✅ 所有敏感資料集中在 `.env`（不提交到 Git）
✅ 一個檔案管理所有配置
✅ 支援多環境快速切換
✅ 提升安全性與可維護性

---

## 📅 實作時程規劃

| 步驟 | 預計時間 | 負責人 |
|------|----------|--------|
| Step 1-2 | 30 分鐘 | 開發者 |
| Step 3-4 | 20 分鐘 | 開發者 |
| Step 5-7 | 30 分鐘 | 開發者 |
| 測試驗證 | 1 小時 | QA/開發者 |
| 文件更新 | 20 分鐘 | 開發者 |

**總計**: 約 2.5 小時

---

## 🎓 學習資源

- [Docker Compose Environment Variables](https://docs.docker.com/compose/environment-variables/)
- [12 Factor App - Config](https://12factor.net/config)
- [Laravel Environment Configuration](https://laravel.com/docs/configuration)

---

_文件建立日期: 2025-11-07_
_最後更新: 2025-11-07_
_版本: 1.0_
