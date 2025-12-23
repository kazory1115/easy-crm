# PHP-FPM 映像檔（只保留 MVP 必要套件）
FROM php:8.2-fpm

# 可由 docker-compose 傳入的建置參數
ARG TIMEZONE=Asia/Taipei
ARG PHP_MEMORY_LIMIT=256M
ARG PHP_UPLOAD_MAX_FILESIZE=20M
ARG PHP_POST_MAX_SIZE=20M

# 轉成執行期環境變數
ENV TZ=${TIMEZONE} \
    PHP_MEMORY_LIMIT=${PHP_MEMORY_LIMIT} \
    PHP_UPLOAD_MAX_FILESIZE=${PHP_UPLOAD_MAX_FILESIZE} \
    PHP_POST_MAX_SIZE=${PHP_POST_MAX_SIZE}

# 安裝 PHP 擴充與基礎工具（不含不必要套件）
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    zip \
    unzip \
    && docker-php-ext-install \
      pdo_mysql \
      mbstring \
      exif \
      pcntl \
      bcmath \
      gd \
      zip \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# 寫入 PHP 設定檔（對應 .env 參數）
RUN { \
    echo "memory_limit=${PHP_MEMORY_LIMIT}"; \
    echo "upload_max_filesize=${PHP_UPLOAD_MAX_FILESIZE}"; \
    echo "post_max_size=${PHP_POST_MAX_SIZE}"; \
    echo "max_execution_time=300"; \
  } > /usr/local/etc/php/conf.d/custom.ini

# 直接使用官方 composer 影像提供的二進位檔
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# 預設工作目錄
WORKDIR /var/www/html

# 設定時區
RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone

# PHP-FPM 預設埠
EXPOSE 9000

# 啟動 PHP-FPM
CMD ["php-fpm"]
