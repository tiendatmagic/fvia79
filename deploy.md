# Hướng Dẫn Triển Khai (Deploy) Hệ Thống Isocalmarket Lên VPS

Tài liệu này ghi lại chi tiết các bước đã thực hiện để deploy thành công dự án **Isocalmarket** (Frontend Angular + Backend Laravel + CSDL MySQL) lên hệ thống VPS với SSL HTTPS và Cronjob tự động.

---

## 📌 1. Thông Tin VPS & Tên Miền

- **VPS IP**: `184.164.93.247`
- **Username VPS**: `root`
  **Pass VPS**: "T7o9pEuOCesR" (không có dấu ")
- **Domain**: `https://isocalmarket.com/`
- **phpMyAdmin URL**: `https://isocalmarket.com/phpmyadmin/`
- **MySQL Database**: `isocalmarket`
- **MySQL User**: `isocalmarket_user`
- **MySQL Password**: `IsoCal_9221eaf12c8455169dfec5b9`
- **Web Server**: Nginx (Ubuntu 22.04 LTS)
- **PHP**: PHP 8.3 (PPA `ppa:ondrej/php`) + PHP-FPM
- **SSL Certificate**: Let's Encrypt (Certbot)

---

## 🚀 2. Chi Tiết Các Bước Triển Khai

### Bước 1: Chuẩn Bị & Build Frontend Cục Bộ

1. Di chuyển vào thư mục Frontend: `cd D:\git\isocalmarket\isocalmarket-web`
2. Tiến hành build sản phẩm production:
   ```bash
   npm run build
   ```
3. Đóng gói kết quả tại `dist/angular-web3-wallet/browser` thành file `frontend-dist.zip`.

### Bước 2: Cài Đặt Môi Trường Mới Trên VPS

1. Thêm PPA và cài đặt PHP 8.3 cùng các extension cần thiết:
   ```bash
   apt-get update && apt-get install -y software-properties-common
   add-apt-repository -y ppa:ondrej/php
   apt-get update
   apt-get install -y nginx mysql-server php8.3-fpm php8.3-mysql php8.3-cli php8.3-curl php8.3-mbstring php8.3-xml php8.3-zip php8.3-bcmath php8.3-gd php8.3-intl unzip composer certbot python3-certbot-nginx
   update-alternatives --set php /usr/bin/php8.3
   ```

### Bước 3: Tạo CSDL & Import Dữ Liệu SQL

1. Tạo Database `isocalmarket` và User truy cập:
   ```sql
   CREATE DATABASE IF NOT EXISTS isocalmarket CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   CREATE USER IF NOT EXISTS 'isocalmarket_user'@'localhost' IDENTIFIED BY 'IsoCalMarket_Pass_2026!';
   GRANT ALL PRIVILEGES ON isocalmarket.* TO 'isocalmarket_user'@'localhost';
   FLUSH PRIVILEGES;
   ```
2. Upload và import file SQL `isocalmarket_24_7_2026.sql`:
   ```bash
   mysql isocalmarket < /tmp/isocalmarket_24_7_2026.sql
   ```

### Bước 4: Triển Khai Backend Laravel (`/var/www/isocalmarket-api`)

1. Upload và giải nén source code Backend:
   ```bash
   mkdir -p /var/www/isocalmarket-api
   unzip -o /tmp/source-code.zip -d /var/www/isocalmarket-api
   ```
2. Tạo file cấu hình `.env` cho môi trường Production:

   ```env
   APP_NAME=Isocalmarket
   APP_ENV=production
   APP_KEY=base64:NSC1XciR8ATY9eOQSf3hHGnLk6AsiKaZ42002EZluCA=
   APP_DEBUG=false
   APP_URL=https://isocalmarket.com

   APP_TIMEZONE=Asia/Ho_Chi_Minh
   APP_LOCALE=vi
   APP_FALLBACK_LOCALE=en

   LOG_CHANNEL=stack
   LOG_LEVEL=error

   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=isocalmarket
   DB_USERNAME=isocalmarket_user
   DB_PASSWORD=IsoCalMarket_Pass_2026!

   SESSION_DRIVER=file
   SESSION_LIFETIME=120
   SESSION_ENCRYPT=false
   SESSION_PATH=/
   SESSION_DOMAIN=null

   FILESYSTEM_DISK=local
   QUEUE_CONNECTION=database
   CACHE_STORE=file

   BCRYPT_ROUNDS=12
   TOKEN_CONTRACT_ADDRESS=0x55d398326f99059ff775485246999027b3197955
   TOKEN_DECIMALS=18
   ```

3. Cài đặt thư mục vendor và tối ưu hóa Laravel:
   ```bash
   cd /var/www/isocalmarket-api
   composer install --no-dev --optimize-autoloader --no-interaction
   php artisan storage:link
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```
4. Phân quyền bộ nhớ đệm và upload:
   ```bash
   chown -R www-data:www-data /var/www/isocalmarket-api
   chmod -R 775 /var/www/isocalmarket-api/storage /var/www/isocalmarket-api/bootstrap/cache
   ```

### Bước 5: Triển Khai Frontend Angular (`/var/www/isocalmarket-web`)

1. Upload và giải nén giao diện Angular:
   ```bash
   mkdir -p /var/www/isocalmarket-web
   unzip -o /tmp/frontend-dist.zip -d /var/www/isocalmarket-web
   chown -R www-data:www-data /var/www/isocalmarket-web
   ```

### Bước 6: Cấu Hình Nginx Web Server

Tạo file `/etc/nginx/sites-available/isocalmarket.com`:

```nginx
server {
    listen 80;
    listen [::]:80;
    server_name isocalmarket.com www.isocalmarket.com;

    return 301 https://isocalmarket.com$request_uri;
}

server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    server_name isocalmarket.com www.isocalmarket.com;

    ssl_certificate /etc/letsencrypt/live/isocalmarket.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/isocalmarket.com/privkey.pem;
    include /etc/letsencrypt/options-ssl-nginx.conf;
    ssl_dhparam /etc/letsencrypt/ssl-dhparams.pem;

    root /var/www/isocalmarket-web;
    index index.html;

    gzip on;
    gzip_types text/plain text/css application/json application/javascript text/xml application/xml application/xml+rss text/javascript;

    # Frontend Angular SPA fallback
    location / {
        try_files $uri $uri/ /index.html;
    }

    # Backend Laravel API
    location /api {
        alias /var/www/isocalmarket-api/public;
        try_files $uri $uri/ @api_fallback;

        location ~ \.php$ {
            include fastcgi_params;
            fastcgi_param SCRIPT_FILENAME /var/www/isocalmarket-api/public/index.php;
            fastcgi_param SCRIPT_NAME /index.php;
            fastcgi_pass unix:/run/php/php8.3-fpm.sock;
        }
    }

    location @api_fallback {
        rewrite ^/api/(.*)$ /api/index.php?$query_string last;
    }

    location ~* \.(js|css|png|jpg|jpeg|gif|ico|svg|woff|woff2|ttf|eot)$ {
        expires 30d;
        add_header Cache-Control "public, no-transform";
    }
}
```

Kích hoạt site và khởi động lại Nginx:

```bash
ln -sf /etc/nginx/sites-available/isocalmarket.com /etc/nginx/sites-enabled/isocalmarket.com
rm -f /etc/nginx/sites-enabled/default
nginx -t
systemctl restart php8.3-fpm
systemctl restart nginx
```

### Bước 7: Cấp Chứng Chỉ SSL (HTTPS)

Đăng ký chứng chỉ SSL miễn phí từ Let's Encrypt cho tên miền:

```bash
certbot --nginx -d isocalmarket.com -d www.isocalmarket.com --non-interactive --agree-tos --email admin@isocalmarket.com --redirect
```

### Bước 8: Thiết Lập Cronjob Tự Động

Thêm dòng sau vào crontab của user `www-data`:

```bash
(crontab -u www-data -l 2>/dev/null; echo "* * * * * cd /var/www/isocalmarket-api && php artisan schedule:run >> /dev/null 2>&1") | crontab -u www-data -
```

---

## ⚡ 3. Kiểm Tra & Nghiệm Thu Hệ Thống

1. **Website Frontend (HTTPS)**:
   - URL: `https://isocalmarket.com/` (Trả về HTTP 200 OK và chứng chỉ SSL hợp lệ).
2. **Backend API Endpoints**:
   - URL: `https://isocalmarket.com/api/v1/categories` (Trả về JSON danh mục thành công).
   - URL: `https://isocalmarket.com/api/v1/products/grouped` (Trả về JSON sản phẩm theo nhóm thành công).
3. **Cronjob Scheduler**:
   - Đã cài đặt tự động quét nạp tiền USDT BEP20, Binance Pay và đồng bộ nhà cung cấp mỗi phút.
