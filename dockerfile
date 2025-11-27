# 使用 PHP 8.2 FPM 作為基礎映像
FROM php:8.2-fpm

# 接收建置參數
ARG TIMEZONE=Asia/Taipei
ARG PHP_MEMORY_LIMIT=256M
ARG PHP_UPLOAD_MAX_FILESIZE=20M
ARG PHP_POST_MAX_SIZE=20M

# 設定環境變數
ENV TZ=${TIMEZONE} \
    PHP_MEMORY_LIMIT=${PHP_MEMORY_LIMIT} \
    PHP_UPLOAD_MAX_FILESIZE=${PHP_UPLOAD_MAX_FILESIZE} \
    PHP_POST_MAX_SIZE=${PHP_POST_MAX_SIZE}

# 安裝系統依賴，包括 Git、curl、MySQL 客戶端和 PHP 擴展所需的開發包
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    default-mysql-client \
    libzip-dev \
    zip \
    unzip \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# 安裝 PHP 擴展（MySQL 相關）
RUN docker-php-ext-install \
    pdo_mysql \
    mysqli \
    mbstring \
    exif \
    pcntl \
    bcmath \
    gd \
    zip \
    && php -m | grep -i pdo_mysql

# 設定 PHP 配置
RUN { \
    echo 'memory_limit=${PHP_MEMORY_LIMIT}'; \
    echo 'upload_max_filesize=${PHP_UPLOAD_MAX_FILESIZE}'; \
    echo 'post_max_size=${PHP_POST_MAX_SIZE}'; \
    echo 'max_execution_time=300'; \
    } > /usr/local/etc/php/conf.d/custom.ini

# 安裝 Composer，將其從官方 Composer 映像中複製到容器
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 複製啟動腳本
COPY docker-entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

# 設置工作目錄
WORKDIR /var/www/html

# 設定時區
RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone

# 設置文件和目錄權限，確保容器內的 www-data 用戶擁有寫入權限
RUN chown -R www-data:www-data /var/www/html

# 暴露容器的 9000 端口（供 PHP-FPM 使用）
EXPOSE 9000

# 設定啟動腳本
ENTRYPOINT ["docker-entrypoint.sh"]

# 啟動 PHP-FPM，這是 PHP 的 FastCGI 進程管理器
CMD ["php-fpm"]
