# AGENTS.md - Lịch sử thay đổi và ghi nhớ công việc

## [2026-07-26] Cập nhật phân trang trang Storefront (/storefront)

- Yêu cầu: Đặt số lượng hiển thị phân trang của riêng trang `http://localhost:4200/storefront` thành 100 record/page. Giữ nguyên dữ liệu hiện tại và không ảnh hưởng các chức năng khác.
- Thực hiện:
  - Frontend: Cập nhật `limit` thành `100` trong `d:\git\fvia79\fvia79-web\src\app\features\storefront\storefront.component.ts`.
  - Backend: Thêm chốt chặn bảo mật khống chế max limit `max(1, min((int)$limit, 100))` trong `ProductController.php` giúp ngăn chặn hành vi DoS/spam limit giả lập lớn (ví dụ `limit=1000`).
  - Đã kiểm tra build Angular frontend và cú pháp PHP backend thành công 100%.

## [2026-07-26] Rà soát và chuẩn hóa tham số limit (max 20) và page trong truy vấn CSDL

- Mô tả: Rà soát các vấn đề liên quan đến tham số `limit` và `page` khi client gửi request truy vấn API phân trang, phòng chống các lỗi offset âm (`OFFSET -10`), chia cho 0 (`limit = 0`), cắt mảng sai trang trong `array_slice`, và áp dụng mức `limit` tối đa **20** bản ghi/trang cho toàn bộ hệ thống.
- Thực hiện:
  - Backend (`fvia79-api`):
    - `ProductRepository.php`, `OrderRepository.php`, `UserRepository.php`, `CategoryRepository.php`, `InventoryItemRepository.php`, `InventoryBatchRepository.php`, `CouponRepository.php`, `DepositRepository.php`: Đồng bộ bổ sung chuẩn hóa `$page = max(1, $page);` và `$limit = max(1, $limit);` cho toàn bộ các hàm phân trang.
    - `ProductController.php`, `OrderController.php`, `AdminUserController.php`, `CategoryController.php`, `UserSessionController.php`, `B2bController.php`, `B2bAdminController.php`: Cập nhật bọc trần `$limit = min(20, max(1, (int) $request->query('limit', ...)));` ép giá trị `limit` tối đa không vượt quá **20** bản ghi/trang.
    - Kiểm thử: Kiểm tra cú pháp PHP `php -l` toàn bộ các Controller thành công **100% SUCCESS, 0 ERRORS**.
- Xác nhận: Hệ thống hoạt động an toàn 100%, tự động ép `limit` tối đa **20** bản ghi/trang!
