# Lịch sử Yêu cầu & Quy trình Làm việc (Isocalmarket)

## Yêu cầu ban đầu
- Xóa bỏ tất cả các page/feature demo như `home`, `about`, `contact`...
- Đọc và tuân thủ các quy tắc trong `design.md`, `ARCHITECTURE.md` và toàn bộ các tệp tại thư mục `working/`.
- Định hướng sản phẩm: **Enterprise Digital Inventory Management System** phát triển trong nhiều năm cho team 20+ người.
- Luôn ưu tiên: Khả năng mở rộng, dễ bảo trì, mã nguồn sạch. Không làm ẩu để chạy được.
- Đưa ra cảnh báo về Technical Debt nếu có quyết định sai lầm.
- Tận dụng triệt để components tại `isocalmarket-web/src/app/shared/components`.

## Phân tích & Thực hiện
- Đã đọc hiểu kiến trúc Frontend: Angular v18, Flat Features, quản lý State qua Slice Stores (Signals).
- Đã đọc hiểu kiến trúc Backend: Laravel với Domain-Driven Design (DDD), CQRS, Repository & Services Pattern.
- Nhận diện Technical Debt tiềm năng: Phải lưu từng `Inventory Item` thành một thực thể riêng biệt thay vì lưu trữ dạng biến số lượng (`quantity`) để phục vụ mở rộng trong tương lai.
- Lên kế hoạch (Implementation Plan) để xóa các thư mục demo (`home`, `about`, `contact`) và gỡ route tại `app.routes.ts`. Đã thực hiện hoàn tất và build frontend thành công.
- Người dùng yêu cầu triển khai tiếp toàn bộ hệ thống gồm Frontend, Backend và Database.
- Đã thiết kế kiến trúc hoàn chỉnh cho cả 3 lớp:
  - Database schema: `products`, `inventory_items`, `orders`, `order_items`, cập nhật `users`.
  - Backend DDD lai: entities, repositories, persistent models, mappers, services, controllers, form requests.
  - Frontend features: `store`, `products`, `inventory`, `orders` dùng Signals, Slice Stores, Tailwind v4 và Custom Components có sẵn.
  - Đã cập nhật bản kế hoạch triển khai chi tiết lên `implementation_plan.md`. Đã được người dùng thông qua.
  - Đã triển khai và chạy thành công database migrations.
  - Đã tạo các lớp Domain & Infrastructure (Eloquent, Repositories, Mappers), Application Services, Form Requests, HTTP Controllers và cấu hình định tuyến API trong Backend.
  - Đã tạo ApiService, các Slice Stores (ProductStore, InventoryStore, OrderStore), cấu hình HttpClient và lazy routing trong Frontend.
  - Đã xây dựng 4 component giao diện chính (Storefront, Products, Inventory, Orders) tận dụng components dùng chung có sẵn và tuân thủ chặt chẽ tài liệu `design.md`.
  - Đã chạy biên dịch Angular (`npm run build`) và hoàn thành thành công không lỗi.
  - Người dùng yêu cầu **bỏ Web3 và chuyển sang đăng ký/đăng nhập bằng Web2 (Email & Password)**.
  - Đã thiết kế lại hệ thống xác thực sang Web2 và cập nhật `implementation_plan.md`. Đang chờ phê duyệt từ người dùng.
  - Nhận yêu cầu tắt chức năng Web3 qua cấu hình environment.
  - Đã thêm `enableWeb3: false` vào `environment.ts` và `environment.development.ts`.
  - Đã cập nhật `Web3Service` để bỏ qua khởi tạo AppKit (WalletConnect) và bảo vệ các hàm tương tác ví tránh lỗi Null Reference khi tắt Web3.
  - Đã chạy kiểm tra build thành công.
  - Ghi nhận thông tin vào `AGENTS.md`.
  - Nhận yêu cầu tối ưu và hoàn tất dự án:
    - Sửa màu chủ đạo sang Xanh dương (`#006dd0`) và Xanh lục (`#0d9488`).
    - Sửa lỗi mobile sidebar không hoạt động bằng cách xây dựng drawer với hiệu ứng animation trượt mượt mà bằng CSS transitions thay vì dùng `*ngIf`.
    - Cập nhật cart drawer sử dụng CSS transitions trượt mượt mà.
    - Cấu hình Rate Limiter nghiêm ngặt cho các route `/auth/login` và `/auth/register` (tối đa 10 requests trong vòng 1 phút) để phòng tránh tấn công brute-force.
    - Cho phép người dùng đăng nhập bằng cả username (cột `name`) hoặc `email`.
    - Mở public danh sách tài nguyên (sản phẩm) tại Storefront mà không yêu cầu đăng nhập trước.
    - Phát triển tính năng Phân trang (Pagination) ở cả Backend (API endpoints) và Frontend (Slice Stores + Components) cho các bảng sản phẩm, kho hàng và đơn hàng bằng cách tái sử dụng component `app-pagination`.
    - Tạo migration bổ sung chỉ mục (indexes) trên các cột thường xuyên tìm kiếm lọc như `users.name`, `products.status`, `orders.status`, `orders.created_at` giúp tối ưu tốc độ truy xuất cơ sở dữ liệu.
    - Phân tích và thảo luận về việc lựa chọn giữa `BigInt` tự tăng được đánh index tốt với `UUIDv7` đối với dự án hiện tại.
  - **[HOÀN THÀNH] Chuyển đổi toàn bộ hệ thống sang UUIDv7**:
    - Đã viết lại 5 Database Migrations dùng `uuid()` PK và `foreignUuid()` FK, đồng thời nhúng tất cả indexes vào migration gốc (bỏ migration `add_indexes` riêng biệt).
    - Đã chạy `php artisan migrate:fresh` thành công — 7 bảng tạo lại với schema UUID.
    - Đã cập nhật 5 Eloquent Models với `HasUuids` trait và override `newUniqueId()` để dùng `Str::uuid7()`.
    - Đã cập nhật 5 Domain Entities: `?int $id` → `?string $id`, tất cả FK IDs đổi sang `string`.
    - Đã cập nhật 4 Repository Interfaces và 4 Repository Implementations.
    - Đã cập nhật 4 Application Services (AuthService, ProductService, OrderService, InventoryService).
    - Đã cập nhật 3 Controllers (bỏ cast `(int)$id`).
    - Đã sắp xếp theo `created_at` thay vì `id` trong tất cả queries (UUID không sort được theo số).
    - Đã xác nhận UUID7 thực sự được tạo ra: `019f6699-e6d2-70f2-89bd-d3b93946a77d` (prefix time-ordered).
    - Frontend Angular không cần sửa vì stores dùng `any` type.

