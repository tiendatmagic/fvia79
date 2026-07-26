# AGENTS.md - Lịch sử thay đổi và ghi nhớ công việc

## [2026-07-26] Cập nhật phân trang trang Storefront (/storefront)
- Yêu cầu: Đặt số lượng hiển thị phân trang của riêng trang `http://localhost:4200/storefront` thành 100 record/page. Giữ nguyên dữ liệu hiện tại và không ảnh hưởng các chức năng khác.
- Thực hiện:
  - Cập nhật `limit` thành `100` trong `d:\git\fvia79\fvia79-web\src\app\features\storefront\storefront.component.ts`.
  - Đã tiến hành kiểm tra build Angular frontend.
