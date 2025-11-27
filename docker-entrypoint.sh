#!/bin/sh
set -e

# 修復 Laravel storage 目錄權限
if [ -d "/var/www/html/backend/storage" ]; then
    echo "修復 storage 目錄權限..."
    chown -R www-data:www-data /var/www/html/backend/storage
    chmod -R 775 /var/www/html/backend/storage
    echo "storage 目錄權限已修復"
fi

# 修復 Laravel bootstrap/cache 目錄權限
if [ -d "/var/www/html/backend/bootstrap/cache" ]; then
    echo "修復 bootstrap/cache 目錄權限..."
    chown -R www-data:www-data /var/www/html/backend/bootstrap/cache
    chmod -R 775 /var/www/html/backend/bootstrap/cache
    echo "bootstrap/cache 目錄權限已修復"
fi

# 執行原本的 CMD 指令
exec "$@"
