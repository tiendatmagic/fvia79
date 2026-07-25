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
- **FTP Host**: `184.164.93.247` (Port `21`)
- **FTP User**: `deploy_f627f2`
- **FTP Pass**: `IsoFtp_c688e0bca6d2!`
- **FTP Root Dir**: `/var/www`
  - Frontend: `/var/www/isocalmarket-web`
  - Backend: `/var/www/isocalmarket-api`
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
   php artisan config:clear && php artisan config:cache
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

---

## 🛡️ 4. Quy Định .gitignore & Quản Lý Mã Nguồn (Git Ignore Guidelines)

Để đảm bảo an toàn mật mã, tránh lộ thông tin môi trường nhạy cảm (CSDL, API Key, Secret Token) và tối ưu hóa dung lượng khi lưu trữ mã nguồn trên Git cũng như khi đóng gói deploy qua FTP/VPS, dự án thiết lập cấu hình `.gitignore` ở thư mục gốc và ở cả 2 phân hệ Backend/Frontend:

### 1. Thư Mục Gốc Dự Án (`.gitignore`)
- **Quản lý tổng thể**: Bỏ qua toàn bộ `node_modules/`, `vendor/`, `dist/`, các tệp `.env`, `.env.*` (ngoại trừ `.env.example`), tệp sao lưu CSDL `*.sql` và tệp tạm hệ thống (`.DS_Store`, `Thumbs.db`, `.idea/`, `.vscode/*`).

### 2. Backend Laravel (`isocalmarket-api/.gitignore`)
- **Môi trường & Mật mã nhạy cảm (CỰC KỲ QUAN TRỌNG)**:
  - `.env`, `.env.backup`, `.env.production` (Không bao giờ commit tệp `.env` chứa mật khẩu thật lên repository).
- **Thư mục thư viện & Nhật ký hoạt động**:
  - `/vendor` (Tải lại bằng `composer install` trên môi trường VPS/Local).
  - `/node_modules`
  - `*.log` (Nhật ký lỗi hệ thống trong `storage/logs/`).
  - `/storage/*.key` (Khóa bảo mật OAuth/JWT).
- **Thư mục Sao lưu CSDL & Cache công cụ**:
  - `/backup/*.sql` (Tệp dump database tự động).
  - `/.phpunit.cache`, `_ide_helper.php`, `/.idea`, `/.vscode`.

### 2. Frontend Angular (`isocalmarket-web/.gitignore`)
- **Sản phẩm biên dịch & Bộ nhớ đệm Build**:
  - `/dist` (Sản phẩm build Frontend sau khi chạy `npm run build`).
  - `/tmp`, `/out-tsc`, `/.angular/cache`.
- **Thư mục thư viện & Log lỗi**:
  - `/node_modules` (Tải lại bằng `npm install`).
  - `npm-debug.log`, `yarn-error.log`.
- **Cấu hình cá nhân IDE & Agent AI**:
  - `.idea/`, `.vscode/*` (Trừ các tệp thiết lập dùng chung được phép theo dõi).
  - `.DS_Store`, `Thumbs.db`, `.agent/`, `.agents/`.

### 💡 Quy Chuẩn Khi Đóng Gói & Deploy Lên VPS
1. **Bảo tồn dữ liệu & Bảo mật**: Khi nén code hoặc sync qua FTP, **LUÔN BỎ QUA** thư mục `vendor/`, `node_modules/`, `dist/` và tệp `.env`.
2. **Triển khai trên VPS**:
   - Backend: Upload mã nguồn sạch -> Chạy `composer install --no-dev --optimize-autoloader` trên VPS -> Tạo tệp `.env` riêng biệt theo đúng thông số server -> Chạy `php artisan config:clear && php artisan config:cache && php artisan route:cache && php artisan view:cache`.
   - Frontend: Chạy `npm run build` tại máy Local -> Upload nội dung thư mục `dist/angular-web3-wallet/browser` trực tiếp vào `/var/www/isocalmarket-web`.

