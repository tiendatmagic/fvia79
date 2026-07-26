# Fvia79 Backend API

## Cấu hình hệ thống & Lệnh vận hành

### 1. Lệnh quét đối soát giao dịch nạp tiền (Cron Job / Scheduler)

Hệ thống sử dụng cơ chế tự động quét lịch sử nhận USDT qua blockchain (USDT BEP-20) và cổng Binance Pay (Binance ID) để đối soát hóa đơn nạp và cộng tiền tự động cho người dùng.

- **Lệnh chạy thủ công (Kiểm tra lập tức)**:

    ```bash
    php artisan deposit:check-pending
    ```

    _Lệnh này sẽ quét toàn bộ các hóa đơn nạp đang ở trạng thái `pending_payment`, tự động gọi API đối soát của Moralis (cho BEP-20) và Binance API (cho Binance Pay)._

- **Cấu hình Cron Job (Chạy tự động mỗi phút trên Server)**:
  Đăng ký Laravel Scheduler chạy mỗi phút trong cron job của máy chủ Linux (VPS):
    ```bash
    * * * * * cd /path-to-your-project/fvia79-api && php artisan schedule:run >> /dev/null 2>&1
    ```
    _Scheduler đã được lập lịch sẵn trong `routes/console.php` để tự động gọi lệnh `deposit:check-pending` mỗi 1 phút._

### 2. Lệnh đồng bộ danh mục & sản phẩm từ Nhà cung cấp (Supplier Sync)

- **Lệnh chạy thủ công (Đồng bộ tất cả nhà cung cấp đang bật)**:

    ```bash
    php artisan suppliers:sync
    ```

- **Đồng bộ 1 Nhà cung cấp cụ thể theo mã Code (ví dụ: `ads7979`, `vlclone`)**:

    ```bash
    php artisan suppliers:sync ads7979
    ```

- **Chỉ đồng bộ các nhà cung cấp đã đến hạn (chạy ngầm mỗi 10 phút qua Scheduler)**:
    ```bash
    php artisan suppliers:sync --due
    ```

### 3. Các lệnh bảo trì khác

- **Force Logout toàn bộ bằng cách thu hồi phiên bản token (không thay đổi App Key)**:
    ```bash
    php artisan auth:force-logout-all
    ```

### 4. Sao lưu và khôi phục MySQL

Tab **Sao lưu & Khôi phục** trong trang Cấu hình hệ thống sử dụng trực tiếp
MySQL client, không thực thi file tải lên qua PHP hoặc `DB::unprepared()`.

Máy chủ API phải có hai executable `mysql` và `mysqldump`. Trên Debian/Ubuntu
có thể cài package `default-mysql-client`. Nếu executable không nằm trong
`PATH`, cấu hình đường dẫn tuyệt đối:

```dotenv
MYSQLDUMP_BINARY=/usr/bin/mysqldump
MYSQL_BINARY=/usr/bin/mysql
DATABASE_BACKUP_MAX_UPLOAD_KB=102400
DATABASE_BACKUP_TIMEOUT=900
DATABASE_BACKUP_RETENTION_DAYS=30
```

Bản sao lưu an toàn trước mỗi lần khôi phục được giữ trong
`storage/app/private/database-backups`. Thư mục này không được public qua web
server và cần được bảo vệ trong chính sách sao lưu hạ tầng.
