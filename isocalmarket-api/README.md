# Isocalmarket Backend API

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
    * * * * * cd /path-to-your-project/isocalmarket-api && php artisan schedule:run >> /dev/null 2>&1
    ```
    _Scheduler đã được lập lịch sẵn trong `routes/console.php` để tự động gọi lệnh `deposit:check-pending` mỗi 1 phút._

### 2. Các lệnh bảo trì khác

- **Force Logout toàn bộ bằng cách thu hồi phiên bản token (không thay đổi App Key)**:
    ```bash
    php artisan auth:force-logout-all
    ```
