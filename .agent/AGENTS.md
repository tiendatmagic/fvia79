# Lịch sử Yêu cầu & Quy trình Làm việc (Isocalmarket)

## ⛔ QUY TẮC BẮT BUỘC (MANDATORY RULES)
# Lịch sử Yêu cầu & Quy trình Làm việc (Isocalmarket)

## ⛔ QUY TẮC BẮT BUỘC (MANDATORY RULES)

> **TUYỆT ĐỐI KHÔNG được chạy lệnh `php artisan migrate:fresh --seed`** vì lệnh này sẽ **XÓA TOÀN BỘ DỮ LIỆU** hiện có trong database. Chỉ được dùng trong môi trường dev sạch hoàn toàn.
>
> Khi cần chạy migration mới, chỉ dùng:
> - `php artisan migrate` — áp dụng migration mới mà không xóa dữ liệu cũ.
>
> Khi cần seed dữ liệu cho một seeder cụ thể, chỉ dùng:
> - `php artisan db:seed --class=TenSeederClass` — chạy đúng 1 seeder.

- **[CHUYỂN MÃ UID NGƯỜI DÙNG SANG DẠNG SỐ TỰ ĐỘNG MỞ RỘNG (DYNAMIC AUTO-EXPANDING NUMERIC UID 8-32 DIGITS)]**:
  - Cập nhật thuật toán tạo UID `UserModel::generateUniqueUid()`: Khởi đầu bằng mã 8 chữ số số thuần (`10000000` -> `99999999`) dựa theo timestamp tăng tiến kết hợp bộ đếm sequence trong bộ nhớ. Khi số lượng tài khoản/thời gian vượt ngưỡng 90 triệu người dùng, thuật toán tự động nới rộng lên 9 chữ số (`100000000`), 10 chữ số... mượt mà mà không bao giờ bị giới hạn đụng trần.
  - Nâng độ rộng cột `uid` trong bảng `users` lên `VARCHAR(32)` tại migrations `2026_07_23_000000_add_uid_to_users_table.php` và `2026_07_23_000002_update_uid_to_numeric_in_users_table.php` để CSDL sẵn sàng mở rộng mà không cần chạy lại migration sau này.
  - Sửa Tooltip và Toast thông báo tại bảng danh sách người dùng (`admin-users.component.html` & `admin-users.component.ts`): Bổ sung hàm `copyUid()`, khi click copy UID sẽ hiển thị đúng Toast `Đã sao chép UID!` thay vì báo nhầm `Đã sao chép API Key!`.
  - Cập nhật test case tự động `test_user_uid_is_purely_numeric_and_unique()` trong `SecurityHardeningTest.php`, đã chạy `php artisan test` vượt qua 56/56 test cases (100% PASSED).

- **[THÊM CHỨC NĂNG XÓA TÀI KHOẢN HÀNG LOẠT (BULK DELETE USERS)]**:
  - Triển khai tính năng Xóa tài khoản thành viên hàng loạt bằng cách tích chọn checkbox tại trang Quản lý thành viên (`AdminUsersComponent`).
  - Thêm nút **Xóa đã chọn (X)** (màu đỏ / rose với icon thùng rác) trên thanh công cụ Header khi tích chọn ít nhất 1 tài khoản (bất kể trạng thái active hay banned).
  - Tự động bảo vệ an toàn cho tài khoản Admin đang đăng nhập thao tác (loại trừ khỏi danh sách bị xóa).
  - Tích hợp Modal xác nhận (`modalService.confirm`) cảnh báo hành động xóa vĩnh viễn không thể hoàn tác.
  - Backend API (`POST /v1/admin/users/bulk-delete`): Bọc DB Transaction để xóa hàng loạt các tài khoản được chọn ngoại trừ ID Admin hiện tại.
  - Đã thêm test cases tự động trong `SecurityHardeningTest.php`, chạy `php artisan test` vượt qua 53/53 test cases (100% PASSED) và Angular `npm run build` thành công 100%.

- **[THÊM CHỨC NĂNG KHÓA / MỞ KHÓA TÀI KHOẢN HÀNG LOẠT (BULK LOCK & UNLOCK)]**:
  - Triển khai tính năng tích chọn hàng loạt nhiều tài khoản người dùng tại trang Quản lý thành viên (`AdminUsersComponent`).
  - Thêm cột checkbox chọn dòng và checkbox chọn tất cả trên header bảng (`select_header`). Tự động bảo vệ tài khoản Admin hiện tại đang thao tác (không cho phép tự tích chọn khóa chính mình).
  - Thêm 2 nút thao tác hàng loạt linh hoạt trên thanh công cụ Header:
    - **Khóa đã chọn (X)**: Nút màu hổ phách / danger chỉ kích hoạt khi chọn ít nhất 1 tài khoản đang hoạt động.
    - **Mở khóa đã chọn (X)**: Nút màu xanh lá / info chỉ kích hoạt khi chọn ít nhất 1 tài khoản đang bị khóa.
  - Tích hợp Modal xác nhận an toàn (`modalService.confirm`) thông báo số lượng tài khoản sẽ bị ảnh hưởng.
  - Backend API (`POST /v1/admin/users/bulk-status`): Sử dụng DB Transaction để cập nhật hàng loạt trạng thái (`active` / `banned`). Khi khóa hàng loạt, hệ thống tự động loại trừ ID Admin hiện tại, xóa toàn bộ `refresh_token_hash` và tăng `token_version` để thu hồi tức thì toàn bộ JWT Token active của tất cả các tài khoản bị khóa.
  - Đã viết unit & feature test suite đầy đủ (`SecurityHardeningTest.php`), chạy `php artisan test` vượt qua 52/52 test cases (100% PASSED) và Angular `npm run build` thành công 100%.

- **[CHUYỂN CHỌN TRẠNG THÁI TÀI KHOẢN SANG SWITCH & THÊM NÚT KHÓA/MỞ KHÓA BẢNG TÀI KHOẢN]**:
  - Đã chuyển dropdown lựa chọn Trạng thái tài khoản (`statusOptions`) trong `UserFormModalComponent` (`user-form-modal.component.html`) thành công tắc `app-custom-switch` (Bật = Hoạt động, Tắt = Bị khóa / Bị ban).
  - Tích hợp Modal xác nhận (`modalService.confirm`) khi người dùng gạt công tắc switch chuyển trạng thái tài khoản trong modal.
  - Bổ sung nút **Khóa** (màu hổ phách / danger với icon khóa) và **Mở khóa** (màu xanh lá với icon mở khóa) tại cột hành động trong danh sách người dùng (`AdminUsersComponent`), nằm ngay trước nút `+ Cộng / Trừ Số Dư`.
  - Tích hợp Modal xác nhận với thông tin chi tiết tên thành viên (`name`), username (`@username`) và cảnh báo tác động trước khi thực hiện hành động khóa/mở khóa tài khoản.
  - Thêm đầy đủ từ khóa dịch trong `LanguageService` cho cả tiếng Việt và tiếng Anh.
  - Đã biên dịch Angular `npm run build` và kiểm tra backend tests `php artisan test` vượt qua 50/50 test cases thành công 100%.

- **[THÊM CHỨC NĂNG THU HỒI NẠP TIỀN ĐƠN LẺ & HÀNG LOẠT (REVOKE DEPOSITS)]**:
  - Triển khai thành công tính năng Thu hồi số tiền đã nạp cho hóa đơn `completed` (cả đơn lẻ và tích chọn hàng loạt nhiều hóa đơn của nhiều người dùng khác nhau).
  - **Đảm bảo tuyệt đối không để tài khoản user bị âm số dư**:
    - Thu hồi đơn lẻ: Kiểm tra `user.balance >= deposit.amount_usdt`. Nếu số dư không đủ, hệ thống chặn và báo lỗi rõ ràng.
    - Thu hồi hàng loạt: Gom nhóm các hóa đơn theo `user_id`, kiểm tra tổng số tiền cần thu hồi với số dư người dùng. Chỉ thu hồi khi số dư >= tổng tiền cần thu hồi, tự động bỏ qua và báo cáo chi tiết các hóa đơn không đủ điều kiện.
  - Backend (Laravel DDD):
    - Thêm migration `2026_07_23_000001_add_revoked_status_to_deposits_table.php` và cập nhật cột status hỗ trợ `'revoked'`.
    - Thêm `revokeDepositManually()` và `bulkRevokeDepositsManually()` trong `DepositService.php` bọc DB Transaction & locking.
    - Khai báo API endpoints `POST /v1/admin/deposits/{id}/revoke` và `POST /v1/admin/deposits/bulk-revoke` trong `AdminDepositController.php` & `routes/api.php`.
    - Viết test suite đầy đủ cho 2 tính năng, đã chạy `php artisan test` vượt qua 50/50 test cases.
  - Frontend (Angular 18):
    - Cập nhật `TableComponent` hỗ trợ custom header template (cho phép checkbox chọn tất cả trên header).
    - Cập nhật `AdminDepositStore`, `AdminDepositsComponent` & template: Thêm cột checkbox chọn dòng, nút **Thu hồi** cho đơn `completed`, nút **Thu hồi đã chọn (X đơn)** trên thanh công cụ, cập nhật badge/filter cho trạng thái `revoked`, và tích hợp confirm modal an toàn.

## Yêu cầu ban đầu
- Xóa bỏ tất cả các page/feature demo như `home`, `about`, `contact`...
- Đọc và tuân thủ các quy tắc trong `design.md`, `ARCHITECTURE.md` và toàn bộ các tệp tại thư mục `working/`.
- Định hướng sản phẩm: **Enterprise Digital Inventory Management System** phát triển trong nhiều năm cho team 20+ người.
- Luôn ưu tiên: Khả năng mở rộng, dễ bảo trì, mã nguồn sạch. Không làm ẩu để chạy được.
- Đưa ra cảnh báo về Technical Debt nếu có quyết định sai lầm.
- Tận dụng triệt để components tại `isocalmarket-web/src/app/shared/components`.

## Phân tích & Thực hiện
- **[RÀ SOÁT & LÀM SẠCH COMMENT TOÀN BỘ CODEBASE]**:
  - Đã rà soát và phân loại comment trong toàn bộ dự án (Frontend Angular & Backend Laravel).
  - Loại bỏ các comment rác / boilerplate của Laravel trong 20+ file migrations, 3 file middleware, `UserFactory`, `ExampleTest`, `TestCase`, `public/index.php`.
  - Loại bỏ các comment nhãn HTML trùng lặp trong `auth-layout.component.html`, `login.component.html`, `register.component.html`, `admin-dashboard.component.html`, `header.component.html`, `index.html`.
  - Giữ lại 100% các comment quan trọng giải thích logic nghiệp vụ (mã hóa API key, bảo lưu số lượng đã bán của lô hàng, đếm ngược nạp tiền theo thời gian tuyệt đối, token rotation, dung sai đối soát deposit).
  - Đã chạy kiểm tra: `npm run build` biên dịch Angular thành công 100%, `php artisan test` vượt qua 47/47 unit & feature tests.
- **[CẤU HÌNH NETLIFY DEPLOY]**:
  - Đã kiểm tra và cập nhật file [netlify.toml](file:///d:/git/isocalmarket/netlify.toml) ở thư mục gốc và `isocalmarket-web/netlify.toml`.
  - Sửa đường dẫn `publish` từ `dist/isocalmarket-web/browser` sang `dist/angular-web3-wallet/browser` để khớp với tên ứng dụng cấu hình trong `angular.json`.
  - Bổ sung quy tắc redirect SPA (`[[redirects]] from = "/*" to = "/index.html" status = 200`) tránh lỗi 404 khi tải lại trang ở các đường dẫn phụ trên Netlify.
  - Bổ sung `NODE_VERSION = "20"` trong `[build.environment]` để đảm bảo tương thích phiên bản Node.js khi Netlify tiến hành build.
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
    - Đã cập nhật 5 Domain Entities: `?int $id` → `?string $id`, tất cả FK IDs đổi sang `string`.
    - Đã cập nhật 4 Repository Interfaces và 4 Repository Implementations.
    - Đã cập nhật 4 Application Services (AuthService, ProductService, OrderService, InventoryService).
    - Đã cập nhật 3 Controllers (bỏ cast `(int)$id`).
    - Đã sắp xếp theo `created_at` thay vì `id` trong tất cả queries (UUID không sort được theo số).
    - Đã xác nhận UUID7 thực sự được tạo ra: `019f6699-e6d2-70f2-89bd-d3b93946a77d` (prefix time-ordered).
    - Frontend Angular không cần sửa vì stores dùng `any` type.
  - **[SỬA LỖI] Lỗi khởi chạy hệ thống (npm run start)**:
    - Nhận diện lỗi: Khi chạy `npm run be`, PHP 8.3.28 (trong Laragon) bị thoát lập tức với mã lỗi `3236495362` (tương ứng hex `0xC0E90002` do Windows Smart App Control chặn).
    - Phân tích: Do file `php.exe` đi kèm Laragon không được ký số và chưa có độ tin cậy được ghi nhận trên đám mây, dẫn đến bị Smart App Control chặn thực thi. Bản PHP 8.1.10 cũ hơn thì hoạt động bình thường nhưng không tương thích với Laravel 11/12/13 (yêu cầu PHP >= 8.3).
    - Thực hiện khắc phục:
      1. Tải bản PHP 8.3.8 chính thức dạng ZIP từ php.net (bản build chính thức được ký số/đã được xác thực uy tín).
      2. Giải nén vào thư mục `D:\laragon\bin\php\php-8.3.8-Win32-vs16-x64` và tiến hành unblock các file.
      3. Đổi tên thư mục `php-8.3.28-Win32-vs16-x64` bị lỗi thành `.bak` và đổi tên thư mục `php-8.3.8-Win32-vs16-x64` thành `php-8.3.28-Win32-vs16-x64` để hệ thống tự động nhận diện và sử dụng PHP 8.3.8 mà không cần thay đổi biến PATH toàn hệ thống.
      4. Kiểm tra lại `php -v` đã hoạt động bình thường, xuất ra phiên bản PHP 8.3.8 mà không bị crash.
  - **[TỐI ƯU RESPONSIVE & SỬA LỖI ĐĂNG NHẬP]**:
    - Sửa tên tài khoản Admin trong `DatabaseSeeder.php` thành `'admin'` thay vì `'Admin Manager'` để khớp với tài khoản thử nghiệm gợi ý ở màn hình đăng nhập (`admin / admin`).
    - Thực hiện chạy `php artisan migrate:fresh --seed` để làm mới và seed lại dữ liệu chuẩn cho cơ sở dữ liệu.
    - Sửa đổi `HeaderComponent` để inject `Router` và xác định trang xác thực (`isAuthPage()`).
    - Ẩn nút "Đăng nhập" ở Header khi đang ở các trang đăng nhập/đăng ký nhằm tránh trùng lặp.
    - Điều chỉnh padding của khung đăng nhập/đăng ký trên màn hình nhỏ từ `p-8` thành `p-6 sm:p-8` để hiển thị cân đối và gọn gàng hơn.
    - Thêm các lớp CSS `visible` và `invisible` cho Mobile Sidebar Drawer (`app.html`) và Cart Drawer (`storefront.component.html`) kết hợp transition, giúp ẩn hẳn container khi đóng để không cản trở thao tác chuột ở màn hình.
  - **[SỬA LỖI SKELETON LOADING TRÊN BẢNG]**:
    - Sửa đổi cấu trúc bọc `<app-skeleton-loader>` trong các file `products.component.html`, `inventory.component.html` và `orders.component.html`.
    - Thay vì đặt trực tiếp loader làm con của `<tbody>` (làm vỡ cấu trúc DOM vì loader tạo ra `<div>`), đã bọc loader trong thẻ `<tr>` và `<td>` với thuộc tính `colspan` tương ứng với số cột của bảng (`colspan="7"`, `colspan="6"`, `colspan="5"`).
    - Thêm thuộc tính `[showHeader]="false"` vào loader trên bảng để ẩn thanh tìm kiếm giả vì các trang đã có sẵn thanh tìm kiếm thật.
  - **[SỬA LỖI TRANSITION CART DRAWER KHI INIT/CHUYỂN TAB]**:
    - Nhận diện lỗi: Khi người dùng chuyển qua lại giữa các tab (hoặc khi mới tải trang Storefront), giỏ hàng (Cart Drawer) hiển thị chớp nhoáng rồi thụt dần về sau (trượt ẩn vào mép bên phải) tạo hiệu ứng thị giác lỗi.
    - Nguyên nhân: Angular chèn phần tử Drawer vào DOM trước rồi mới áp dụng `[ngClass]` động (`translate-x-full` hoặc `invisible`). Do có class transition (`transition-all` và `transition-transform`) thiết lập sẵn, trình duyệt nhận thấy sự thay đổi từ trạng thái mặc định của DOM sang trạng thái đóng, kích hoạt animation trượt đóng mỗi khi khởi tạo component.
    - Giải pháp:
      1. Khai báo thuộc tính `hasBeenOpened = false` và phương thức `openCart()` trong [storefront.component.ts](file:///d:/git/isocalmarket/isocalmarket-web/src/app/features/storefront/storefront.component.ts).
      2. Trong phương thức `openCart()`, đặt `hasBeenOpened = true` và sử dụng `setTimeout` 50ms để đổi `showCartDrawer = true`, cho phép trình duyệt render xong DOM ban đầu rồi mới kích hoạt transition mở mượt mà.
      3. Bọc khối HTML của Cart Drawer trong điều kiện `@if (hasBeenOpened) { ... }` ở [storefront.component.html](file:///d:/git/isocalmarket/isocalmarket-web/src/app/features/storefront/storefront.component.html). Khi component mới tạo (chưa từng click mở giỏ hàng), Cart Drawer không tồn tại trong DOM nên hoàn toàn tránh được hiệu ứng trượt đóng lỗi.
  - **[SỬA LỖI HIỂN THỊ SKELETON & ĐỊNH TUYẾN API TRÊN CÁC TRANG QUẢN TRỊ]**:
    - Nhận diện lỗi:
      1. Ở trang Quản lý sản phẩm và Quản lý kho hàng, khi đang tải dữ liệu thì skeleton loader hiển thị đè/song song phía trên các hàng dữ liệu cũ.
      2. Ở trang Dashboard, layout skeleton của 4 card thống kê bị vỡ và tràn (hiển thị ô card con đè lên và lồi ra ngoài).
      3. Có Toast lỗi "Không thể tải số liệu thống kê!" và trang Quản lý đơn hàng không load được dữ liệu (mặc dù đăng nhập Admin).
    - Nguyên nhân:
      1. Vòng lặp `*ngFor` của data rows không được bọc trong điều kiện check `!loading()`, dẫn đến render dữ liệu cũ song song với loader.
      2. Thẻ bọc ngoài skeleton list có chiều cao cố định `h-32` nhưng kiểu list lại tạo ra các card con có tổng chiều cao lớn hơn `h-32` gây tràn.
      3. Các endpoint API `admin/dashboard-stats`, `orders`, `orders/{id}` gọi trực tiếp qua `apiService` bị thiếu tiền tố `v1/` trong URL dẫn đến lỗi 404 từ Laravel backend.
    - Giải pháp khắc phục:
      1. Bọc data rows của bảng trong khối `@if (!loading)` ở các file: [products.component.html](file:///d:/git/isocalmarket/isocalmarket-web/src/app/features/products/products.component.html), [inventory.component.html](file:///d:/git/isocalmarket/isocalmarket-web/src/app/features/inventory/inventory.component.html) và [orders.component.html](file:///d:/git/isocalmarket/isocalmarket-web/src/app/features/orders/orders.component.html).
      2. Sửa lại skeleton loading của Dashboard trong [admin-dashboard.component.html](file:///d:/git/isocalmarket/isocalmarket-web/src/app/features/admin-dashboard/admin-dashboard.component.html) thành grid 4 cột nhấp nháy có cấu trúc flex đồng bộ 100% với card thống kê thật (loại bỏ kiểu list gây vỡ layout).
      3. Thêm tiền tố `v1/` vào các hàm gọi API trong [admin-dashboard.component.ts](file:///d:/git/isocalmarket/isocalmarket-web/src/app/features/admin-dashboard/admin-dashboard.component.ts) and [admin-orders.component.ts](file:///d:/git/isocalmarket/isocalmarket-web/src/app/features/admin-orders/admin-orders.component.ts).
  - **[SỬA LỖI RACE CONDITION ĐĂNG NHẬP / CHUYỂN HƯỚNG SAI]**:
    - Nhận diện lỗi: Người dùng truy cập trang quản trị như `/admin/orders`, giao diện Sidebar và Header hiển thị trạng thái đã đăng nhập ("Xin chào, admin") nhưng ở giữa màn hình vẫn hiện Form Đăng nhập.
    - Nguyên nhân: Do bất đồng bộ (race condition). Khi reload app hoặc chuyển hướng, các Guard (`AuthGuard` và `AdminGuard`) chạy đồng bộ và kiểm tra thấy `currentUser` là `null` (vì API `checkMe()` đang chạy chưa xong), thế là Guard chuyển hướng user sang `/auth/login`. Một lát sau, `checkMe()` hoàn thành và cập nhật `currentUser` thành `admin` khiến Header và Sidebar hiển thị trạng thái đã đăng nhập, nhưng URL thì vẫn bị kẹt ở `/auth/login` hiển thị Form.
    - Giải pháp khắc phục:
      1. Cấu hình in-flight HTTP request cache (`checkMeObservable$`) trong [auth.store.ts](file:///d:/git/isocalmarket/isocalmarket-web/src/app/features/auth/auth.store.ts) để tránh gửi trùng lặp request HTTP `/auth/me` song song từ nhiều nơi khi load ứng dụng.
      2. Cập nhật [auth.guard.ts](file:///d:/git/isocalmarket/isocalmarket-web/src/app/core/guards/auth.guard.ts) và [admin.guard.ts](file:///d:/git/isocalmarket/isocalmarket-web/src/app/core/guards/admin.guard.ts): Nếu người dùng chưa có `currentUser` nhưng có cookie đăng nhập `auth_logged_in=true`, Guard sẽ trả về Observable từ `checkMe()` và đợi request hoàn tất trước khi đưa ra quyết định chuyển hướng. Điều này giúp ngăn chặn hoàn toàn việc redirect sai sang trang login khi người dùng reload trang quản trị.
      3. Cập nhật [login.component.ts](file:///d:/git/isocalmarket/isocalmarket-web/src/app/features/auth/login.component.ts) và [register.component.ts](file:///d:/git/isocalmarket/isocalmarket-web/src/app/features/auth/register.component.ts): Thêm Angular `effect()` để theo dõi trạng thái `isAuthenticated()`. Nếu trạng thái này chuyển sang `true`, component sẽ tự động chuyển hướng người dùng về trang đích hợp lệ (Dashboard cho admin hoặc Storefront/returnUrl cho khách hàng).
  - **[SỬA LỖI GIẬT TRANSITION DRAWER, TRÙNG LẶP API ME, VÀ SKELETON STOREFRONT]**:
    - Nhận diện lỗi:
      1. Khi chuyển qua lại các tab quản trị, các Side Drawer (Thêm sản phẩm, Import kho, Chi tiết đơn hàng) tự động trượt ra rồi thụt về, gây giật giao diện rất khó chịu.
      2. Reload trang Quản trị bị báo lỗi Toast "Bạn không có quyền truy cập trang quản trị!" và đá ra storefront dù tài khoản admin đã hiển thị.
      3. Ở tab Network, API `v1/auth/me` bị gọi 3 lần song song khi reload trang.
      4. Skeleton loader danh sách sản phẩm ở Storefront bị bóp méo, nén hẹp thành các cột bé tí tẹo nằm sát nhau.
    - Nguyên nhân:
      1. Tương tự như Cart Drawer, các Side Drawer bị Angular đưa vào DOM trước rồi mới gán class `translate-x-full`/`invisible`. Trình duyệt kích hoạt transition từ trạng thái mặc định sang trạng thái đóng ảo khi init component.
      2. `checkMe()` trả về đối tượng response `{ user: { role: 'admin', ... } }`, nhưng trong `AdminGuard` lại so sánh trực tiếp `user.role === 'admin'` (thiếu `.user` hoặc chưa map), dẫn đến giá trị kiểm tra là `undefined === 'admin'` (sai).
      3. Angular HttpClient trả về Cold Observables, khi có nhiều subscriber (App và các Guards) subscribe đồng thời sẽ sinh ra các request HTTP độc lập song song.
      4. Thẻ bọc ngoài skeleton-loader ở storefront đã bọc class grid 4 cột, trong khi bản thân `SkeletonLoaderComponent` loại `'card-grid'` cũng tự bọc grid 4 cột bên trong. Grid lồng grid khiến toàn bộ block loader bị nhét vào đúng 1 cột của grid cha.
    - Giải pháp khắc phục:
      1. Khai báo `hasBeenOpened = false` và trì hoãn gán `showXDrawer = true` qua `setTimeout` 50ms khi mở cho các Drawer ở `products.component.ts`, `inventory.component.ts`, `admin-orders.component.ts` và `orders.component.ts`. Bọc HTML các Drawer này bằng `@if (hasBeenOpened)` để tránh render chúng vào DOM khi chưa từng click mở.
      2. Cấu hình toán tử `map(res => res?.user || null)` trong `auth.store.ts` để `checkMe()` trả về đúng đối tượng `User` sạch sẽ, giúp `AdminGuard` kiểm tra chính xác.
      3. Bổ sung toán tử `shareReplay(1)` vào pipe của `checkMeObservable$` giúp chia sẻ kết quả của request HTTP `/auth/me` đang chạy cho mọi subscriber đồng thời, giảm từ 3 request song song xuống 1 duy nhất.
      4. Loại bỏ div grid bọc ngoài `<app-skeleton-loader>` trong [storefront.component.html](file:///d:/git/isocalmarket/isocalmarket-web/src/app/features/storefront/storefront.component.html) để component tự vẽ grid của chính nó, sửa hoàn toàn lỗi bóp méo giao diện skeleton.
  - **[THAY THẾ TOÀN BỘ DRAWERS BẰNG DYNAMIC MODALS]**:
    - Yêu cầu: Chuyển đổi toàn bộ các side Drawers hiển thị bên phải (Thêm sản phẩm, Giỏ hàng, Import kho, Chi tiết đơn hàng) sang Modals động được gọi từ file logic `.ts`.
    - Phân tích & Lên kế hoạch:
      1. Đã nghiên cứu `ModalService` hiện tại có sẵn trong dự án. Nó hỗ trợ khởi tạo Component động bằng `ApplicationRef` và chèn vào DOM dạng Modal, hỗ trợ gửi dữ liệu qua `MODAL_DATA` và trả về `ModalRef` để Component cha lắng nghe sự kiện đóng/kết quả.
      2. Đã viết chi tiết kế hoạch thay thế lên [implementation_plan.md](file:///C:/Users/dev/.gemini/antigravity-ide/brain/53c333ad-1140-416e-8608-5f694d30e19e/implementation_plan.md) và gửi người dùng phê duyệt.
    - Thực hiện triển khai & Kết quả:
      1. Đã tạo 4 Dynamic Modal Components: `ProductFormModalComponent`, `ImportInventoryModalComponent`, `OrderDetailModalComponent` (dùng chung cho cả admin và khách) và `CartModalComponent`.
      2. Đã cập nhật 5 file logic cha (`products.component.ts`, `inventory.component.ts`, `orders.component.ts`, `admin-orders.component.ts`, `storefront.component.ts`) để gỡ bỏ toàn bộ code Drawer, form variables, API logic bọc ngoài và thay bằng việc gọi qua `ModalService.open()`.
      3. Đã cập nhật 5 file HTML cha để xóa 100% mã HTML/CSS Drawer cũ, giúp template cực kỳ sạch sẽ và dễ đọc.
      4. Chạy `npm run build` kiểm tra biên dịch thành công 100% không phát sinh bất kỳ lỗi TS/HTML compiler nào.
  - **[KHẮC PHỤC LỖI DUPLICATE API & CHỐNG SPAM CLICK]**:
    - Nhận diện lỗi: Khi nhấn nút Lưu trong `ProductFormModal` hoặc Import trong `ImportInventoryModal`, request API (PUT, POST, và GET load lại danh sách) bị gửi đi trùng lặp nhiều lần (duplicate) và có hiện tượng gửi liên tục hàng chục request pending/200 ở Network.
    - Nguyên nhân:
      1. **Duplicate API**: Các API ghi trong Slice Stores (`ProductStore`, `InventoryStore`, `OrderStore`) tự động gọi lại các phương thức `fetch` của chính nó ở trong `tap()`. Nhưng ở ngoài component sau khi nhận kết quả hoặc modal đóng thành công cũng gọi `fetch`. Điều này tạo ra 2 request tải dữ liệu giống nhau chạy song song.
      2. **Spam Click**: Các nút bấm Lưu/Import chưa có cơ chế disabled/loading. Khi API phản hồi chậm, người dùng bấm nhiều lần khiến trình duyệt gửi liên tiếp hàng chục request chồng chéo nhau.
    - Giải pháp khắc phục:
      1. Loại bỏ hoàn toàn việc tự động gọi `fetch` trong `tap()` của các Store để đảm bảo pure stream RxJS. Trả quyền gọi `fetch` về cho Component cha để quản lý chính xác luồng dữ liệu và giữ nguyên phân trang hiện tại.
      2. Thêm biến trạng thái `isSubmitting` trong các Form Modals, sử dụng `finalize()` để khôi phục trạng thái.
      3. Truyền `[loading]="isSubmitting"` vào các component nút bấm (`app-button`) trong HTML. Khi đang gửi API, nút sẽ tự động chuyển sang loading spinner và chặn click (disabled/pointer-events-none) để ngăn chặn hoàn toàn việc spam click.
      4. Đã chạy biên dịch kiểm tra dự án thành công không có lỗi.
      5. Cấu hình tất cả các Modal (bao gồm cả Confirm Modal của ModalService) đều cho phép đóng khi bấm ra ngoài vùng chứa (closeOnBackdropClick: true).
  - **[TỐI ƯU RESPONSIVE TABLE & THIẾT KẾ LẠI NÚT THAO TÁC]**:
    - Yêu cầu: Responsive toàn bộ các bảng trong hệ thống, đảm bảo các nút thao tác luôn nằm trên 1 hàng, không bị rớt dòng và thiết kế lại kiểu dáng nút thao tác (có viền và nền mờ nhạt đồng bộ màu sắc thương hiệu, nút Xóa màu đỏ).
    - Thực hiện triển khai & Kết quả:
      1. Đã cập nhật 4 file HTML: [products.component.html](file:///d:/git/isocalmarket/isocalmarket-web/src/app/features/products/products.component.html), [inventory.component.html](file:///d:/git/isocalmarket/isocalmarket-web/src/app/features/inventory/inventory.component.html), [orders.component.html](file:///d:/git/isocalmarket/isocalmarket-web/src/app/features/orders/orders.component.html), và [admin-orders.component.html](file:///d:/git/isocalmarket/isocalmarket-web/src/app/features/admin-orders/admin-orders.component.html).
      2. Áp dụng class `whitespace-nowrap` trên tất cả thẻ `<th>` và `<td>` của bảng (trừ các cột chứa văn bản mô tả dài thì sử dụng class `min-w` và `truncate` để giao diện không bị bóp nghẹt).
      3. Bọc các nút thao tác (như Sửa, Xóa, Chi tiết) trong một container flex `flex items-center gap-2 whitespace-nowrap` kèm `whitespace-nowrap` trên thẻ `<td>` tương ứng để ngăn chặn hoàn toàn việc rớt dòng.
      4. Thiết kế lại kiểu dáng nút thao tác theo ảnh yêu cầu:
         - Nút **Sửa**: Dùng màu xanh dương chủ đạo `text-[var(--color-primary)]` với viền mờ `border-[var(--color-primary)]/20` và nền nhạt `bg-[var(--color-primary)]/5` (Dark Mode tương ứng).
         - Nút **Xóa**: Luôn là màu đỏ `text-rose-600` với viền mờ `border-rose-500/20` và nền đỏ nhạt `bg-rose-50` (Dark Mode tương ứng).
         - Nút **Chi tiết**: Dùng màu xanh lục hỗ trợ `text-teal-600` với viền mờ `border-teal-500/20` và nền lục nhạt `bg-teal-50` (Dark Mode tương ứng).
         - Ở trang quản trị đơn hàng (`admin-orders`), đã chuyển đổi nút xem chi tiết từ chỉ icon SVG thô thành nút bấm có chữ "Chi tiết" kèm icon SVG đồng bộ tuyệt đối với trang Orders của khách.
      5. Đã chạy biên dịch kiểm tra dự án thành công 100% không có lỗi.
  - **[TỐI ƯU BẢNG VÀ KHUNG SẢN PHẨM ĐÃ MUA TRONG MODAL CHI TIẾT]**:
    - Yêu cầu: Làm nhạt border và divide của phần "Sản phẩm đã mua" trong modal Chi tiết đơn hàng cho tinh tế và nhã nhặn hơn ở cả Light/Dark Mode.
    - Thực hiện triển khai & Kết quả:
      1. Đã cập nhật [order-detail-modal.component.html](file:///d:/git/isocalmarket/isocalmarket-web/src/app/features/orders/order-detail-modal/order-detail-modal.component.html).
      2. Thay thế `border-slate-150` và `divide-slate-150` ở khung danh sách sản phẩm bằng `border-slate-200/40` và `divide-slate-200/40` (Light Mode), `dark:border-slate-800/40` và `dark:divide-slate-800/40` (Dark Mode).
      3. Đồng bộ hóa toàn bộ các đường viền khác trong modal (khung tóm tắt đơn hàng, tiêu đề, danh sách tài nguyên và phần footer modal) sang định dạng mờ mảnh tương tự, đồng thời sửa lỗi class Tailwind không tồn tại `dark:border-slate-850` thành `dark:border-slate-800/40`.
      4. Đã chạy biên dịch kiểm tra dự án thành công 100% không có lỗi.
  - **[SỬA LỖI HIỂN THỊ SELECT UI VÀ BỎ TRANSITIONS]**:
    - Yêu cầu: Sửa lỗi hiển thị `[object Object]` tại các Select dropdown trên các trang và component, đồng thời bỏ các class transition, animation, duration khỏi component Select UI và các ô nhập liệu `.form-input`.
    - Thực hiện triển khai & Kết quả:
      1. Đặt giá trị mặc định cho `@Input() valueKey = 'value'` và `labelKey = 'label'` trong [custom-select.component.ts](file:///d:/git/isocalmarket/isocalmarket-web/src/app/shared/components/custom-select/custom-select.component.ts) để tự động nhận dạng object option chuẩn `{ label, value }`.
      2. Loại bỏ các class `transition-all`, `transition-transform duration-200` và `transition-colors` trong tệp [custom-select.component.html](file:///d:/git/isocalmarket/isocalmarket-web/src/app/shared/components/custom-select/custom-select.component.html).
      3. Cập nhật các tệp gọi component Select bao gồm: [inventory.component.html](file:///d:/git/isocalmarket/isocalmarket-web/src/app/features/inventory/inventory.component.html), [import-inventory-modal.component.html](file:///d:/git/isocalmarket/isocalmarket-web/src/app/features/inventory/import-inventory-modal/import-inventory-modal.component.html) và [product-form-modal.component.html](file:///d:/git/isocalmarket/isocalmarket-web/src/app/features/products/product-form-modal/product-form-modal.component.html) để bổ sung rõ ràng `valueKey="value"` và `labelKey="label"`.
      4. Loại bỏ class `transition-all duration-200` khỏi `.form-input`, `.form-textarea` và `.search-input` trong tệp stylesheet toàn cục [styles.scss](file:///d:/git/isocalmarket/isocalmarket-web/src/styles.scss).
      5. Đã kiểm tra và chạy build thành công 100% không phát sinh lỗi biên dịch.
  - **[ĐỒNG BỘ PHÂN TRANG 10 RECORD & TỐI ƯU HÓA AUTH 401]**:
    - Yêu cầu: Cấu hình bắt buộc giới hạn phân trang mặc định là 10 bản ghi/trang cho tất cả các trang/danh sách.
    - Thực hiện triển khai & Kết quả:
      1. Phía Frontend: Đổi `limit = 12` thành `limit = 10` trong [storefront.component.ts](file:///d:/git/isocalmarket/isocalmarket-web/src/app/features/storefront/storefront.component.ts); đổi `limit = 15` thành `limit = 10` trong [inventory.component.ts](file:///d:/git/isocalmarket/isocalmarket-web/src/app/features/inventory/inventory.component.ts).
      2. Phía Backend: Đổi limit mặc định thành `10` trong [ProductController.php](file:///d:/git/isocalmarket/isocalmarket-api/app/Http/Controllers/ProductController.php) và [InventoryController.php](file:///d:/git/isocalmarket/isocalmarket-api/app/Http/Controllers/InventoryController.php).
      3. Đơn giản hóa [auth.store.ts](file:///d:/git/isocalmarket/isocalmarket-web/src/app/features/auth/auth.store.ts) — Loại bỏ logic xóa cookie thủ công trong `checkMe()` vì interceptor đã đảm nhận hoàn toàn.
      4. Đã chạy build kiểm tra frontend thành công không phát sinh lỗi biên dịch.
  - **[THÊM CHỨC NĂNG THÔNG TIN CÁ NHÂN, ĐỔI MẬT KHẨU & BẢO MẬT 2FA]**:
    - Yêu cầu: Cho phép người dùng chỉnh sửa số điện thoại (username/email hiển thị readonly và disable). Hỗ trợ đổi mật khẩu (3 trường: mật khẩu cũ, mật khẩu mới, xác nhận mật khẩu mới). Hỗ trợ bảo mật 2 lớp (2FA) bằng Google Authenticator.
    - Thực hiện triển khai & Kết quả:
      1. Database: Tạo và chạy migration bổ sung các cột `phone`, `google2fa_secret` và `google2fa_enabled` vào bảng `users`.
      2. Backend Domain & Infrastructure: Cập nhật `UserModel`, Domain `User` Entity, và `UserMapper` để đồng bộ các trường mới.
      3. Helper TOTP: Tạo class `Google2FA` xử lý sinh secret key, sinh OTP Auth URL và verify mã OTP 6 chữ số bằng thuật toán PHP thuần (không phụ thuộc package ngoài).
      4. Backend Controllers & Routes:
         - Viết `ProfileController` cung cấp các API cập nhật phone, đổi mật khẩu, kích hoạt/xác nhận/hủy 2FA.
         - Cập nhật `AuthController` khi login thành công có kiểm tra 2FA. Nếu bật 2FA, trả về `{ requires_2fa: true, temp_token }` để yêu cầu tiếp OTP đăng nhập qua API `/auth/login/2fa`.
      5. Frontend:
         - Cài đặt thư viện `ng-qrcode` để vẽ mã QR trực tiếp tại trình duyệt.
         - Cập nhật `AuthStore` thêm các API call cho profile/2FA và cập nhật hàm login hỗ trợ 2FA.
         - Xây dựng hệ thống Routing cho Profile: Đăng ký route `/profile` trong `app.routes.ts` với lazy child routes `/profile/information`, `/profile/password`, `/profile/security` trong `profile.routes.ts`.
         - Xây dựng component cha `ProfileComponent` thiết kế layout 2 cột (Desktop) và responsive tabs cuộn ngang (Mobile).
         - Xây dựng 3 component trang con: `ProfileInformationComponent` (SĐT), `ProfilePasswordComponent` (mật khẩu) và `ProfileSecurityComponent` (2FA).
         - Cập nhật `LoginComponent` hỗ trợ hiển thị màn hình nhập mã OTP khi tài khoản bật 2FA.
         - Cập nhật `HeaderComponent` (ts & html) để liên kết nút "Thông tin cá nhân" bằng `routerLink="/profile"` và tự đóng dropdown.
         - Xóa bỏ modal profile cũ không sử dụng.
         - **[TỐI ƯU LAYOUT PROFILE & 2FA MODALS]**: Nhận phản hồi về việc sidebar bị ép chữ và yêu cầu dùng modal xác thực 2FA. Đã thiết kế lại `ProfileComponent` dùng grid 12 cột (`md:col-span-4 lg:col-span-3` cho sidebar) kèm `whitespace-nowrap` chống gãy chữ. Tạo 2 modal động `Enable2faModalComponent` và `Disable2faModalComponent`, cập nhật `ProfileSecurityComponent` liên kết mở modal qua `ModalService.open`.
       6. Đã chạy biên dịch Angular frontend (`npm run build`) và unit test suite Laravel backend (`php artisan test`) thành công 100% không phát sinh lỗi.
  - **[HOÀN THÀNH] Đồng bộ Shared Components**:
    - Yêu cầu: Xem lại toàn bộ web, đảm bảo tất cả các trang đã kế thừa sử dụng các shared components có sẵn (`app-page-header`, `app-badge`, `app-icon`, `app-card`). Đồng bộ hóa giao diện lại toàn bộ.
    - Thực hiện triển khai & Kết quả:
      1. Đã cập nhật 8 features và 2 shared layouts (Header, Sidebar, Mobile Sidebar) để chuyển đổi 100% code thô, div thủ công, badge và inline SVG sang sử dụng:
         - `<app-page-header>` cho tiêu đề trang và thanh tìm kiếm.
         - `<app-card>` cho các khung chứa, card thống kê, bảng dữ liệu, và form Đăng nhập / Đăng ký.
         - Sử dụng `!p-0` cho các card chứa bảng hoặc khối tiêu đề trong để cho phép nội dung chạy tràn viền tự nhiên, tránh bị co nhỏ padding.
         - Override class nền kính mờ (`!bg-white/60 dark:!bg-slate-900/60 backdrop-blur-md`) trên Login/Register card để giữ hiệu ứng glassmorphism đẳng cấp.
         - `<app-badge>` cho trạng thái tồn kho, trạng thái đơn hàng, trạng thái 2FA và bộ đếm.
         - `<app-icon>` thay cho toàn bộ các inline SVG thô giúp mã nguồn cực kỳ gọn gàng.
      2. Cập nhật Sidebar và Mobile Sidebar sử dụng đúng icon theo chức năng (`dashboard`, `list-bullet`, `inventory`, `orders`).
      3. Dọn dẹp các warning về imports không sử dụng (`SkeletonLoaderComponent` trong `AdminDashboardComponent`, `ButtonComponent` trong `HeaderComponent`).
       4. Chạy `npm run build` biên dịch thành công 100% không phát sinh lỗi.
  - **[SỬA LỖI HIỂN THỊ HEADER & THAY ĐỔI XÁC THỰC TẮT 2FA]**:
    - Yêu cầu:
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
  - **[SỬA LỖI] Lỗi khởi chạy hệ thống (npm run start)**:
    - Nhận diện lỗi: Khi chạy `npm run be`, PHP 8.3.28 (trong Laragon) bị thoát lập tức với mã lỗi `3236495362` (tương ứng hex `0xC0E90002` do Windows Smart App Control chặn).
    - Phân tích: Do file `php.exe` đi kèm Laragon không được ký số và chưa có độ tin cậy được ghi nhận trên đám mây, dẫn đến bị Smart App Control chặn thực thi. Bản PHP 8.1.10 cũ hơn thì hoạt động bình thường nhưng không tương thích với Laravel 11/12/13 (yêu cầu PHP >= 8.3).
    - Thực hiện khắc phục:
      1. Tải bản PHP 8.3.8 chính thức dạng ZIP từ php.net (bản build chính thức được ký số/đã được xác thực uy tín).
      2. Giải nén vào thư mục `D:\laragon\bin\php\php-8.3.8-Win32-vs16-x64` và tiến hành unblock các file.
      3. Đổi tên thư mục `php-8.3.28-Win32-vs16-x64` bị lỗi thành `.bak` và đổi tên thư mục `php-8.3.8-Win32-vs16-x64` thành `php-8.3.28-Win32-vs16-x64` để hệ thống tự động nhận diện và sử dụng PHP 8.3.8 mà không cần thay đổi biến PATH toàn hệ thống.
      4. Kiểm tra lại `php -v` đã hoạt động bình thường, xuất ra phiên bản PHP 8.3.8 mà không bị crash.
  - **[TỐI ƯU RESPONSIVE & SỬA LỖI ĐĂNG NHẬP]**:
    - Sửa tên tài khoản Admin trong `DatabaseSeeder.php` thành `'admin'` thay vì `'Admin Manager'` để khớp với tài khoản thử nghiệm gợi ý ở màn hình đăng nhập (`admin / admin`).
    - Thực hiện chạy `php artisan migrate:fresh --seed` để làm mới và seed lại dữ liệu chuẩn cho cơ sở dữ liệu.
    - Sửa đổi `HeaderComponent` để inject `Router` và xác định trang xác thực (`isAuthPage()`).
    - Ẩn nút "Đăng nhập" ở Header khi đang ở các trang đăng nhập/đăng ký nhằm tránh trùng lặp.
    - Điều chỉnh padding của khung đăng nhập/đăng ký trên màn hình nhỏ từ `p-8` thành `p-6 sm:p-8` để hiển thị cân đối và gọn gàng hơn.
    - Thêm các lớp CSS `visible` và `invisible` cho Mobile Sidebar Drawer (`app.html`) và Cart Drawer (`storefront.component.html`) kết hợp transition, giúp ẩn hẳn container khi đóng để không cản trở thao tác chuột ở màn hình.
  - **[SỬA LỖI SKELETON LOADING TRÊN BẢNG]**:
    - Sửa đổi cấu trúc bọc `<app-skeleton-loader>` trong các file `products.component.html`, `inventory.component.html` và `orders.component.html`.
    - Thay vì đặt trực tiếp loader làm con của `<tbody>` (làm vỡ cấu trúc DOM vì loader tạo ra `<div>`), đã bọc loader trong thẻ `<tr>` và `<td>` với thuộc tính `colspan` tương ứng với số cột của bảng (`colspan="7"`, `colspan="6"`, `colspan="5"`).
    - Thêm thuộc tính `[showHeader]="false"` vào loader trên bảng để ẩn thanh tìm kiếm giả vì các trang đã có sẵn thanh tìm kiếm thật.
  - **[SỬA LỖI TRANSITION CART DRAWER KHI INIT/CHUYỂN TAB]**:
    - Nhận diện lỗi: Khi người dùng chuyển qua lại giữa các tab (hoặc khi mới tải trang Storefront), giỏ hàng (Cart Drawer) hiển thị chớp nhoáng rồi thụt dần về sau (trượt ẩn vào mép bên phải) tạo hiệu ứng thị giác lỗi.
    - Nguyên nhân: Angular chèn phần tử Drawer vào DOM trước rồi mới áp dụng `[ngClass]` động (`translate-x-full` hoặc `invisible`). Do có class transition (`transition-all` và `transition-transform`) thiết lập sẵn, trình duyệt nhận thấy sự thay đổi từ trạng thái mặc định của DOM sang trạng thái đóng, kích hoạt animation trượt đóng mỗi khi khởi tạo component.
    - Giải pháp:
      1. Khai báo thuộc tính `hasBeenOpened = false` và phương thức `openCart()` trong [storefront.component.ts](file:///d:/git/isocalmarket/isocalmarket-web/src/app/features/storefront/storefront.component.ts).
      2. Trong phương thức `openCart()`, đặt `hasBeenOpened = true` và sử dụng `setTimeout` 50ms để đổi `showCartDrawer = true`, cho phép trình duyệt render xong DOM ban đầu rồi mới kích hoạt transition mở mượt mà.
      3. Bọc khối HTML của Cart Drawer trong điều kiện `@if (hasBeenOpened) { ... }` ở [storefront.component.html](file:///d:/git/isocalmarket/isocalmarket-web/src/app/features/storefront/storefront.component.html). Khi component mới tạo (chưa từng click mở giỏ hàng), Cart Drawer không tồn tại trong DOM nên hoàn toàn tránh được hiệu ứng trượt đóng lỗi.
  - **[SỬA LỖI HIỂN THỊ SKELETON & ĐỊNH TUYẾN API TRÊN CÁC TRANG QUẢN TRỊ]**:
    - Nhận diện lỗi:
      1. Ở trang Quản lý sản phẩm và Quản lý kho hàng, khi đang tải dữ liệu thì skeleton loader hiển thị đè/song song phía trên các hàng dữ liệu cũ.
      2. Ở trang Dashboard, layout skeleton của 4 card thống kê bị vỡ và tràn (hiển thị ô card con đè lên và lồi ra ngoài).
      3. Có Toast lỗi "Không thể tải số liệu thống kê!" và trang Quản lý đơn hàng không load được dữ liệu (mặc dù đăng nhập Admin).
    - Nguyên nhân:
      1. Vòng lặp `*ngFor` của data rows không được bọc trong điều kiện check `!loading()`, dẫn đến render dữ liệu cũ song song với loader.
      2. Thẻ bọc ngoài skeleton list có chiều cao cố định `h-32` nhưng kiểu list lại tạo ra các card con có tổng chiều cao lớn hơn `h-32` gây tràn.
      3. Các endpoint API `admin/dashboard-stats`, `orders`, `orders/{id}` gọi trực tiếp qua `apiService` bị thiếu tiền tố `v1/` trong URL dẫn đến lỗi 404 từ Laravel backend.
    - Giải pháp khắc phục:
      1. Bọc data rows của bảng trong khối `@if (!loading)` ở các file: [products.component.html](file:///d:/git/isocalmarket/isocalmarket-web/src/app/features/products/products.component.html), [inventory.component.html](file:///d:/git/isocalmarket/isocalmarket-web/src/app/features/inventory/inventory.component.html) và [orders.component.html](file:///d:/git/isocalmarket/isocalmarket-web/src/app/features/orders/orders.component.html).
      2. Sửa lại skeleton loading của Dashboard trong [admin-dashboard.component.html](file:///d:/git/isocalmarket/isocalmarket-web/src/app/features/admin-dashboard/admin-dashboard.component.html) thành grid 4 cột nhấp nháy có cấu trúc flex đồng bộ 100% với card thống kê thật (loại bỏ kiểu list gây vỡ layout).
      3. Thêm tiền tố `v1/` vào các hàm gọi API trong [admin-dashboard.component.ts](file:///d:/git/isocalmarket/isocalmarket-web/src/app/features/admin-dashboard/admin-dashboard.component.ts) and [admin-orders.component.ts](file:///d:/git/isocalmarket/isocalmarket-web/src/app/features/admin-orders/admin-orders.component.ts).
  - **[SỬA LỖI RACE CONDITION ĐĂNG NHẬP / CHUYỂN HƯỚNG SAI]**:
    - Nhận diện lỗi: Người dùng truy cập trang quản trị như `/admin/orders`, giao diện Sidebar và Header hiển thị trạng thái đã đăng nhập ("Xin chào, admin") nhưng ở giữa màn hình vẫn hiện Form Đăng nhập.
    - Nguyên nhân: Do bất đồng bộ (race condition). Khi reload app hoặc chuyển hướng, các Guard (`AuthGuard` và `AdminGuard`) chạy đồng bộ và kiểm tra thấy `currentUser` là `null` (vì API `checkMe()` đang chạy chưa xong), thế là Guard chuyển hướng user sang `/auth/login`. Một lát sau, `checkMe()` hoàn thành và cập nhật `currentUser` thành `admin` khiến Header và Sidebar hiển thị trạng thái đã đăng nhập, nhưng URL thì vẫn bị kẹt ở `/auth/login` hiển thị Form.
    - Giải pháp khắc phục:
      1. Cấu hình in-flight HTTP request cache (`checkMeObservable$`) trong [auth.store.ts](file:///d:/git/isocalmarket/isocalmarket-web/src/app/features/auth/auth.store.ts) để tránh gửi trùng lặp request HTTP `/auth/me` song song từ nhiều nơi khi load ứng dụng.
      2. Cập nhật [auth.guard.ts](file:///d:/git/isocalmarket/isocalmarket-web/src/app/core/guards/auth.guard.ts) và [admin.guard.ts](file:///d:/git/isocalmarket/isocalmarket-web/src/app/core/guards/admin.guard.ts): Nếu người dùng chưa có `currentUser` nhưng có cookie đăng nhập `auth_logged_in=true`, Guard sẽ trả về Observable từ `checkMe()` và đợi request hoàn tất trước khi đưa ra quyết định chuyển hướng. Điều này giúp ngăn chặn hoàn toàn việc redirect sai sang trang login khi người dùng reload trang quản trị.
      3. Cập nhật [login.component.ts](file:///d:/git/isocalmarket/isocalmarket-web/src/app/features/auth/login.component.ts) và [register.component.ts](file:///d:/git/isocalmarket/isocalmarket-web/src/app/features/auth/register.component.ts): Thêm Angular `effect()` để theo dõi trạng thái `isAuthenticated()`. Nếu trạng thái này chuyển sang `true`, component sẽ tự động chuyển hướng người dùng về trang đích hợp lệ (Dashboard cho admin hoặc Storefront/returnUrl cho khách hàng).
  - **[SỬA LỖI GIẬT TRANSITION DRAWER, TRÙNG LẶP API ME, VÀ SKELETON STOREFRONT]**:
    - Nhận diện lỗi:
      1. Khi chuyển qua lại các tab quản trị, các Side Drawer (Thêm sản phẩm, Import kho, Chi tiết đơn hàng) tự động trượt ra rồi thụt về, gây giật giao diện rất khó chịu.
      2. Reload trang Quản trị bị báo lỗi Toast "Bạn không có quyền truy cập trang quản trị!" và đá ra storefront dù tài khoản admin đã hiển thị.
      3. Ở tab Network, API `v1/auth/me` bị gọi 3 lần song song khi reload trang.
      4. Skeleton loader danh sách sản phẩm ở Storefront bị bóp méo, nén hẹp thành các cột bé tí tẹo nằm sát nhau.
    - Nguyên nhân:
      1. Tương tự như Cart Drawer, các Side Drawer bị Angular đưa vào DOM trước rồi mới gán class `translate-x-full`/`invisible`. Trình duyệt kích hoạt transition từ trạng thái mặc định sang trạng thái đóng ảo khi init component.
      2. `checkMe()` trả về đối tượng response `{ user: { role: 'admin', ... } }`, nhưng trong `AdminGuard` lại so sánh trực tiếp `user.role === 'admin'` (thiếu `.user` hoặc chưa map), dẫn đến giá trị kiểm tra là `undefined === 'admin'` (sai).
      3. Angular HttpClient trả về Cold Observables, khi có nhiều subscriber (App và các Guards) subscribe đồng thời sẽ sinh ra các request HTTP độc lập song song.
      4. Thẻ bọc ngoài skeleton-loader ở storefront đã bọc class grid 4 cột, trong khi bản thân `SkeletonLoaderComponent` loại `'card-grid'` cũng tự bọc grid 4 cột bên trong. Grid lồng grid khiến toàn bộ block loader bị nhét vào đúng 1 cột của grid cha.
    - Giải pháp khắc phục:
      1. Khai báo `hasBeenOpened = false` và trì hoãn gán `showXDrawer = true` qua `setTimeout` 50ms khi mở cho các Drawer ở `products.component.ts`, `inventory.component.ts`, `admin-orders.component.ts` và `orders.component.ts`. Bọc HTML các Drawer này bằng `@if (hasBeenOpened)` để tránh render chúng vào DOM khi chưa từng click mở.
      2. Cấu hình toán tử `map(res => res?.user || null)` trong `auth.store.ts` để `checkMe()` trả về đúng đối tượng `User` sạch sẽ, giúp `AdminGuard` kiểm tra chính xác.
      3. Bổ sung toán tử `shareReplay(1)` vào pipe của `checkMeObservable$` giúp chia sẻ kết quả của request HTTP `/auth/me` đang chạy cho mọi subscriber đồng thời, giảm từ 3 request song song xuống 1 duy nhất.
      4. Loại bỏ div grid bọc ngoài `<app-skeleton-loader>` trong [storefront.component.html](file:///d:/git/isocalmarket/isocalmarket-web/src/app/features/storefront/storefront.component.html) để component tự vẽ grid của chính nó, sửa hoàn toàn lỗi bóp méo giao diện skeleton.
  - **[THAY THẾ TOÀN BỘ DRAWERS BẰNG DYNAMIC MODALS]**:
    - Yêu cầu: Chuyển đổi toàn bộ các side Drawers hiển thị bên phải (Thêm sản phẩm, Giỏ hàng, Import kho, Chi tiết đơn hàng) sang Modals động được gọi từ file logic `.ts`.
    - Phân tích & Lên kế hoạch:
      1. Đã nghiên cứu `ModalService` hiện tại có sẵn trong dự án. Nó hỗ trợ khởi tạo Component động bằng `ApplicationRef` và chèn vào DOM dạng Modal, hỗ trợ gửi dữ liệu qua `MODAL_DATA` và trả về `ModalRef` để Component cha lắng nghe sự kiện đóng/kết quả.
      2. Đã viết chi tiết kế hoạch thay thế lên [implementation_plan.md](file:///C:/Users/dev/.gemini/antigravity-ide/brain/53c333ad-1140-416e-8608-5f694d30e19e/implementation_plan.md) và gửi người dùng phê duyệt.
    - Thực hiện triển khai & Kết quả:
      1. Đã tạo 4 Dynamic Modal Components: `ProductFormModalComponent`, `ImportInventoryModalComponent`, `OrderDetailModalComponent` (dùng chung cho cả admin và khách) và `CartModalComponent`.
      2. Đã cập nhật 5 file logic cha (`products.component.ts`, `inventory.component.ts`, `orders.component.ts`, `admin-orders.component.ts`, `storefront.component.ts`) để gỡ bỏ toàn bộ code Drawer, form variables, API logic bọc ngoài và thay bằng việc gọi qua `ModalService.open()`.
      3. Đã cập nhật 5 file HTML cha để xóa 100% mã HTML/CSS Drawer cũ, giúp template cực kỳ sạch sẽ và dễ đọc.
      4. Chạy `npm run build` kiểm tra biên dịch thành công 100% không phát sinh bất kỳ lỗi TS/HTML compiler nào.
  - **[KHẮC PHỤC LỖI DUPLICATE API & CHỐNG SPAM CLICK]**:
    - Nhận diện lỗi: Khi nhấn nút Lưu trong `ProductFormModal` hoặc Import trong `ImportInventoryModal`, request API (PUT, POST, và GET load lại danh sách) bị gửi đi trùng lặp nhiều lần (duplicate) và có hiện tượng gửi liên tục hàng chục request pending/200 ở Network.
    - Nguyên nhân:
      1. **Duplicate API**: Các API ghi trong Slice Stores (`ProductStore`, `InventoryStore`, `OrderStore`) tự động gọi lại các phương thức `fetch` của chính nó ở trong `tap()`. Nhưng ở ngoài component sau khi nhận kết quả hoặc modal đóng thành công cũng gọi `fetch`. Điều này tạo ra 2 request tải dữ liệu giống nhau chạy song song.
      2. **Spam Click**: Các nút bấm Lưu/Import chưa có cơ chế disabled/loading. Khi API phản hồi chậm, người dùng bấm nhiều lần khiến trình duyệt gửi liên tiếp hàng chục request chồng chéo nhau.
    - Giải pháp khắc phục:
      1. Loại bỏ hoàn toàn việc tự động gọi `fetch` trong `tap()` của các Store để đảm bảo pure stream RxJS. Trả quyền gọi `fetch` về cho Component cha để quản lý chính xác luồng dữ liệu và giữ nguyên phân trang hiện tại.
      2. Thêm biến trạng thái `isSubmitting` trong các Form Modals, sử dụng `finalize()` để khôi phục trạng thái.
      3. Truyền `[loading]="isSubmitting"` vào các component nút bấm (`app-button`) trong HTML. Khi đang gửi API, nút sẽ tự động chuyển sang loading spinner và chặn click (disabled/pointer-events-none) để ngăn chặn hoàn toàn việc spam click.
      4. Đã chạy biên dịch kiểm tra dự án thành công không có lỗi.
      5. Cấu hình tất cả các Modal (bao gồm cả Confirm Modal của ModalService) đều cho phép đóng khi bấm ra ngoài vùng chứa (closeOnBackdropClick: true).
  - **[TỐI ƯU RESPONSIVE TABLE & THIẾT KẾ LẠI NÚT THAO TÁC]**:
    - Yêu cầu: Responsive toàn bộ các bảng trong hệ thống, đảm bảo các nút thao tác luôn nằm trên 1 hàng, không bị rớt dòng và thiết kế lại kiểu dáng nút thao tác (có viền và nền mờ nhạt đồng bộ màu sắc thương hiệu, nút Xóa màu đỏ).
    - Thực hiện triển khai & Kết quả:
      1. Đã cập nhật 4 file HTML: [products.component.html](file:///d:/git/isocalmarket/isocalmarket-web/src/app/features/products/products.component.html), [inventory.component.html](file:///d:/git/isocalmarket/isocalmarket-web/src/app/features/inventory/inventory.component.html), [orders.component.html](file:///d:/git/isocalmarket/isocalmarket-web/src/app/features/orders/orders.component.html), và [admin-orders.component.html](file:///d:/git/isocalmarket/isocalmarket-web/src/app/features/admin-orders/admin-orders.component.html).
      2. Áp dụng class `whitespace-nowrap` trên tất cả thẻ `<th>` và `<td>` của bảng (trừ các cột chứa văn bản mô tả dài thì sử dụng class `min-w` và `truncate` để giao diện không bị bóp nghẹt).
      3. Bọc các nút thao tác (như Sửa, Xóa, Chi tiết) trong một container flex `flex items-center gap-2 whitespace-nowrap` kèm `whitespace-nowrap` trên thẻ `<td>` tương ứng để ngăn chặn hoàn toàn việc rớt dòng.
      4. Thiết kế lại kiểu dáng nút thao tác theo ảnh yêu cầu:
         - Nút **Sửa**: Dùng màu xanh dương chủ đạo `text-[var(--color-primary)]` với viền mờ `border-[var(--color-primary)]/20` và nền nhạt `bg-[var(--color-primary)]/5` (Dark Mode tương ứng).
         - Nút **Xóa**: Luôn là màu đỏ `text-rose-600` với viền mờ `border-rose-500/20` và nền đỏ nhạt `bg-rose-50` (Dark Mode tương ứng).
         - Nút **Chi tiết**: Dùng màu xanh lục hỗ trợ `text-teal-600` với viền mờ `border-teal-500/20` và nền lục nhạt `bg-teal-50` (Dark Mode tương ứng).
         - Ở trang quản trị đơn hàng (`admin-orders`), đã chuyển đổi nút xem chi tiết từ chỉ icon SVG thô thành nút bấm có chữ "Chi tiết" kèm icon SVG đồng bộ tuyệt đối với trang Orders của khách.
      5. Đã chạy biên dịch kiểm tra dự án thành công 100% không có lỗi.
  - **[TỐI ƯU BẢNG VÀ KHUNG SẢN PHẨM ĐÃ MUA TRONG MODAL CHI TIẾT]**:
    - Yêu cầu: Làm nhạt border và divide của phần "Sản phẩm đã mua" trong modal Chi tiết đơn hàng cho tinh tế và nhã nhặn hơn ở cả Light/Dark Mode.
    - Thực hiện triển khai & Kết quả:
      1. Đã cập nhật [order-detail-modal.component.html](file:///d:/git/isocalmarket/isocalmarket-web/src/app/features/orders/order-detail-modal/order-detail-modal.component.html).
      2. Thay thế `border-slate-150` và `divide-slate-150` ở khung danh sách sản phẩm bằng `border-slate-200/40` và `divide-slate-200/40` (Light Mode), `dark:border-slate-800/40` và `dark:divide-slate-800/40` (Dark Mode).
      3. Đồng bộ hóa toàn bộ các đường viền khác trong modal (khung tóm tắt đơn hàng, tiêu đề, danh sách tài nguyên và phần footer modal) sang định dạng mờ mảnh tương tự, đồng thời sửa lỗi class Tailwind không tồn tại `dark:border-slate-850` thành `dark:border-slate-800/40`.
      4. Đã chạy biên dịch kiểm tra dự án thành công 100% không có lỗi.
  - **[SỬA LỖI HIỂN THỊ SELECT UI VÀ BỎ TRANSITIONS]**:
    - Yêu cầu: Sửa lỗi hiển thị `[object Object]` tại các Select dropdown trên các trang và component, đồng thời bỏ các class transition, animation, duration khỏi component Select UI và các ô nhập liệu `.form-input`.
    - Thực hiện triển khai & Kết quả:
      1. Đặt giá trị mặc định cho `@Input() valueKey = 'value'` và `labelKey = 'label'` trong [custom-select.component.ts](file:///d:/git/isocalmarket/isocalmarket-web/src/app/shared/components/custom-select/custom-select.component.ts) để tự động nhận dạng object option chuẩn `{ label, value }`.
      2. Loại bỏ các class `transition-all`, `transition-transform duration-200` và `transition-colors` trong tệp [custom-select.component.html](file:///d:/git/isocalmarket/isocalmarket-web/src/app/shared/components/custom-select/custom-select.component.html).
      3. Cập nhật các tệp gọi component Select bao gồm: [inventory.component.html](file:///d:/git/isocalmarket/isocalmarket-web/src/app/features/inventory/inventory.component.html), [import-inventory-modal.component.html](file:///d:/git/isocalmarket/isocalmarket-web/src/app/features/inventory/import-inventory-modal/import-inventory-modal.component.html) và [product-form-modal.component.html](file:///d:/git/isocalmarket/isocalmarket-web/src/app/features/products/product-form-modal/product-form-modal.component.html) để bổ sung rõ ràng `valueKey="value"` và `labelKey="label"`.
      4. Loại bỏ class `transition-all duration-200` khỏi `.form-input`, `.form-textarea` và `.search-input` trong tệp stylesheet toàn cục [styles.scss](file:///d:/git/isocalmarket/isocalmarket-web/src/styles.scss).
      5. Đã kiểm tra và chạy build thành công 100% không phát sinh lỗi biên dịch.
  - **[ĐỒNG BỘ PHÂN TRANG 10 RECORD & TỐI ƯU HÓA AUTH 401]**:
    - Yêu cầu: Cấu hình bắt buộc giới hạn phân trang mặc định là 10 bản ghi/trang cho tất cả các trang/danh sách.
    - Thực hiện triển khai & Kết quả:
      1. Phía Frontend: Đổi `limit = 12` thành `limit = 10` trong [storefront.component.ts](file:///d:/git/isocalmarket/isocalmarket-web/src/app/features/storefront/storefront.component.ts); đổi `limit = 15` thành `limit = 10` trong [inventory.component.ts](file:///d:/git/isocalmarket/isocalmarket-web/src/app/features/inventory/inventory.component.ts).
      2. Phía Backend: Đổi limit mặc định thành `10` trong [ProductController.php](file:///d:/git/isocalmarket/isocalmarket-api/app/Http/Controllers/ProductController.php) và [InventoryController.php](file:///d:/git/isocalmarket/isocalmarket-api/app/Http/Controllers/InventoryController.php).
      3. Xử lý lỗi 401: Bổ sung logic tự động xóa cookie `auth_logged_in` trong block `catchError` của hàm `checkMe()` tại [auth.store.ts](file:///d:/git/isocalmarket/isocalmarket-web/src/app/features/auth/auth.store.ts) giúp tránh tình trạng kẹt phiên đăng nhập ảo và hỗ trợ định tuyến Guards chính xác khi session hết hạn trên server.
      4. Đã chạy build kiểm tra frontend thành công không phát sinh lỗi biên dịch.
  - **[TRIỂN KHAI JWT HTTP INTERCEPTOR AUTO-REFRESH TOKEN]**:
    - Yêu cầu: Người dùng không bao giờ phải đăng nhập lại trong vòng 7 ngày (miễn là còn truy cập web). Hệ thống phải tự động làm mới `access_token` ngầm khi nó hết hạn (30 phút).
    - Phân tích: Backend Laravel đã hoàn chỉnh 100% — đã có endpoint `POST /api/v1/auth/refresh` nhận `refresh_token` từ cookie HttpOnly, tạo cặp token mới và trả về. Phần còn thiếu duy nhất là HTTP Interceptor ở Frontend Angular.
    - Thực hiện triển khai & Kết quả:
      1. Tạo mới [auth.interceptor.ts](file:///d:/git/isocalmarket/isocalmarket-web/src/app/core/interceptors/auth.interceptor.ts) — Interceptor dạng functional (Angular 16+) xử lý toàn bộ luồng tự động refresh:
         - Bắt lỗi 401 từ bất kỳ API nào (ngoại trừ chính endpoint `/auth/refresh` để tránh vòng lặp vô hạn).
         - Nếu `access_token` hết hạn: gọi ngầm `POST /api/v1/auth/refresh`.
         - Sau khi refresh thành công: gửi lại request gốc đã bị 401 — người dùng hoàn toàn không nhận ra gián đoạn.
         - Nếu có nhiều request 401 cùng lúc: dùng `BehaviorSubject` để xếp hàng chờ — chỉ gọi refresh **1 lần duy nhất**, tránh spam API.
         - Nếu `refresh_token` cũng hết hạn (sau 7 ngày không truy cập): xóa cookie `auth_logged_in`, chuyển người dùng về `/auth/login`.
      2. Cập nhật [app.config.ts](file:///d:/git/isocalmarket/isocalmarket-web/src/app/app.config.ts) — Đăng ký interceptor vào `provideHttpClient(withInterceptors([authInterceptor]))`.
      3. Đơn giản hóa [auth.store.ts](file:///d:/git/isocalmarket/isocalmarket-web/src/app/features/auth/auth.store.ts) — Loại bỏ logic xóa cookie thủ công trong `checkMe()` vì interceptor đã đảm nhận hoàn toàn.
      4. Đã chạy build kiểm tra frontend thành công không phát sinh lỗi biên dịch.
  - **[TRIỂN KHAI JWT HTTP INTERCEPTOR AUTO-REFRESH TOKEN]**:
    - Yêu cầu: Người dùng không bao giờ phải đăng nhập lại trong vòng 7 ngày (miễn là còn truy cập web). Hệ thống phải tự động làm mới `access_token` ngầm khi nó hết hạn (30 phút).
    - Phân tích: Backend Laravel đã hoàn chỉnh 100% — đã có endpoint `POST /api/v1/auth/refresh` nhận `refresh_token` từ cookie HttpOnly, tạo cặp token mới và trả về. Phần còn thiếu duy nhất là HTTP Interceptor ở Frontend Angular.
    - Thực hiện triển khai & Kết quả:
      1. Tạo mới [auth.interceptor.ts](file:///d:/git/isocalmarket/isocalmarket-web/src/app/core/interceptors/auth.interceptor.ts) — Interceptor dạng functional (Angular 16+) xử lý toàn bộ luồng tự động refresh:
         - Bắt lỗi 401 từ bất kỳ API nào (ngoại trừ chính endpoint `/auth/refresh` để tránh vòng lặp vô hạn).
         - Nếu `access_token` hết hạn: gọi ngầm `POST /api/v1/auth/refresh`.
         - Sau khi refresh thành công: gửi lại request gốc đã bị 401 — người dùng hoàn toàn không nhận ra gián đoạn.
         - Nếu có nhiều request 401 cùng lúc: dùng `BehaviorSubject` để xếp hàng chờ — chỉ gọi refresh **1 lần duy nhất**, tránh spam API.
         - Nếu `refresh_token` cũng hết hạn (sau 7 ngày không truy cập): xóa cookie `auth_logged_in`, chuyển người dùng về `/auth/login`.
      2. Cập nhật [app.config.ts](file:///d:/git/isocalmarket/isocalmarket-web/src/app/app.config.ts) — Đăng ký interceptor vào `provideHttpClient(withInterceptors([authInterceptor]))`.
  - **[THÊM CHỨC NĂNG THÔNG TIN CÁ NHÂN, ĐỔI MẬT KHẨU & BẢO MẬT 2FA]**:
    - Yêu cầu: Cho phép người dùng chỉnh sửa số điện thoại (username/email hiển thị readonly và disable). Hỗ trợ đổi mật khẩu (3 trường: mật khẩu cũ, mật khẩu mới, xác nhận mật khẩu mới). Hỗ trợ bảo mật 2 lớp (2FA) bằng Google Authenticator.
    - Thực hiện triển khai & Kết quả:
      1. Database: Tạo và chạy migration bổ sung các cột `phone`, `google2fa_secret` và `google2fa_enabled` vào bảng `users`.
      2. Backend Domain & Infrastructure: Cập nhật `UserModel`, Domain `User` Entity, và `UserMapper` để đồng bộ các trường mới.
      3. Helper TOTP: Tạo class `Google2FA` xử lý sinh secret key, sinh OTP Auth URL và verify mã OTP 6 chữ số bằng thuật toán PHP thuần (không phụ thuộc package ngoài).
      4. Backend Controllers & Routes:
         - Viết `ProfileController` cung cấp các API cập nhật phone, đổi mật khẩu, kích hoạt/xác nhận/hủy 2FA.
         - Cập nhật `AuthController` khi login thành công có kiểm tra 2FA. Nếu bật 2FA, trả về `{ requires_2fa: true, temp_token }` để yêu cầu tiếp OTP đăng nhập qua API `/auth/login/2fa`.
      5. Frontend:
         - Cài đặt thư viện `ng-qrcode` để vẽ mã QR trực tiếp tại trình duyệt.
         - Cập nhật `AuthStore` thêm các API call cho profile/2FA và cập nhật hàm login hỗ trợ 2FA.
         - Xây dựng hệ thống Routing cho Profile: Đăng ký route `/profile` trong `app.routes.ts` với lazy child routes `/profile/information`, `/profile/password`, `/profile/security` trong `profile.routes.ts`.
         - Xây dựng component cha `ProfileComponent` thiết kế layout 2 cột (Desktop) và responsive tabs cuộn ngang (Mobile).
         - Xây dựng 3 component trang con: `ProfileInformationComponent` (SĐT), `ProfilePasswordComponent` (mật khẩu) và `ProfileSecurityComponent` (2FA).
         - Cập nhật `LoginComponent` hỗ trợ hiển thị màn hình nhập mã OTP khi tài khoản bật 2FA.
         - Cập nhật `HeaderComponent` (ts & html) để liên kết nút "Thông tin cá nhân" bằng `routerLink="/profile"` và tự đóng dropdown.
         - Xóa bỏ modal profile cũ không sử dụng.
         - **[TỐI ƯU LAYOUT PROFILE & 2FA MODALS]**: Nhận phản hồi về việc sidebar bị ép chữ và yêu cầu dùng modal xác thực 2FA. Đã thiết kế lại `ProfileComponent` dùng grid 12 cột (`md:col-span-4 lg:col-span-3` cho sidebar) kèm `whitespace-nowrap` chống gãy chữ. Tạo 2 modal động `Enable2faModalComponent` và `Disable2faModalComponent`, cập nhật `ProfileSecurityComponent` liên kết mở modal qua `ModalService.open`.
       6. Đã chạy biên dịch Angular frontend (`npm run build`) và unit test suite Laravel backend (`php artisan test`) thành công 100% không phát sinh lỗi.
  - **[HOÀN THÀNH] Đồng bộ Shared Components**:
    - Yêu cầu: Xem lại toàn bộ web, đảm bảo tất cả các trang đã kế thừa sử dụng các shared components có sẵn (`app-page-header`, `app-badge`, `app-icon`, `app-card`). Đồng bộ hóa giao diện lại toàn bộ.
    - Thực hiện triển khai & Kết quả:
      1. Đã cập nhật 8 features và 2 shared layouts (Header, Sidebar, Mobile Sidebar) để chuyển đổi 100% code thô, div thủ công, badge và inline SVG sang sử dụng:
         - `<app-page-header>` cho tiêu đề trang và thanh tìm kiếm.
         - `<app-card>` cho các khung chứa, card thống kê, bảng dữ liệu, và form Đăng nhập / Đăng ký.
         - Sử dụng `!p-0` cho các card chứa bảng hoặc khối tiêu đề trong để cho phép nội dung chạy tràn viền tự nhiên, tránh bị co nhỏ padding.
         - Override class nền kính mờ (`!bg-white/60 dark:!bg-slate-900/60 backdrop-blur-md`) trên Login/Register card để giữ hiệu ứng glassmorphism đẳng cấp.
         - `<app-badge>` cho trạng thái tồn kho, trạng thái đơn hàng, trạng thái 2FA và bộ đếm.
         - `<app-icon>` thay cho toàn bộ các inline SVG thô giúp mã nguồn cực kỳ gọn gàng.
      2. Cập nhật Sidebar và Mobile Sidebar sử dụng đúng icon theo chức năng (`dashboard`, `list-bullet`, `inventory`, `orders`).
      3. Dọn dẹp các warning về imports không sử dụng (`SkeletonLoaderComponent` trong `AdminDashboardComponent`, `ButtonComponent` trong `HeaderComponent`).
       4. Chạy `npm run build` biên dịch thành công 100% không phát sinh lỗi.
  - **[SỬA LỖI HIỂN THỊ HEADER & THAY ĐỔI XÁC THỰC TẮT 2FA]**:
    - Yêu cầu:
      1. Sửa lỗi mất giỏ hàng và nút đăng nhập trên Header khi truy cập trang đăng nhập, đăng ký.
      2. Khi tắt bảo mật 2FA, bắt buộc nhập mã xác thực OTP 6 số của Google Authenticator thay vì mật khẩu hiện tại.
    - Thực hiện triển khai & Kết quả:
      1. Header:
         - Đổi điều kiện hiển thị giỏ hàng trên Header từ `@if (isStorefrontPage())` thành `@if (!router.url.includes('/admin'))` giúp hiển thị giỏ hàng trên mọi trang khách hàng (kể cả login, register).
         - Cập nhật logic nút đăng nhập: nếu chưa đăng nhập, Header sẽ tự động đổi sang nút **Đăng ký** nếu đang ở trang `/auth/login`, và hiển thị nút **Đăng nhập** nếu đang ở `/auth/register` hoặc trang Storefront.
      2. Tắt bảo mật 2FA:
         - Backend: Sửa API `POST /v1/profile/2fa/disable` trong `ProfileController.php` để validate `code` (string) và kiểm tra bằng `Google2FA::verifyCode` thay vì kiểm tra mật khẩu tài khoản.
         - Frontend Store: Cập nhật hàm `disable2Fa` trong `auth.store.ts` để truyền payload `{ code }` và dịch thông báo lỗi API sang tiếng Việt thân thiện.
         - Frontend UI: Sửa giao diện modal `Disable2faModal` thành input text nhập mã OTP 2FA 6 số kèm validation client-side, thay đổi mô tả và nhãn cho phù hợp.
      3. Thiết kế & Căn chỉnh nút Auth trên Header:
         - Cập nhật kích thước nút Đăng nhập và Đăng ký trên Header thành `h-10 px-5` kèm flex layout để căn giữa dọc/ngang hoàn hảo.
         - Thêm hiệu ứng `hover:scale-[1.05] active:scale-[0.95]` và bóng mờ nhẹ để đồng bộ 100% với nút giỏ hàng.
      - Đã chạy kiểm tra build Angular frontend thành công 100% không lỗi.
  - **[SEED DỮ LIỆU DEMO LỚN]**:
    - Đã viết lại `DatabaseSeeder.php` để sinh ra dữ liệu phong phú: 10 tài khoản khách hàng, 10 sản phẩm (có attributes đầy đủ), 400 tài nguyên kho hàng (40 tài nguyên mỗi sản phẩm), và 50 đơn hàng mẫu với ngày tạo, trạng thái ngẫu nhiên (paid, pending, cancelled) phân bổ chuẩn nghiệp vụ.
    - Đã chạy lại `php artisan migrate:fresh --seed` thành công.
  - **[TỐI ƯU THIẾT KẾ VÀ PHÂN QUYỀN USER DROPDOWN MENU]**:
    - Nhận diện vấn đề: Menu dropdown của tài khoản Web2 chưa đồng bộ màu sắc thương hiệu (sử dụng màu xanh emerald thay vì Teal), trigger button thiếu trạng thái active trực quan khi dropdown mở ra, và thiếu các liên kết tắt quan trọng để người dùng điều hướng nhanh chóng (Trang quản trị đối với Admin, Lịch sử đơn hàng đối với Khách hàng).
    - Giải pháp khắc phục:
      1. Đổi tất cả các tông màu xanh emerald cũ trên trigger button và trong dropdown panel (khung số dư) sang màu **xanh lục thương hiệu (Teal)**.
      2. Thêm class động `[ngClass]` và `[class]` cho trigger button để thay đổi màu viền sang primary, đổi nền sang màu primary 5%, và đổi màu chữ/icon sang màu primary khi dropdown mở (Active State), tăng tính phản hồi tương tự như dropdown Web3 cũ.
      3. Bổ sung các liên kết điều hướng tương ứng với vai trò của người dùng: dòng **"Trang quản trị"** ở đầu menu dành cho Admin, dòng **"Lịch sử đơn hàng"** ở đầu menu dành cho Khách hàng thông thường. Thiết kế các liên kết này dạng phẳng (flat item) đồng nhất 100% về kích thước, padding, màu sắc, font chữ và hiệu ứng hover/active với dòng "Thông tin cá nhân".
      4. Loại bỏ hiệu ứng animation chuyển động không cần thiết trên dropdown panel nhằm tuân thủ chính sách "No-Animation" của hệ thống để phản hồi mở menu tức thì.
      5. Chạy build biên dịch Angular frontend (`npm run build`) thành công 100% không có lỗi.


  - **[TỐI ƯU HÓA BẢNG BIỂU & VIỆT HÓA TRẠNG THÁI]**:
    - Yêu cầu:
      1. Rút gọn các mã định danh UUIDv7 dài trên các bảng để tối ưu hiển thị.
      2. Tối ưu hiển thị dữ liệu tài nguyên cực dài trong quản lý kho hàng.
      3. Việt hóa toàn bộ các cột trạng thái trên mọi trang (sản phẩm, kho hàng, đơn hàng, dashboard).
    - Thực hiện triển khai & Kết quả:
      1. Rút gọn UUIDv7: Áp dụng format `#{{ id | slice:0:8 }}...{{ id | slice:-4 }}` cho ID sản phẩm, tài nguyên, đơn hàng trong các bảng. Thêm tooltip hiển thị ID đầy đủ khi hover, và tích hợp icon Copy nhanh hiển thị toast tiếng Việt khi sao chép thành công.
      2. Tối ưu dữ liệu tài nguyên kho: Rút gọn hiển thị trên dòng còn 15 ký tự kèm `...`. Tích hợp nút copy nhanh trên dòng. Khi click vào dữ liệu tài nguyên hoặc bấm icon Xem chi tiết, hệ thống mở modal confirm hiển thị đầy đủ chuỗi text trong khung `.font-mono` dễ đọc kèm nút "Sao chép dữ liệu" và nút "Đóng".
      3. Việt hóa trạng thái:
         - Trang Sản phẩm: `Active` -> `Hoạt động`, `Inactive` -> `Ngưng hoạt động`.
         - Trang Kho hàng: `available` -> `Sẵn có`, `reserved` -> `Tạm giữ`, `sold` -> `Đã bán`. Việt hóa cả nhãn lọc trạng thái.
         - Dashboard: Đổi nhãn `Items` thành `Tài nguyên` trong thẻ thống kê tồn kho khả dụng.
      4. Trang Chi tiết đơn hàng: Thêm nút copy nhanh cho mã đơn hàng.
      5. Đã chạy biên dịch frontend thành công 100% không phát sinh lỗi.
  - **[ĐỒNG BỘ GIỎ HÀNG, SỬA RESPONSIVE HEADER & TÍCH HỢP TÌM KIẾM + LÀM MỚI]**:
    - Yêu cầu:
      1. Cho hiện giỏ hàng trên tất cả các trang, không phân biệt admin hay khách hàng.
      2. Sửa lỗi responsive tiêu đề trang bị bép nghẹt/xuống dòng thô.
      3. Tích hợp ô tìm kiếm và nút làm mới (Tải lại) cho toàn bộ các trang danh sách dữ liệu (Kho hàng, Sản phẩm, Đơn hàng Admin, Lịch sử đơn hàng Khách).
    - Thực hiện triển khai & Kết quả:
      1. Đồng bộ giỏ hàng: Gỡ điều kiện ẩn nút giỏ hàng trên trang `/admin` ở `header.component.html` để giỏ hàng hiển thị đồng nhất.
      2. Sửa lỗi responsive:
         - Bọc tiêu đề page-header bằng class `flex-1 min-w-0` giúp flexbox dàn đều không gian.
         - Nâng breakpoint chuyển đổi layout của `PageHeaderComponent` từ `md` sang `xl` (1280px) để ở màn hình laptop trung bình trở xuống (như 1024px) khi mở sidebar, tiêu đề không bị đè ép và ngắt chữ mà tự động xếp dọc ngay ngắn.
         - Cấu hình lại Actions container: chuyển sang căn lề trái `justify-start` trên màn hình nhỏ và `xl:justify-end` trên màn hình lớn.
         - Tối ưu kích thước của search input thành `w-full md:w-64` để co giãn mượt mà ở mobile/tablet dọc.
      3. Tích hợp tìm kiếm & làm mới:
         - Backend: Bổ sung bộ lọc tìm kiếm `search` vào các repository `InventoryItemRepository` (lọc `id`, `data`) và `OrderRepository` (lọc `order_code`, email/name user) và cập nhật Controllers tương ứng.
         - Frontend: Cấu hình `InventoryStore` và `OrderStore` hỗ trợ query param `search`. Thêm ô `<app-custom-search-input>` và nút Tải lại (Làm mới) vào các trang Sản phẩm, Kho hàng, Đơn hàng Admin và Lịch sử đơn hàng Khách hàng.
       4. Đã chạy build frontend Angular thành công 100% không phát sinh lỗi.
  - **[PHÂN LOẠI DANH MỤC (CATEGORIES) & CHIA SECTION STOREFRONT]**:
    - Đã tạo bảng `categories` (UUIDv7) và liên kết khóa ngoại `category_id` vào `products` trong Database.
    - Triển khai đầy đủ mô hình DDD backend cho Category: Domain Entity, Eloquent Model, Mapper, Repository, controller, public API endpoint `GET /v1/categories`.
    - Cập nhật API Products hỗ trợ lọc theo query parameter `category_id`.
    - Cập nhật `DatabaseSeeder.php` tạo 12 danh mục chính xác từ web mẫu và phân bổ 51 sản phẩm MMO thực tế. Đã chạy lại `php artisan migrate:fresh --seed` thành công.
    - Cập nhật frontend: `ProductStore` lấy categories và truyền `category_id` khi tải sản phẩm.
    - Sửa storefront limit phân trang thành **12 sản phẩm/trang** theo yêu cầu.
    - Thiết kế giao diện storefront hiển thị sản phẩm chia theo từng danh mục (Section) riêng biệt nằm dọc từ trên xuống dưới. Mỗi danh mục tự quản lý trang hiện tại của nó độc lập.
    - Tích hợp dropdown chọn danh mục cho sản phẩm trong `ProductFormModal` của admin.
    - Tuân thủ quy tắc "Clean SVG Overrides" ở `.agent/`: Loại bỏ hoàn toàn các emoji thô (như `📂`) khỏi giao diện storefront và thay thế bằng icon hệ thống sạch sẽ (`list-bullet`).
    - Đã chạy build frontend Angular thành công 100% không phát sinh lỗi.
  - **[SẮP XẾP DANH MỤC & CHẾ ĐỘ XEM CARD/LIST Ở STOREFRONT]**:
    - **Sắp xếp danh mục:** Tích hợp bộ chọn sắp xếp danh mục (Dropdown CustomSelect ở Toolbar phía bên phải) cho phép lựa chọn thứ tự sắp xếp danh mục: "Tên danh mục (A - Z)" (mặc định), "Tên danh mục (Z - A)" và "Thứ tự mặc định". Sử dụng hàm `localeCompare` tiếng Việt trong computed signal để sắp xếp mượt mà.
    - **Chế độ xem Card/List:** Tích hợp bộ chuyển đổi chế độ xem (View Mode Toggle ở Toolbar phía bên trái) giữa **Dạng Card** (mặc định, hiển thị dạng Grid 4 cột) và **Dạng List** (hiển thị dạng Table chuyên nghiệp tối ưu không gian).
    - Dạng List Table hiển thị đầy đủ thông tin: Tên sản phẩm, các tag thuộc tính động, đơn giá, tồn kho (với badge tương ứng), và nút "Mua Ngay" gọn gàng.
    - Đã chạy build frontend Angular thành công 100% không phát sinh lỗi.
  - **[SỬA LỖI CUSTOM-SELECT UI BỊ ẨN TRÊN TOOLBAR]**:
    - **Nguyên nhân:** Do Toolbar cha chứa select dropdown có class `backdrop-blur-md` (kích hoạt `backdrop-filter` trong CSS). Theo tiêu chuẩn CSS, việc áp dụng filter/backdrop-filter tạo ra một containing block mới cho các phần tử con, khiến phần tử `position: fixed` của select dropdown bị định vị sai lệch hoàn toàn khỏi viewport và biến mất.
    - **Khắc phục:** Loại bỏ lớp `backdrop-blur-md` khỏi Toolbar trong [storefront.component.html](file:///d:/git/isocalmarket/isocalmarket-web/src/app/features/storefront/storefront.component.html), chuyển sang nền đục phẳng chuẩn thương hiệu (`bg-white` và `dark:bg-slate-900` với viền xám nhẹ `/80`). Giúp dropdown menu khôi phục viewport containing block và định vị chuẩn xác dưới trigger button khi mở.
    - Đã chạy build frontend Angular thành công 100% không phát sinh lỗi.

  - **[SỬA LỖI ĐĂNG NHẬP HIỂN THỊ "UNAUTHORIZED." & CẢI THIỆN THÔNG BÁO LỖI]**:
    - **Nhận diện lỗi:** Khi người dùng nhập sai mật khẩu, form đăng nhập hiển thị thông báo thô `"Unauthorized."` thay vì thông báo lỗi rõ ràng bằng tiếng Việt.
    - **Nguyên nhân gốc rễ (Root Cause):** `AuthInterceptor` bắt mọi lỗi 401 từ tất cả API endpoints, kể cả endpoint `/auth/login`. Khi đăng nhập sai mật khẩu, backend trả về 401 đúng (sai thông tin) — Interceptor nhầm đây là token hết hạn, gọi thêm `/auth/refresh`. Refresh thất bại trả về 401 khác với message `"Unauthorized."` — message này ghi đè lên lỗi gốc và được hiển thị ra UI.
    - **Giải pháp:**
      1. **Sửa `auth.interceptor.ts`:** Thêm điều kiện `isAuthEndpoint` để bỏ qua việc xử lý 401 cho các endpoint `/auth/login` và `/auth/register`. Các endpoint này trả về 401 khi sai thông tin (expected), không phải do token hết hạn.
      2. **Thêm `translateAuthError()` vào `auth.store.ts`:** Hàm private map tất cả message tiếng Anh từ API sang tiếng Việt rõ ràng. Hỗ trợ cả map theo message cụ thể và theo HTTP status code (401 → "Sai tài khoản/mật khẩu", 422 → parse validation errors, 429 → rate limit, 500/0 → lỗi máy chủ/mạng).
      3. **Thêm signals `loginError` & `registerError`:** Lưu thông báo lỗi inline để hiển thị trực tiếp trong form (không chỉ Toast). Reset về chuỗi rỗng khi bắt đầu gọi API mới.
      4. **Cập nhật `login.component.html` & `register.component.html`:** Thêm error banner màu đỏ với icon cảnh báo SVG, hiển thị ngay dưới tiêu đề form khi có lỗi. Thêm `(ngModelChange)` vào các input để tự động xóa lỗi khi người dùng gõ lại.
    - Đã chạy build frontend Angular thành công 100% không phát sinh lỗi.

  - **[TÍNH NĂNG] MODAL THÔNG BÁO HỆ THỐNG SAU ĐĂNG NHẬP**:
    - **Yêu cầu:** Sau khi đăng nhập thành công, hiển thị modal thông báo hệ thống (tuyên bố miễn trừ trách nhiệm). Modal có checkbox "Không hỏi lại trong 7 ngày" (mặc định tích). Đóng được bằng backdrop click, nút X trên header, hoặc nút "Đã hiểu & Đóng".
    - **Giải pháp thiết kế:**
      1. Tạo `WelcomeNoticeModalComponent` tại `features/auth/welcome-notice-modal/`. Component standalone, inject `ModalRef` để tự đóng.
      2. Export hàm helper `shouldShowWelcomeNotice()` kiểm tra `localStorage` key `welcome_notice_suppressed_until` (so sánh timestamp hiện tại với timestamp hết hạn).
      3. Khi đóng modal, `savePreference()` lưu timestamp `Date.now() + 7 ngày` nếu checkbox tích, hoặc xóa key nếu không tích.
      4. Inject `ModalService` vào `AuthStore`, thêm method private `openWelcomeNoticeIfNeeded()`: gọi sau `login()` và `login2Fa()` thành công, dùng `setTimeout(400ms)` để đợi router navigation xong trước khi mở modal.
      5. Tái sử dụng hoàn toàn `ModalService.open()` + `ModalWrapperComponent` (dynamic modal pattern đã có sẵn). Title modal: `"🔔 Thông Báo Hệ Thống"`, size `sm`, `closeOnBackdropClick: true`.
    - Đã chạy build Angular thành công 100% không phát sinh lỗi.
    - **Cập nhật dọn dẹp (Refactor):** Thay thế toàn bộ các emoji (như ❌) và các thẻ `<svg>` inline viết trực tiếp trong các template HTML (`welcome-notice-modal.component.html`, `login.component.html`, `register.component.html`, `accordion-item.component.html`, `product-form-modal.component.html`, `custom-checkbox.component.html`) bằng việc gọi qua component dùng chung `<app-icon>`. Import `IconComponent` vào các component tương ứng. Loại bỏ emoji khỏi chuỗi title modal trong `auth.store.ts`. Biên dịch hệ thống thành công không lỗi.

  - **[HOÀN THÀNH] DỌN DẸP COMMENT RÁC, DIVIDERS (====) & CODE COMMENT-OUT**:
    - **Yêu cầu:** Loại bỏ toàn bộ các comment phân cách rườm rà dạng `=======`, mã nguồn bị comment-out (không sử dụng), và các comment giải thích hiển nhiên không cần thiết (clean code/self-documenting) ở cả Frontend và Backend.
    - **Thực hiện:**
      1. **Frontend:**
         - Rút gọn các divider lớn `/* ==================== HỆ THỐNG ... ==================== */` trong [styles.scss](file:///d:/git/isocalmarket/isocalmarket-web/src/styles.scss) và [state.service.ts](file:///d:/git/isocalmarket/isocalmarket-web/src/app/core/services/state.service.ts), [icon.component.html](file:///d:/git/isocalmarket/isocalmarket-web/src/app/shared/components/icon/icon.component.html) thành các đề mục tiếng Anh ngắn gọn, tinh tế.
         - Xóa bỏ các comment hiển nhiên bên cạnh icon, các chú thích logic UI hiển nhiên trong các file HTML component con (Shared Components & Features HTML) và các file logic `.ts` (theme.service, state.service, tooltip.directive).
      2. **Backend:**
         - Loại bỏ các comment-out imports (`// use ...`) trong [User.php](file:///d:/git/isocalmarket/isocalmarket-api/app/Models/User.php) và [ExampleTest.php](file:///d:/git/isocalmarket/isocalmarket-api/tests/Feature/ExampleTest.php).
         - Loại bỏ các comment giải thích code hiển nhiên trong [OrderService.php](file:///d:/git/isocalmarket/isocalmarket-api/app/Application/Services/OrderService.php) (giữ lại Pessimistic Lock, giải mã nhạy cảm), [AuthController.php](file:///d:/git/isocalmarket/isocalmarket-api/app/Http/Controllers/AuthController.php), [DashboardController.php](file:///d:/git/isocalmarket/isocalmarket-api/app/Http/Controllers/DashboardController.php), [OrderRepository.php](file:///d:/git/isocalmarket/isocalmarket-api/app/Infrastructure/Persistence/Repositories/OrderRepository.php).
         - Giữ lại toàn bộ comment thuật toán phức tạp của Google TOTP 2FA trong [Google2FA.php](file:///d:/git/isocalmarket/isocalmarket-api/app/Infrastructure/Security/Google2FA.php) để làm tài liệu kỹ thuật.
      3. **Kiểm thử:**
         - Chạy `npm run build` biên dịch Frontend Angular thành công 100% không phát sinh lỗi.
         - Chạy `php artisan test` kiểm thử Backend Laravel thành công 100% (passed 2/2 tests).

  - **[HOÀN THÀNH] DỌN DẸP TUYỆT ĐỐI COMMENT JSDOC, TS & HTML TRÊN ISOCALMARKET-WEB**:
    - **Yêu cầu:** Tiếp tục áp dụng mức độ dọn dẹp triệt để nhất giống như dự án ví Web3: xóa sạch các khối JSDoc (`/** ... */`) giải thích class/component/inputs/outputs, xóa sạch 100% comment HTML (`<!-- ... -->`) trong mọi tệp giao diện, và dọn dẹp các comment SCSS/CSS thừa.
    - **Thực hiện:**
      1. **TypeScript (.ts):** Loại bỏ hoàn toàn comment JSDoc và comment dòng đơn hiển nhiên trong tất cả các file của `isocalmarket-web/src/app` (bao gồm các custom components, stores, services, layouts, pipes, v.v.).
      2. **HTML Templates (.html):** Xóa sạch 100% các thẻ comment HTML và dọn dẹp các dòng trống thừa thãi phát sinh.
      3. **SCSS/CSS:** Loại bỏ toàn bộ các comment block trong `styles.scss` và các styles cục bộ.
      4. **Kiểm thử:** Chạy `npm run build` biên dịch thành công 100% không lỗi biên dịch.

  - **[SỬA LỖI] Lỗi ParameterBag khi thêm/sửa sản phẩm**:
    - **Nhận diện lỗi:** Khi thêm hoặc sửa sản phẩm, hệ thống trả về lỗi 500 do `ProductService::createProduct()` nhận đối tượng `Symfony\Component\HttpFoundation\ParameterBag` thay vì một mảng `?array` cho tham số `$attributes`.
    - **Nguyên nhân:** Do Laravel Request kế thừa từ Symfony Request có thuộc tính public là `$attributes` (là một `ParameterBag` chứa các route parameters, custom attributes,...). Vì vậy, khi gọi `$request->attributes` để lấy input field tên là `'attributes'`, PHP sẽ truy cập trực tiếp vào thuộc tính public của base class Symfony thay vì gọi magic getter `__get` của Laravel Request.
    - **Khắc phục:** Đã sửa đổi `$request->attributes` thành `$request->input('attributes')` tại cả hai phương thức `store` và `update` trong [ProductController.php](file:///d:/git/isocalmarket/isocalmarket-api/app/Http/Controllers/ProductController.php).
    - **Kiểm thử:** Đã chạy `php artisan test` và kiểm tra toàn bộ suite thành công 100%.

  - **[TỐI ƯU] Đồng bộ sắp xếp dữ liệu mới nhất lên hàng đầu**:
    - **Yêu cầu:** Tất cả danh sách dữ liệu (sản phẩm, kho hàng, đơn hàng) phải được sắp xếp theo thứ tự mới nhất đến cũ nhất (mới tạo hiển thị lên đầu).
    - **Thực hiện:**
      - Đã kiểm tra và đảm bảo các repository `OrderRepository` và `InventoryItemRepository` đã sắp xếp chuẩn `created_at` DESC.
      - Phát hiện hàm `findAllActive` trong `ProductRepository` chưa sắp xếp. Đã bổ sung `orderBy('created_at', 'desc')` vào hàm này trong [ProductRepository.php](file:///d:/git/isocalmarket/isocalmarket-api/app/Infrastructure/Persistence/Repositories/ProductRepository.php) để các sản phẩm không phân trang (như trang quản trị sản phẩm tải đầy đủ) luôn hiển thị sản phẩm mới tạo lên trên cùng.
      - Chạy test backend thành công 100%.

  - **[SỬA LỖI] Lỗi chớp dữ liệu cũ trước khi hiện skeleton ở Quản lý kho hàng**:
    - **Nhận diện lỗi:** Khi truy cập Quản lý kho hàng `/admin/inventory`, danh sách dữ liệu cũ chớp lên đầy đủ rồi mới hiển thị skeleton loader và tải lại dữ liệu mới, trong khi các trang khác hiện skeleton ngay từ đầu.
    - **Nguyên nhân:** Hàm `ngOnInit()` trong `InventoryComponent` được gọi tuần tự (waterfall), đợi API tải danh sách sản phẩm `fetchProducts()` xong rồi mới gọi API tải danh sách kho `fetchFilteredItems(1)`. Trong lúc chờ API sản phẩm phản hồi, biến `inventoryStore.loading()` vẫn là `false` nên giao diện render dữ liệu cũ lưu trong store cache.
    - **Khắc phục:** Thay đổi cách gọi API trong `ngOnInit()` thành gọi song song và độc lập. Gọi `fetchFilteredItems(1)` ngay lập tức giúp `inventoryStore.loading()` chuyển sang `true` trước khi render, giúp hiển thị skeleton loader ngay từ đầu.
    - **Kiểm thử:** Chạy `npm run build` biên dịch thành công 100% không phát sinh bất kỳ lỗi nào.

  - **[HOÀN THÀNH] CHUYỂN ĐỔI HỆ THỐNG KHO SANG DẠNG LÔ HÀNG (INVENTORY BATCHES) - PHƯƠNG ÁN B**:
    - **Yêu cầu:** Thay đổi thiết kế từ lưu trữ từng tài nguyên đơn lẻ (mỗi dòng text là 1 row DB) sang dạng quản lý theo Lô Hàng (Inventory Batches), lưu toàn bộ key chưa bán trong pool văn bản lớn (`keys_pool`) của 1 lô hàng để tối ưu hiệu năng và khả năng mở rộng.
    - **Thực hiện:**
      - **Database (Migrations):**
        1. Xóa bảng `inventory_items` và tạo bảng `inventory_batches` lưu trữ `batch_name`, `keys_pool`, `initial_count`, `sold_count`, `status`.
        2. Thêm cột `delivered_keys` vào bảng `order_items` để lưu trữ các tài nguyên đã giao riêng cho đơn hàng.
      - **Backend (Laravel):**
        1. Xóa Entity/Model/Mapper/Repository của `InventoryItem` và xây dựng thay thế bằng `InventoryBatch`, `InventoryBatchModel`, `InventoryBatchMapper`, `InventoryBatchRepository`.
        2. Cập nhật `ProductRepository` để tính tổng tồn kho khả dụng từ các lô hàng active (bằng cách đếm số dòng trong `keys_pool`).
        3. Cập nhật `OrderService::createOrder()` để tự động bóc tách (pop) key từ lô hàng active cũ nhất sang lô hàng mới hơn theo phương thức FIFO khi thanh toán, và lưu vào `delivered_keys` của order item.
        4. Cập nhật `OrderController::show` chuyển đổi `delivered_keys` thành mảng `accounts` để giữ nguyên tương thích ngược đầu ra cho frontend.
        5. Cập nhật `DashboardController` và `DatabaseSeeder` hoạt động hoàn hảo với cấu trúc Lô Hàng.
        6. Chạy `php artisan migrate:fresh --seed` và chạy `php artisan test` vượt qua toàn bộ các bài test.
      - **Frontend (Angular):**
        1. Cập nhật `inventory.store.ts` để gọi API và truyền tải các tham số Lô hàng (`batchName`, `keysPool`).
        2. Cập nhật form Import (`import-inventory-modal`) thêm ô nhập **Tên Lô Hàng** (hỗ trợ tự sinh tên theo ngày giờ).
        3. Cập nhật form Sửa (`edit-inventory-modal`) cho phép admin sửa tên lô, trạng thái (Hoạt động / Hoàn thành / Tạm khóa), và chỉnh sửa trực tiếp danh sách key còn lại trong `keys_pool`.
        4. Cập nhật bảng hiển thị ở `inventory.component.html` hiển thị: ID Lô, Tên sản phẩm, Tên lô hàng, Số lượng (Khả dụng / Ban đầu), Trạng thái lô hàng, và các nút Sửa/Xóa.
      - **Kiểm thử:** Chạy build frontend Angular (`npm run build`) thành công 100% không phát sinh lỗi hay cảnh báo.

  - **[HOÀN THÀNH] NÂNG FONT-SIZE TỐI THIỂU 12PX (TEXT-XS) TOÀN HỆ THỐNG**:
    - **Yêu cầu:** Rà soát toàn bộ source code frontend, đảm bảo font-size nhỏ nhất là 12px, không có trường hợp nào dưới 12px.
    - **Thực hiện:**
      1. Rà soát toàn bộ project `isocalmarket-web` bằng grep regex `text-\[([1-9]|10|11)(\.[0-9]+)?px\]` để tìm các class Tailwind chỉ định font-size nhỏ hơn 12px.
      2. Kiểm tra và xác nhận không có file CSS/SCSS nào tự định nghĩa thuộc tính `font-size: Xpx/rem/em` thủ công.
      3. Đã chỉnh sửa tổng cộng 13 file (bao gồm các components, layouts và directive) để chuyển đổi toàn bộ các class `text-[9px]`, `text-[10px]`, `text-[11px]` và `text-[10px] md:text-xs` sang class `text-xs` (tương đương 12px):
         - `sidebar.component.html` (chuyển `text-[9px]`, `text-[10px]`)
         - `header.component.html` (chuyển `text-[10px]`, `text-[11px]`)
         - `app.html` (chuyển `text-[9px]`, `text-[10px]`)
         - `tx-speed-selector.component.html` (chuyển `text-[10px]`)
         - `tooltip.directive.ts` (chuyển `text-[11px]`)
         - `theme-switcher.component.html` (chuyển `text-[11px]`)
         - `tab-group.component.html` (chuyển `text-[10px]`)
         - `kbd.component.ts` (chuyển `text-[10px]`)
         - `custom-select.component.html` (chuyển `text-[10px]`)
         - `custom-radio.component.html` (chuyển `text-[10px]`)
         - `custom-date-time-range.component.html` (chuyển `text-[10px]`, `text-[11px]`)
         - `custom-checkbox.component.html` (chuyển `text-[10px]`)
         - `custom-date-picker.component.html` (chuyển `text-[11px]`)
         - `badge.component.ts` (chuyển `text-[10px]`)
         - `storefront.component.html` (chuyển `text-[9px]`, `text-[10px]`, `text-[11px]`)
         - `enable-2fa-modal.component.html` (chuyển `text-[10px]`)
         - `products.component.html` (chuyển `text-[10px]`)
         - `product-form-modal.component.html` (chuyển `text-[11px]`)
         - `order-detail.component.html` (chuyển `text-[10px]`)
         - `import-inventory-modal.component.html` (chuyển `text-[11px]`)
         - `edit-inventory-modal.component.html` (chuyển `text-[11px]`)
         - `admin-dashboard.component.html` (chuyển `text-[10px] md:text-xs`)
      4. Chạy biên dịch dự án kiểm tra thành công.
  - **[HOÀN THÀNH] Bỏ thông tin tài khoản ở sidebar và chuyển đổi Đăng xuất sang Modal xác nhận**:
    - Đã cập nhật [sidebar.component.html](file:///d:/git/isocalmarket/isocalmarket-web/src/app/shared/layout/sidebar/sidebar.component.html) và [app.html](file:///d:/git/isocalmarket/isocalmarket-web/src/app/app.html): Loại bỏ hoàn toàn khối chứa thông tin tài khoản (tên user, vai trò, nút Thoát) và nút Đăng nhập ở dưới cùng Sidebar của cả hai giao diện desktop và mobile sidebar, chỉ giữ lại thanh chuyển đổi giao diện `<app-theme-switcher />`.
    - Đã cập nhật [header.component.ts](file:///d:/git/isocalmarket/isocalmarket-web/src/app/shared/layout/header/header.component.ts) và [header.component.html](file:///d:/git/isocalmarket/isocalmarket-web/src/app/shared/layout/header/header.component.html): Chuyển đổi sự kiện click của nút Đăng xuất trong dropdown tài khoản ở Header sang gọi phương thức `logout()` trong TS controller. Phương thức này sẽ kích hoạt `ModalService.confirm()` hiển thị modal xác nhận động, và chỉ gọi API logout thực sự khi người dùng chọn Xác nhận.
  - **[HOÀN THÀNH] Tối ưu hóa màu nền cho các trường nhập liệu Input, Select, Textarea**:
    - Đã cập nhật [styles.scss](file:///d:/git/isocalmarket/isocalmarket-web/src/styles.scss): Điều chỉnh màu nền của các class `.form-input`, `.form-textarea`, `.search-input` từ xám nhạt (`bg-slate-50`) sang màu trắng tinh (`bg-white`) ở chế độ Light Mode và tối đậm (`bg-slate-950`) ở chế độ Dark Mode.
    - Đã đồng bộ hóa [edit-inventory-modal.component.html](file:///d:/git/isocalmarket/isocalmarket-web/src/app/features/inventory/edit-inventory-modal/edit-inventory-modal.component.html), [import-inventory-modal.component.html](file:///d:/git/isocalmarket/isocalmarket-web/src/app/features/inventory/import-inventory-modal/import-inventory-modal.component.html) và [product-form-modal.component.html](file:///d:/git/isocalmarket/isocalmarket-web/src/app/features/products/product-form-modal/product-form-modal.component.html) để loại bỏ các class CSS inline cứng của các thẻ `input` và `textarea`, chuyển sang sử dụng các class toàn cục `.form-input` và `.form-textarea`.
  - **[HOÀN THÀNH] Bổ sung màu nền cho nút Tải lại (Reload) trên toàn hệ thống**:
    - Đã cập nhật 4 tệp giao diện: [products.component.html](file:///d:/git/isocalmarket/isocalmarket-web/src/app/features/products/products.component.html), [inventory.component.html](file:///d:/git/isocalmarket/isocalmarket-web/src/app/features/inventory/inventory.component.html), [orders.component.html](file:///d:/git/isocalmarket/isocalmarket-web/src/app/features/orders/orders.component.html) và [admin-orders.component.html](file:///d:/git/isocalmarket/isocalmarket-web/src/app/features/admin-orders/admin-orders.component.html). Bổ sung class `bg-white dark:bg-slate-950` để nút Tải lại có nền trắng ở Light Mode và tối ở Dark Mode, giúp đồng bộ hóa thẩm mỹ UI.
  - **[HOÀN THÀNH] Sửa lỗi logic tính toán số lượng lô hàng (Khả dụng / Ban đầu) khi sửa**:
    - Đã cập nhật [InventoryService.php](file:///d:/git/isocalmarket/isocalmarket-api/app/Application/Services/InventoryService.php): Sửa đổi logic hàm `updateBatch` để giữ nguyên số lượng đã bán thực tế `sold_count` cũ, đồng thời tính lại `initial_count` mới theo công thức = `available_count` hiện tại + `sold_count` đã bán.
    - Đã chạy lệnh Tinker sửa đổi và đồng bộ hóa cột `initial_count` cho toàn bộ các lô hàng mẫu bị lỗi lệch số lượng khả dụng lớn hơn số lượng ban đầu (`19 / 3` đổi thành `19 / 19`).
  - **[HOÀN THÀNH] Tích hợp bộ đếm số lượng tài nguyên dưới Textarea**:
    - Đã cập nhật [edit-inventory-modal.component.ts](file:///d:/git/isocalmarket/isocalmarket-web/src/app/features/inventory/edit-inventory-modal/edit-inventory-modal.component.ts) và [edit-inventory-modal.component.html](file:///d:/git/isocalmarket/isocalmarket-web/src/app/features/inventory/edit-inventory-modal/edit-inventory-modal.component.html): Viết getter `keysCount` và hiển thị nhãn `Tổng cộng: X tài khoản` ngay dưới textarea trong modal Chỉnh sửa tài nguyên.
    - Đã cập nhật [import-inventory-modal.component.ts](file:///d:/git/isocalmarket/isocalmarket-web/src/app/features/inventory/import-inventory-modal/import-inventory-modal.component.ts) và [import-inventory-modal.component.html](file:///d:/git/isocalmarket/isocalmarket-web/src/app/features/inventory/import-inventory-modal/import-inventory-modal.component.html): Viết getter `rawDataCount` và hiển thị nhãn `Tổng cộng: Y tài khoản` dưới textarea trong modal Import Kho Hàng.
  - **[HOÀN THÀNH] Việt hóa 100% toast messages từ API backend**:
    - Đã cập nhật [toast.service.ts](file:///d:/git/isocalmarket/isocalmarket-web/src/app/core/services/toast.service.ts): Tích hợp bộ dịch tự động `translateMessage` trong `ToastService`. Mỗi khi hàm `showToast` được gọi, thông điệp (success/error) truyền qua sẽ được so khớp từ điển tiếng Anh và dịch hoàn toàn sang tiếng Việt thân thiện trước khi render. Hỗ trợ dịch động cả các chuỗi chứa tham số (ví dụ số lượng import).
  - **[HOÀN THÀNH] Tích hợp Khóa lạc quan (OCC) và Tự động đồng bộ dữ liệu khi sửa kho**:
    - Backend: Thêm route show item, phương thức `show($id)` trong [InventoryController.php](file:///d:/git/isocalmarket/isocalmarket-api/app/Http/Controllers/InventoryController.php), phương thức `getItemById` và so sánh `last_updated_at` chống Lost Update trong [InventoryService.php](file:///d:/git/isocalmarket/isocalmarket-api/app/Application/Services/InventoryService.php).
    - Frontend: Viết hàm `fetchItemDetail` trong [inventory.store.ts](file:///d:/git/isocalmarket/isocalmarket-web/src/app/features/inventory/inventory.store.ts), cập nhật [inventory.component.ts](file:///d:/git/isocalmarket/isocalmarket-web/src/app/features/inventory/inventory.component.ts) để gọi API lấy dữ liệu mới nhất trước khi mở modal, và cập nhật [edit-inventory-modal.component.ts](file:///d:/git/isocalmarket/isocalmarket-web/src/app/features/inventory/edit-inventory-modal/edit-inventory-modal.component.ts) để truyền kèm `last_updated_at` when lưu.
  - **[HOÀN THÀNH] Tô màu trực quan cột Số lượng (Khả dụng / Ban đầu) trong Kho**:
    - Đã cập nhật [inventory.component.html](file:///d:/git/isocalmarket/isocalmarket-web/src/app/features/inventory/inventory.component.html): Tô màu số lượng khả dụng phía trước dựa trên trạng thái kho: màu đỏ (`text-rose-600`) khi đã bán hết (số lượng bằng 0), màu cam (`text-amber-600`) khi đang bán dở, và màu xanh lá (`text-emerald-600`) khi còn mới nguyên vẹn. Làm mờ dấu gạch chéo `/` và số ban đầu phía sau để làm nổi bật số khả dụng ở phía trước.
  - **[HOÀN THÀNH] Tối ưu hóa rút gọn bảng dữ liệu và tích hợp Tooltip khi hover**:
    - Đã import `TooltipDirective` và tích hợp `[appTooltip]` cho tất cả 5 trang feature: Lịch sử đơn hàng, Tổng quan Dashboard, Quản lý sản phẩm, Quản lý kho hàng, và Quản lý đơn hàng.
    - Đã rút gọn hiển thị tên sản phẩm, tên lô hàng, mô tả, email khách hàng bằng class `truncate` kết hợp `max-w-[...]` thích hợp để giữ bảng cân đối, hiển thị dấu `...` khi quá dài.
    - Hover chuột vào các trường này và các ô dữ liệu khác sẽ hiển thị tooltip đầy đủ thông tin gốc.
  - **[HOÀN THÀNH] Triển khai bộ lọc (Filter Bar) đồng bộ trên 4 trang giao diện**:
    - Backend: Cập nhật `OrderRepository`, `OrderService`, `OrderController` và `DashboardController` để tiếp nhận và truy vấn dữ liệu chính xác theo khoảng thời gian (`start_date`, `end_date`).
    - Quản lý sản phẩm: Tích hợp dropdown bộ chọn Danh mục và Trạng thái, thực hiện lọc client-side mượt mà qua signal computed `filteredProducts()`.
    - Lịch sử đơn hàng & Quản lý đơn hàng: Triển khai thanh bộ lọc Grid đồng bộ chứa dropdown Trạng thái và bộ chọn thời gian `<app-custom-date-time-range>`, kết nối và gọi API backend chính xác.
    - Dashboard: Thêm bộ lọc thời gian để thống kê Doanh thu và Số lượng đơn hàng động theo khoảng thời gian chọn.
    - Biên dịch: Chạy lệnh build frontend hoàn thành không lỗi.
    - Sửa lỗi bộ lọc: Khắc phục hiện tượng race condition khi lọc khoảng thời gian bằng cách chuyển sự kiện lắng nghe từ `(valueChange)` sang `(ngModelChange)` cho `CustomDateTimeRangeComponent` ở cả 3 trang (Orders, Admin Orders, Dashboard) giúp model cập nhật đầy đủ trước khi fetch.
    - Sửa lỗi bộ lọc sản phẩm: Chuyển đổi các biến lọc Category/Status của sản phẩm sang Signals để computed `filteredProducts()` phản ứng và tính toán lại chính xác client-side ngay khi chọn dropdown.
    - Chạy build frontend và unit tests backend thành công 100%.
  - **[HOÀN THÀNH] Tối ưu hiển thị mô tả sản phẩm ở trang Quản lý sản phẩm**:
    - Yêu cầu: Giới hạn mô tả sản phẩm hiển thị tối đa 2 dòng, nếu dài hơn thì hiển thị 3 chấm (...).
    - Giải pháp: Thay thế class `truncate max-w-xs block` bằng `line-clamp-2 max-w-xs` cho thẻ mô tả sản phẩm `<p>` dưới cột Tên sản phẩm trong file [products.component.html](file:///d:/git/isocalmarket/isocalmarket-web/src/app/features/products/products.component.html). Điều này cho phép hiển thị tối đa 2 dòng mô tả và tự động thêm 3 chấm nếu văn bản vượt quá 2 dòng.
  - **[HOÀN THÀNH] Triển khai Chức năng Quản lý Danh mục (CRUD Category)**:
    - Yêu cầu: Bổ sung chức năng quản lý danh mục (thêm, sửa, xóa) ở cả Backend và Frontend.
    - Phân tích & Kế hoạch: Đã xây dựng `implementation_plan.md` chi tiết và nhận được sự phê duyệt.
    - Thực hiện triển khai & Kết quả:
      1. Backend: Tạo [CreateCategoryRequest.php](file:///d:/git/isocalmarket/isocalmarket-api/app/Http/Requests/CreateCategoryRequest.php) validate dữ liệu; tạo [CategoryService.php](file:///d:/git/isocalmarket/isocalmarket-api/app/Application/Services/CategoryService.php) xử lý CRUD và chặn xóa danh mục chứa sản phẩm; cập nhật [CategoryController.php](file:///d:/git/isocalmarket/isocalmarket-api/app/Http/Controllers/CategoryController.php) và [api.php](file:///d:/git/isocalmarket/isocalmarket-api/routes/api.php) khai báo API routes quản trị.
      2. Frontend Store: Bổ sung các phương thức `createCategory`, `updateCategory` và `deleteCategory` vào [ProductStore](file:///d:/git/isocalmarket/isocalmarket-web/src/app/features/products/product.store.ts).
      3. Frontend Routing & Navigation: Đăng ký route `/admin/categories` trong [app.routes.ts](file:///d:/git/isocalmarket/isocalmarket-web/src/app/app.routes.ts), thêm menu liên kết trong [sidebar.component.html](file:///d:/git/isocalmarket/isocalmarket-web/src/app/shared/layout/sidebar/sidebar.component.html) và [app.html](file:///d:/git/isocalmarket/isocalmarket-web/src/app/app.html) với icon `tag`.
      4. Frontend Components: Tạo [CategoryFormModalComponent](file:///d:/git/isocalmarket/isocalmarket-web/src/app/features/admin-categories/category-form-modal/category-form-modal.component.ts) xử lý form thêm/sửa, và [AdminCategoriesComponent](file:///d:/git/isocalmarket/isocalmarket-web/src/app/features/admin-categories/admin-categories.component.ts) làm trang giao diện chính.
      5. Kết quả kiểm tra: Dự án biên dịch (`npm run build`) thành công 100% không phát sinh bất kỳ lỗi TS/HTML compile nào. Gỡ bỏ hoàn toàn cảnh báo IconComponent không sử dụng.
  - **[HOÀN THÀNH] Đồng bộ bộ lọc và cột dữ liệu trang Quản lý danh mục**:
    - Yêu cầu: Thêm bộ lọc tương tự như các trang quản lý khác cho trang Quản lý danh mục.
    - Giải pháp & Triển khai:
      1. Backend Domain & Mappers: Cập nhật `Category` entity và `CategoryMapper` để bổ sung thêm trường thời gian tạo `createdAt` (ánh xạ từ `created_at` trong Eloquent Model).
      2. Backend Controllers: Cập nhật `CategoryController` ở các API lấy danh sách, tạo mới, và cập nhật để trả về giá trị `created_at` trong JSON response. Đã chạy thử nghiệm Backend unit tests pass 100%.
      3. Frontend TS Logic: Cập nhật [admin-categories.component.ts](file:///d:/git/isocalmarket/isocalmarket-web/src/app/features/admin-categories/admin-categories.component.ts) import `CustomSelectComponent` và `CustomDateTimeRangeComponent`. Khai báo 2 signals `selectedSort` và `selectedDateRange` và logic reactive computed `filteredCategories` hỗ trợ tìm kiếm text tiếng Việt, lọc ngày tạo và sắp xếp (Tên A-Z, Tên Z-A, Mới nhất, Cũ nhất).
      4. Frontend UI HTML: Cập nhật [admin-categories.component.html](file:///d:/git/isocalmarket/isocalmarket-web/src/app/features/admin-categories/admin-categories.component.html) bổ sung khung filter bar (khoảng thời gian tạo, sắp xếp) có giao diện đồng bộ 100% với các trang quản lý khác trong hệ thống. Thêm cột "Ngày tạo" vào bảng và nâng tổng số `colspan` của skeleton/empty table lên `6`.
      5. Frontend Toast: Cập nhật phương thức `deleteCategory` trong [product.store.ts](file:///d:/git/isocalmarket/isocalmarket-web/src/app/features/products/product.store.ts) để bắt thông báo lỗi tiếng Anh (như `Cannot delete category containing active products` hoặc `foreign key constraint fails`) và tự động chuyển đổi thành câu tiếng Việt thân thiện: *"Không thể xóa danh mục này vì hiện đang có sản phẩm thuộc danh mục!"* hiển thị qua Toast.
  - **[HOÀN THÀNH] Đồng bộ hóa nút thao tác và căn chỉnh giao diện bảng**:
    - Yêu cầu: Kiểm tra, đồng bộ hóa giao diện cột và nút Thao tác trên tất cả các trang: Lịch sử đơn hàng, Quản trị hệ thống (Dashboard), Quản lý sản phẩm, Quản lý danh mục, Quản lý kho hàng, Quản lý đơn hàng. Đảm bảo cố định sử dụng tên cột "Thao tác" (không dùng "Hành động") và định dạng nút bấm thao tác nhất quán.
    - Giải pháp & Triển khai:
      1. **Đổi tên cột**: Đổi tên tiêu đề cột cuối cùng từ "Hành động" sang "Thao tác" trong bảng quản lý đơn hàng [admin-orders.component.html](file:///d:/git/isocalmarket/isocalmarket-web/src/app/features/admin-orders/admin-orders.component.html) và bảng cảnh báo tồn kho thấp trên Dashboard [admin-dashboard.component.html](file:///d:/git/isocalmarket/isocalmarket-web/src/app/features/admin-dashboard/admin-dashboard.component.html).
      2. **Đồng bộ hóa Nút bấm**:
         - Nút **Sửa**: Đã đồng bộ sang kiểu có text + icon (icon `edit` + text "Sửa") kèm hiệu ứng transition/hover chuẩn cho cả 3 trang: Danh mục (`admin-categories`), Sản phẩm (`products`), và Kho hàng (`inventory`).
         - Nút **Xóa**: Đã đồng bộ sang kiểu có text + icon (icon `trash` + text "Xóa") màu đỏ đặc trưng cho 3 trang: Danh mục (`admin-categories`), Sản phẩm (`products`), và Kho hàng (`inventory`).
         - Nút **Chi tiết**: Đã đồng bộ sang kiểu có text + icon (icon `eye` + text "Chi tiết") màu xanh lục cho trang Lịch sử đơn hàng của khách (`orders`) và Quản trị đơn hàng (`admin-orders`).
      3. **Căn lề cột**: Chuyển căn lề cột "Thao tác" của Quản trị đơn hàng (`admin-orders`) sang căn phải (`text-right` và `justify-end`) để đồng nhất tuyệt đối 100% với cấu trúc bảng của tất cả các trang quản trị khác.
      4. **Xác minh biên dịch**: Chạy biên dịch `npm run build` thành công 100% không gặp bất kỳ lỗi nào.
  - **[HOÀN THÀNH] Chuyển đổi Tìm kiếm qua Backend (Không dấu & Không phân biệt hoa/thường)**:
    - Yêu cầu: Tất cả các chức năng tìm kiếm của các trang (Sản phẩm, Danh mục, Kho hàng, Đơn hàng, Lịch sử đơn hàng, Storefront) phải gửi yêu cầu lên Backend xử lý và so khớp không dấu tiếng Việt.
    - Đã tạo [AccentInsensitiveSearchTrait.php](file:///d:/git/isocalmarket/isocalmarket-api/app/Infrastructure/Persistence/Repositories/AccentInsensitiveSearchTrait.php): Cung cấp hàm helper `applyAccentInsensitiveSearch` thực hiện SQL `REPLACE` thay thế `đ`/`Đ` thành `d` ở tầng DB, kết hợp với collation `utf8mb4_unicode_ci` mặc định của MySQL để so khớp hoàn toàn không dấu và không phân biệt chữ hoa/thường cho tiếng Việt.
    - Đã cập nhật `CategoryRepository`, `ProductRepository`, `InventoryBatchRepository` và `OrderRepository` để sử dụng Trait và truy vấn SQL lọc dữ liệu không dấu từ database.
    - Đã cập nhật các Frontend Components (admin-categories, products, storefront) chuyển từ computed filter frontend sang việc gọi API store để fetch dữ liệu động từ backend mỗi khi từ khóa tìm kiếm thay đổi.
  - **[SỬA LỖI] Lỗi spam API tìm kiếm và không debounce**:
    - Nguyên nhân: Trước đó `debounceTime` được khởi tạo tại constructor của `CustomSearchInputComponent` sử dụng `@Input() debounce`. Do constructor chạy trước khi các input được Angular binding dữ liệu, thuộc tính `debounce` luôn mang giá trị `undefined` hay `0` khiến debounce bị vô hiệu hóa (phát ngay lập tức trên từng keystroke gõ phím).
    - Giải pháp khắc phục:
      1. Khai báo implement `OnInit` và chuyển logic khởi tạo Subscription của `valueSubject` từ `constructor` sang `ngOnInit()` trong [custom-search-input.component.ts](file:///d:/git/isocalmarket/isocalmarket-web/src/app/shared/components/custom-search-input/custom-search-input.component.ts) để đảm bảo giá trị `debounce` đã được truyền từ parent component.
      2. Tăng giá trị `debounce` mặc định lên `500`ms (thay vì 0) giúp tối ưu hóa hiệu suất mạng.
      3. Đồng bộ hóa toàn bộ các tệp HTML chứa ô tìm kiếm ([storefront.component.html](file:///d:/git/isocalmarket/isocalmarket-web/src/app/features/storefront/storefront.component.html), [products.component.html](file:///d:/git/isocalmarket/isocalmarket-web/src/app/features/products/products.component.html), [admin-categories.component.html](file:///d:/git/isocalmarket/isocalmarket-web/src/app/features/admin-categories/admin-categories.component.html), [inventory.component.html](file:///d:/git/isocalmarket/isocalmarket-web/src/app/features/inventory/inventory.component.html), [orders.component.html](file:///d:/git/isocalmarket/isocalmarket-web/src/app/features/orders/orders.component.html), [admin-orders.component.html](file:///d:/git/isocalmarket/isocalmarket-web/src/app/features/admin-orders/admin-orders.component.html)) sang `[debounce]="500"` để thống nhất trải nghiệm debounce 500ms trước khi call API backend.
      4. Loại bỏ hoàn toàn điều kiện tự lọc tìm kiếm ở frontend khỏi computed `filteredCategories` của [admin-categories.component.ts](file:///d:/git/isocalmarket/isocalmarket-web/src/app/features/admin-categories/admin-categories.component.ts) để tránh việc lọc trùng lặp hai lần.
      5. Chạy build frontend và unit tests backend thành công 100%.
  - **[TỐI ƯU HÓA] Giới hạn phạm vi tìm kiếm của Danh mục và Sản phẩm trên Backend**:
    - Vấn đề: Khi người dùng gõ các từ khóa ngắn như "ng", hệ thống tự động tìm kiếm trên cả tên, mô tả và slug. Điều này dẫn đến hầu hết tất cả các danh mục và sản phẩm đều trả về (vì từ "ng" xuất hiện rất nhiều trong các mô tả như "ngâm", "quảng", "lượng"). Người dùng tưởng lầm là chức năng tìm kiếm không lọc được.
    - Giải pháp khắc phục: Cập nhật [CategoryRepository.php](file:///d:/git/isocalmarket/isocalmarket-api/app/Infrastructure/Persistence/Repositories/CategoryRepository.php) và [ProductRepository.php](file:///d:/git/isocalmarket/isocalmarket-api/app/Infrastructure/Persistence/Repositories/ProductRepository.php) để chỉ thực hiện tìm kiếm so khớp không dấu trên cột `name` thay vì tìm cả ở description và slug. Kết quả tìm kiếm hiển thị giờ đây hoàn toàn chính xác theo tên và không bị nhiễu bởi các từ khóa mô tả.
    - Đã chạy `php artisan test` và `npm run build` thành công 100%.
  - **[HOÀN THÀNH] Triển khai Phân trang (Pagination) cho Quản lý Sản phẩm và Quản lý Danh mục**:
    - Backend:
      1. Khai báo thêm `findAllPaginated` trong `CategoryRepositoryInterface` và `CategoryRepository`.
      2. Thêm phương thức `getCategoriesPaginated` trong `CategoryService`.
      3. Cập nhật `CategoryController@index` trả về meta phân trang (`total`, `page`, `limit`, `last_page`) nếu có param `page` và `limit` truyền lên. Ngược lại, trả về danh sách đầy đủ cho dropdown.
    - Frontend:
      1. Cập nhật `ProductStore` khai báo các signals phân trang riêng cho danh mục (`paginatedCategories`, `totalCategories`, `currentCategoryPage`, `lastCategoryPage`) và hàm `fetchCategoriesPaginated`.
      2. Cập nhật `AdminCategoriesComponent` và `ProductsComponent` định cấu hình biến signal `currentPage`, giới hạn `limit = 10`, và phương thức `fetchPage()`.
      3. Thêm component UI `<app-pagination>` vào cuối bảng dữ liệu ở cả hai trang [products.component.html](file:///d:/git/isocalmarket/isocalmarket-web/src/app/features/products/products.component.html) và [admin-categories.component.html](file:///d:/git/isocalmarket/isocalmarket-web/src/app/features/admin-categories/admin-categories.component.html).
  - **[HOÀN THÀNH] Tối ưu tìm kiếm qua Database Index & Chuẩn hóa tên không dấu**:
    - Yêu cầu: Nâng cấp tìm kiếm Accent-Insensitive tối ưu hơn bằng cách tạo các cột chuẩn hóa sẵn ở DB và đánh Index, giúp tránh dùng SQL REPLACE động gây Full Table Scan, đồng thời tăng tính độc lập với hệ cơ sở dữ liệu.
    - Thực hiện Backend:
      1. Tạo migration `2026_07_17_114012_add_normalized_columns_to_tables.php` bổ sung `normalized_name` / `normalized_batch_name` có `index()` vào các bảng `categories`, `products`, `inventory_batches`, `users`. Kèm hàm xử lý backfill dữ liệu cũ không dấu tiếng Việt hoàn toàn bằng PHP.
      2. Tạo `NormalizeTextTrait` xử lý chuẩn hóa toàn bộ nguyên âm tiếng Việt có dấu về dạng không dấu (`á, à, ả... -> a`, `đ, Đ -> d`).
      3. Tích hợp `booted()` hook gọi `static::saving()` trong các Eloquent Models (`CategoryModel`, `ProductModel`, `InventoryBatchModel`, `UserModel`) để tự động gán giá trị chuẩn hóa khi tạo mới hoặc cập nhật.
      4. Cập nhật `AccentInsensitiveSearchTrait` để ánh xạ trực tiếp cột tìm kiếm sang cột normalized (`name -> normalized_name`, `batch_name -> normalized_batch_name`) và truy vấn thuần bằng Eloquent `like` mà không dùng hàm SQL raw REPLACE.
    - Kết quả: Chạy `php artisan migrate:fresh --seed` và `php artisan test` vượt qua 100%. Giao diện hoạt động trơn tru với hiệu năng tối ưu.

  - **[BẢO MẬT] Khắc phục lỗ hổng truy vấn (Resource Exhaustion / Query Limit Vulnerability)**:
    - Yêu cầu: Ngăn ngừa lỗ hổng làm treo máy chủ khi kẻ gian sử dụng giá trị `limit` cực lớn (ví dụ: `limit=100`, `limit=1000000`).
    - Giải pháp & Triển khai:
      1. Đã tạo [QueryLimitMiddleware.php](file:///d:/git/isocalmarket/isocalmarket-api/app/Http/Middleware/QueryLimitMiddleware.php) để làm sạch và chuẩn hóa tham số truy vấn `limit` (tối đa **20**, mặc định 10 nếu nhỏ hơn 0 hoặc không phải số) và `page` (tối thiểu 1, mặc định 1 nếu không hợp lệ).
      2. Đăng ký middleware này toàn cục trong nhóm `api` tại [app.php](file:///d:/git/isocalmarket/isocalmarket-api/bootstrap/app.php) để tự động bảo vệ tất cả các API route có phân trang (Sản phẩm, Danh mục, Kho hàng, Đơn hàng...).
      3. Đã viết và tích hợp Unit Test [QueryLimitMiddlewareTest.php](file:///d:/git/isocalmarket/isocalmarket-api/tests/Unit/QueryLimitMiddlewareTest.php) kiểm thử độc lập cho middleware này mà không phụ thuộc vào kết nối CSDL SQLite của môi trường test.
      4. Đã kiểm tra qua curl/Invoke-RestMethod thực tế từ PowerShell, kết quả phân trang ở các API Cửa hàng, Lịch sử đơn hàng, Quản lý sản phẩm, Danh mục, Kho hàng, Đơn hàng đều được chuẩn hóa và capped an toàn ở giới hạn tối đa 20 bản ghi/trang.

  - **[BẢO MẬT & RATE LIMIT] Cấu hình và kích hoạt Rate Limiting toàn cục cho API**:
    - Yêu cầu: Khắc phục việc cấu hình Rate Limiter cho `api` (ví dụ giới hạn 5 request/phút) không có tác dụng và không chặn request.
    - Giải pháp & Triển khai:
      1. Phát hiện nguyên nhân do Laravel 11 mặc định không tự động tích hợp middleware giới hạn tần suất `throttle:api` cho nhóm route `api` trong `bootstrap/app.php` (dù đã có cấu hình định nghĩa trong `AppServiceProvider.php`).
      2. Đã cập nhật [bootstrap/app.php](file:///d:/git/isocalmarket/isocalmarket-api/bootstrap/app.php) để bổ sung (append) middleware `'throttle:api'` vào nhóm middleware `'api'` toàn cục.
      3. Kiểm tra lại bằng lệnh `php artisan route:list -v` xác nhận mọi route thuộc nhóm `api` đều đã thừa hưởng đầy đủ `throttle:api`. Khi request quá giới hạn cấu hình (ví dụ 5 req/phút), Laravel sẽ tự động chặn và trả về lỗi HTTP 429.

  - **[BẢO MẬT & TRẢI NGHIỆM] Hủy toàn bộ pending request và thông báo quá tải khi nhận lỗi 429/503**:
    - Yêu cầu: Khi một request API nhận lỗi quá tải (429) hoặc bảo trì (503) từ backend, tự động hủy (cancel) toàn bộ các request API khác đang ở trạng thái pending trên Client (Angular) và hiển thị một thông báo Toast lỗi duy nhất.
    - Giải pháp & Triển khai:
      1. Đã cập nhật [auth.interceptor.ts](file:///d:/git/isocalmarket/isocalmarket-web/src/app/core/interceptors/auth.interceptor.ts) sử dụng `Subject` của RxJS và toán tử `takeUntil` để quản lý luồng request API.
      2. Khi phát hiện mã lỗi 429 hoặc 503, interceptor sẽ phát tín hiệu qua `Subject` giúp hủy bỏ ngay lập tức các request API pending còn lại (có loại trừ các request quan trọng như `/auth/refresh` và `/auth/logout` để bảo toàn trạng thái phiên).
      3. Inject `ToastService` để hiển thị cảnh báo lỗi quá tải hệ thống with cơ chế chặn trùng lặp trong vòng 3 giây (chỉ hiển thị 1 Toast duy nhất khi có loạt request lỗi cùng lúc).
      4. Biên dịch lại toàn bộ ứng dụng Frontend (`npm run build`) thành công 100% không phát sinh lỗi.

  - **[HOÀN THÀNH] Đồng bộ giao diện bộ lọc (Filter Bars) và di chuyển Thanh tìm kiếm xuống dưới**:
    - Yêu cầu: Đồng bộ hóa cách sắp xếp các bộ lọc trên tất cả các trang quản trị, dashboard và lịch sử. Bộ lọc trạng thái phải luôn nằm ở phía bên phải. Cấu trúc grid của các bộ lọc phải nhất quán. Đồng thời đưa thanh tìm kiếm từ header xuống filter bar nằm bên phải các bộ lọc để thuận tiện thao tác và giải phóng không gian header.
    - Giải pháp & Triển khai:
      1. **Sắp xếp lại vị trí**: Đã đảo vị trí của "Bộ lọc Trạng thái" và "Bộ lọc Khoảng thời gian" trong [orders.component.html](file:///d:/git/isocalmarket/isocalmarket-web/src/app/features/orders/orders.component.html) and [admin-orders.component.html](file:///d:/git/isocalmarket/isocalmarket-web/src/app/features/admin-orders/admin-orders.component.html) để đảm bộ bộ lọc trạng thái luôn nằm bên phải.
      2. **Chuyển Thanh tìm kiếm xuống Filter Bar**: Đã xóa `app-custom-search-input` trong slot `actions` ở `<app-page-header>` và đưa xuống làm cột thứ 3 bên phải các bộ lọc trong grid 3 cột (`grid-cols-1 sm:grid-cols-2 md:grid-cols-3`) tại các trang:
         - [products.component.html](file:///d:/git/isocalmarket/isocalmarket-web/src/app/features/products/products.component.html)
         - [admin-categories.component.html](file:///d:/git/isocalmarket/isocalmarket-web/src/app/features/admin-categories/admin-categories.component.html)
         - [inventory.component.html](file:///d:/git/isocalmarket/isocalmarket-web/src/app/features/inventory/inventory.component.html) (Cũng đồng thời đổi cấu trúc grid từ 3 cột bị trống sang 3 cột có chứa ô tìm kiếm)
         - [orders.component.html](file:///d:/git/isocalmarket/isocalmarket-web/src/app/features/orders/orders.component.html)
         - [admin-orders.component.html](file:///d:/git/isocalmarket/isocalmarket-web/src/app/features/admin-orders/admin-orders.component.html)
      3. **Đồng bộ Dashboard**: Đã chuyển cấu hình flex của hộp lọc trên trang Dashboard [admin-dashboard.component.html](file:///d:/git/isocalmarket/isocalmarket-web/src/app/features/admin-dashboard/admin-dashboard.component.html) sang dạng grid 3 cột (`grid-cols-1 md:grid-cols-3`) đồng bộ. Bộ lọc khoảng thời gian chiếm đúng 1 cột trái (độ rộng 1/3 tương ứng các bộ lọc của các trang khác), 2 cột còn lại dành cho phần chú giải trải dài thông thoáng, được căn gióng thẳng hàng đẹp mắt (`md:pt-6`).
      4. **Tối ưu trải nghiệm**: Đặt `containerClass="w-full"` cho ô tìm kiếm giúp nó co giãn khít đều cột thứ 3 trên màn hình lớn. Bổ sung nhãn `<label>Tìm kiếm</label>` viết hoa tự động đồng bộ theo design system.
      5. **Xác minh biên dịch**: Đã chạy build frontend hoàn tất thành công 100% không phát sinh lỗi.

  - **[BẢO MẬT & RÀNG BUỘC CƠ SỞ DỮ LIỆU] Ngăn chặn xóa danh mục chứa sản phẩm và Việt hóa thông báo lỗi**:
    - Yêu cầu: Nếu có bất kỳ sản phẩm nào hiện đang được gán cho danh mục, hệ thống phải ngăn cản việc xóa danh mục đó và báo lỗi thân thiện bằng tiếng Việt qua Toast.
    - Giải pháp & Triển khai:
      1. **Database Migration**: Sửa đổi migration gốc [2026_07_15_000001_create_products_table.php](file:///d:/git/isocalmarket/isocalmarket-api/database/migrations/2026_07_15_000001_create_products_table.php) đổi ràng buộc khóa ngoại `onDelete('set null')` thành `onDelete('restrict')` để tránh cơ chế tự động null của database.
      2. **Tạo Migration bổ sung**: Viết migration mới [2026_07_17_225500_alter_products_category_id_foreign_restrict.php](file:///d:/git/isocalmarket/isocalmarket-api/database/migrations/2026_07_17_225500_alter_products_category_id_foreign_restrict.php) để thay thế khóa ngoại cũ `products_category_id_foreign` trên database hiện tại sang `onDelete('restrict')`.
      3. **Cập nhật database**: Chạy `php artisan migrate` và `php artisan migrate:fresh --seed` để áp dụng cấu hình ràng buộc nghiêm ngặt mới.
      4. **Frontend Toast**: Cập nhật phương thức `deleteCategory` trong [product.store.ts](file:///d:/git/isocalmarket/isocalmarket-web/src/app/features/products/product.store.ts) để bắt thông báo lỗi tiếng Anh (như `Cannot delete category containing active products` hoặc `foreign key constraint fails`) và tự động chuyển đổi thành câu tiếng Việt thân thiện: *"Không thể xóa danh mục này vì hiện đang có sản phẩm thuộc danh mục!"* hiển thị qua Toast.

  - **[TÁCH BIỆT TRANG NẠP TIỀN & LỊCH SỬ NẠP TIỀN]**:
    - Yêu cầu: Tách biệt hoàn toàn tính năng Nạp tiền USDT và Lịch sử nạp tiền khỏi trang cá nhân Profile thành hai trang flat features độc lập, tạo các route cấp cao nhất và thêm 2 mục menu riêng biệt trên Sidebar điều hướng.
    - Giải pháp & Triển khai:
      1. **Tách biệt component**: Di chuyển mã nguồn và đổi tên sang standalone [DepositComponent](file:///d:/git/isocalmarket/isocalmarket-web/src/app/features/deposit/deposit.component.ts) và [DepositInvoiceComponent](file:///d:/git/isocalmarket/isocalmarket-web/src/app/features/deposit-invoice/deposit-invoice.component.ts). Tạo mới component [DepositHistoryComponent](file:///d:/git/isocalmarket/isocalmarket-web/src/app/features/deposit-history/deposit-history.component.ts) để quản lý lịch sử.
      2. **Cấu hình Route & Sidebar**: Đăng ký các route cấp cao nhất `/deposit`, `/deposit/history` và `/deposit/invoice/:id` trong [app.routes.ts](file:///d:/git/isocalmarket/isocalmarket-web/src/app/app.routes.ts) dưới sự bảo vệ của `AuthGuard`. Cập nhật Sidebar Desktop [sidebar.component.html](file:///d:/git/isocalmarket/isocalmarket-web/src/app/shared/layout/sidebar/sidebar.component.html) và Mobile [app.html](file:///d:/git/isocalmarket/isocalmarket-web/src/app/app.html) thành 2 menu riêng biệt: "Nạp tiền USDT" và "Lịch sử nạp tiền". Xóa bỏ folder cũ dưới profile.

  - **[PHÁT TRIỂN BACKEND API USDT VÀ TÍCH HỢP TỶ GIÁ COINGECKO & BOT BSCSCAN]**:
    - Yêu cầu: Triển khai database, API backend lấy tỷ giá USDT/VND động từ CoinGecko (cache 10 phút), BOT check tự động giao dịch qua BscScan API, và tích hợp Frontend gọi API thực thay cho mockup LocalStorage.
    - Giải pháp & Triển khai:
      1. **Database & Models**: Viết migrations thêm cột `balance` vào bảng `users` và tạo bảng `deposits` để lưu thông tin hóa đơn. Tạo model `DepositModel`, domain entity `Deposit` và `DepositMapper`. Cập nhật `UserModel`, `User` entity và `UserMapper` tương ứng.
      2. **CoinGecko Rate Service**: Viết logic gọi API CoinGecko simple price ở `DepositService.php`, sử dụng `Cache::remember('usdt_to_vnd_rate', 600, ...)` để cache tỷ giá trong 10 phút, tự động fallback về `26500` nếu lỗi.
      3. **BscScan BOT Check**: Phát triển logic quét block BEP-20 token transfer events của ví chủ sàn qua BscScan API, so khớp chính xác số lẻ thập phân đối soát của hóa đơn, TxHash chưa sử dụng, và tự động cộng tiền `balance` cho người dùng khi hợp lệ.
      4. **Bổ sung Bộ lọc & Tìm kiếm Frontend**: Tích hợp các bộ lọc khoảng thời gian (`CustomDateTimeRangeComponent`), bộ lọc trạng thái (`CustomSelectComponent`), thanh tìm kiếm (`CustomSearchInputComponent`), phân trang động và skeleton table loader vào [DepositHistoryComponent](file:///d:/git/isocalmarket/isocalmarket-web/src/app/features/deposit-history/deposit-history.component.ts) đồng bộ với các trang khác.
      5. **[SỬA LỖI] Lệch múi giờ (Timezone Mismatch) & Đếm ngược**: Sửa định dạng timestamp trong [DepositController.php](file:///d:/git/isocalmarket/isocalmarket-api/app/Http/Controllers/DepositController.php) sang ISO 8601 (`format('c')`) để phía trình duyệt Javascript tự động parse đúng múi giờ địa phương, ngăn chặn lỗi đếm ngược âm/tự hủy lệnh nạp tức thì.
      6. **[SỬA LỖI] Khống chế thời gian giao dịch 15 phút**: Thêm điều kiện so khớp epoch timestamp của transaction trên block với timestamp tạo hóa đơn ở backend, giới hạn giao dịch phải diễn ra trong vòng 900 giây (15 phút) hiệu lực của hóa đơn để chống gian lận.
      7. **Xác minh**: Chạy build thành công cả backend và frontend Angular 100% không phát sinh lỗi.

  - **[ĐỒNG BỘ FORM FIELDS & SỬA LỖI KHOẢNG CÁCH]**:
    - Yêu cầu: Khắc phục lỗi chồng đè nhãn (label) và các trường nhập liệu (input, textarea) native trên các form modal, đồng thời sửa lỗi khoảng cách giữa các nhãn và trường nhập liệu không đồng bộ trên hệ thống.
    - Giải pháp & Triển khai:
      1. **Stylesheet toàn cục**: Cấu hình lại `.form-field` và `.form-field > label` trong [styles.scss](file:///d:/git/isocalmarket/isocalmarket-web/src/styles.scss) bằng CSS thuần (loại bỏ `@apply` không ổn định cho flexbox). Thiết lập `margin-bottom: 0px !important` cho nhãn để vô hiệu hóa margin-bottom viết inline trong HTML, giao toàn bộ quyền kiểm soát khoảng cách (8px) cho thuộc tính `gap: 0.5rem` của flexbox container cha.
      2. **Đồng bộ hóa component**: Chuyển đổi 100% các native `<input class="form-input">` và `<textarea class="form-textarea">` còn lại trong các form modal sang sử dụng component dùng chung `<app-custom-input>` của hệ thống:
         - [edit-inventory-modal.component.html](file:///d:/git/isocalmarket/isocalmarket-web/src/app/features/inventory/edit-inventory-modal/edit-inventory-modal.component.html) và [edit-inventory-modal.component.ts](file:///d:/git/isocalmarket/isocalmarket-web/src/app/features/inventory/edit-inventory-modal/edit-inventory-modal.component.ts) (Import và khai báo CustomInputComponent).
         - [import-inventory-modal.component.html](file:///d:/git/isocalmarket/isocalmarket-web/src/app/features/inventory/import-inventory-modal/import-inventory-modal.component.html) và [import-inventory-modal.component.ts](file:///d:/git/isocalmarket/isocalmarket-web/src/app/features/inventory/import-inventory-modal/import-inventory-modal.component.ts) (Import và khai báo CustomInputComponent).
         - [product-form-modal.component.html](file:///d:/git/isocalmarket/isocalmarket-web/src/app/features/products/product-form-modal/product-form-modal.component.html) (Đổi native textarea sang app-custom-input).
         - [category-form-modal.component.html](file:///d:/git/isocalmarket/isocalmarket-web/src/app/features/admin-categories/category-form-modal/category-form-modal.component.html) (Đổi native textarea sang app-custom-input).
      3. **Xác minh & Build**: Đã chạy build frontend (`npm run build`) hoàn tất thành công 100% không phát sinh lỗi biên dịch. Tất cả form field trên hệ thống giờ đây đều thẳng hàng, đồng bộ và hiển thị đẹp mắt.

  - **[XÂY DỰNG TRANG BẢO TRÌ LỖI 503]**:
    - Yêu cầu: Xây dựng trang thông báo lỗi 503 (bảo trì hệ thống) dựa trên giao diện tham khảo, sử dụng màu sắc chủ đạo và màu phụ của dự án, hỗ trợ đầy đủ Light/Dark Mode và có các hiệu ứng vi hoạt ảnh mượt mà, không dùng emoji.
    - Giải pháp & Triển khai:
      1. **Cấu trúc trạng thái**: Thêm Signal `isMaintenance` trong [UiStateService](file:///d:/git/isocalmarket/isocalmarket-web/src/app/core/services/ui-state.service.ts) và expose ra ngoài qua [StateService](file:///d:/git/isocalmarket/isocalmarket-web/src/app/core/services/state.service.ts).
      2. **Http Interceptor**: Cấu hình [auth.interceptor.ts](file:///d:/git/isocalmarket/isocalmarket-web/src/app/core/interceptors/auth.interceptor.ts) để khi bắt gặp lỗi `503 Service Unavailable` từ API backend, tự động kích hoạt trạng thái `isMaintenance = true` và hủy toàn bộ các request pending khác.
      3. **Tạo Component mới**: Viết component standalone [MaintenanceComponent](file:///d:/git/isocalmarket/isocalmarket-web/src/app/features/maintenance/maintenance.component.ts) xử lý logic tải lại trang `window.location.reload()` thông qua nút "Thử lại".
      4. **Giao diện & Styles**: Xây dựng mẫu HTML [maintenance.component.html](file:///d:/git/isocalmarket/isocalmarket-web/src/app/features/maintenance/maintenance.component.html) có logo thương hiệu, theme switcher, minh họa công cụ SVG và số 503 to gradient, cùng stylesheet [maintenance.component.scss](file:///d:/git/isocalmarket/isocalmarket-web/src/app/features/maintenance/maintenance.component.scss) khai báo `:host { display: block; }` và các hiệu ứng động khi hover.
      5. **Tích hợp Root**: Đăng ký component vào [app.ts](file:///d:/git/isocalmarket/isocalmarket-web/src/app/app.ts) và cập nhật [app.html](file:///d:/git/isocalmarket/isocalmarket-web/src/app/app.html) bọc toàn bộ giao diện chính bằng `@if (!stateService.isMaintenance())` và hiển thị `<app-maintenance />` ở khối `@else`.

  - **[THÊM CỘT SỐ SẢN PHẨM TRÊN DANH MỤC]**:
    - Yêu cầu: Thêm cột hiển thị số lượng sản phẩm của từng danh mục tại trang Quản lý danh mục (`/admin/categories`). Giúp nhận biết danh mục có 0 sản phẩm để có thể xóa được, không làm thay đổi UI cũ và căn giữa cột.
    - Giải pháp & Triển khai:
      1. **Backend Domain**: Bổ sung trường `$productsCount` và phương thức getter `getProductsCount()` vào Domain Entity [Category.php](file:///d:/git/isocalmarket/isocalmarket-api/app/Domain/Entities/Category.php).
      2. **Backend Mapper**: Cập nhật phương thức `toDomain()` trong [CategoryMapper.php](file:///d:/git/isocalmarket/isocalmarket-api/app/Infrastructure/Persistence/Mappers/CategoryMapper.php) để map thuộc tính `products_count` từ Eloquent model sang Entity.
      3. **Backend Repository**: Sử dụng phương thức `withCount('products')` trong các truy vấn Eloquent tại [CategoryRepository.php](file:///d:/git/isocalmarket/isocalmarket-api/app/Infrastructure/Persistence/Repositories/CategoryRepository.php) (cả hàm `findAll` và `findAllPaginated`) giúp nạp trước số lượng sản phẩm liên quan một cách tối ưu.
      4. **Backend Controller**: Thêm trường `'products_count' => $cat->getProductsCount() ?? 0` trong response JSON của phương thức `index` tại [CategoryController.php](file:///d:/git/isocalmarket/isocalmarket-api/app/Http/Controllers/CategoryController.php).
      5. **Frontend HTML**: Cập nhật [admin-categories.component.html](file:///d:/git/isocalmarket/isocalmarket-web/src/app/features/admin-categories/admin-categories.component.html) để chèn cột "Số sản phẩm" căn giữa, hiển thị số lượng sản phẩm dưới dạng text màu giống trang kho hàng (đỏ cho 0, xanh cho >0), đồng thời tăng `colspan` từ 6 lên 7 cho skeleton loader và hàng thông báo rỗng.
      6. **Xác minh & Build**: Chạy build frontend hoàn tất không phát sinh lỗi biên dịch.

  - **[ĐỒNG BỘ CSS SỐ LƯỢNG BẰNG APP-BADGE & TỐI ƯU MÀU TRẠNG THÁI]**:
    - Yêu cầu: Đồng bộ giao diện hiển thị các cột số lượng trên các bảng quản lý (Danh mục, Kho hàng, Đơn hàng) bằng cách dùng `<app-badge>` tương tự như trang sản phẩm, hỗ trợ font-size to hơn (`size="lg"`), và thay đổi màu của trạng thái "Hoàn thành" (`completed`) trong kho hàng.
    - Giải pháp & Triển khai:
      1. **Đồng bộ các cột số lượng bằng `<app-badge>` size="lg"**:
         - Cập nhật [admin-categories.component.html](file:///d:/git/isocalmarket/isocalmarket-web/src/app/features/admin-categories/admin-categories.component.html): Thay thế thẻ `<span>` hiển thị số sản phẩm bằng `<app-badge>` với variant `success` (nếu >0) và `danger` (nếu =0).
         - Cập nhật [inventory.component.html](file:///d:/git/isocalmarket/isocalmarket-web/src/app/features/inventory/inventory.component.html): Thay thế text thô hiển thị tỉ lệ tồn kho khả dụng bằng `<app-badge>` với variant `success` (nếu còn nguyên lô), `warning` (nếu đã bán một phần), và `danger` (nếu hết hàng).
         - Cập nhật [admin-orders.component.html](file:///d:/git/isocalmarket/isocalmarket-web/src/app/features/admin-orders/admin-orders.component.html): Thay thế text thô hiển thị số lượng mua bằng `<app-badge>` màu xám (`neutral`).
         - Cập nhật [products.component.html](file:///d:/git/isocalmarket/isocalmarket-web/src/app/features/products/products.component.html): Nâng size badge tồn kho khả dụng từ `md` lên `lg` để đồng nhất font-size to hơn.
      2. **Sửa lỗi Angular Compiler (Import Missing)**:
         - Cập nhật [admin-categories.component.ts](file:///d:/git/isocalmarket/isocalmarket-web/src/app/features/admin-categories/admin-categories.component.ts) để import `BadgeComponent` vào danh sách `imports` của component.
      3. **Tối ưu màu trạng thái "Hoàn thành"**:
         - Cập nhật [inventory.component.html](file:///d:/git/isocalmarket/isocalmarket-web/src/app/features/inventory/inventory.component.html): Thay đổi variant của trạng thái `completed` (Hoàn thành) của lô hàng từ màu xám (`neutral`) sang xanh dương chủ đạo (`primary`) theo feedback của người dùng.
      4. **Xác minh & Build**: Chạy build frontend (`npm run build`) hoàn tất thành công 100% không phát sinh lỗi biên dịch.

  - **[TÍCH HỢP SOFT DELETE SẢN PHẨM & TÍNH NĂNG KHÔI PHỤC]**:
    - Yêu cầu: Chuyển cơ chế xóa sản phẩm từ Hard Delete sang Soft Delete. Trang quản trị mặc định ẩn sản phẩm đã xóa, hỗ trợ lọc xem sản phẩm đã xóa, hiển thị gạch ngang (strike-through) mờ trên toàn dòng và hiển thị nút Khôi phục để phục hồi sản phẩm.
    - Giải pháp & Triển khai:
      1. **Database Migration**: Tạo migration [2026_07_18_000001_add_soft_deletes_to_products_table.php](file:///d:/git/isocalmarket/isocalmarket-api/database/migrations/2026_07_18_000001_add_soft_deletes_to_products_table.php) và chạy thành công để thêm cột `deleted_at` vào bảng `products`.
      2. **Backend Domain & Persistence**:
         - Cập nhật [ProductModel.php](file:///d:/git/isocalmarket/isocalmarket-api/app/Infrastructure/Persistence/Eloquent/ProductModel.php) khai báo `use SoftDeletes;`.
         - Bổ sung thuộc tính `deletedAt` và các getter tương ứng vào Domain Entity [Product.php](file:///d:/git/isocalmarket/isocalmarket-api/app/Domain/Entities/Product.php).
         - Cập nhật [ProductMapper.php](file:///d:/git/isocalmarket/isocalmarket-api/app/Infrastructure/Persistence/Mappers/ProductMapper.php) map `deleted_at` và hỗ trợ `withTrashed()` khi lưu dữ liệu.
      3. **Backend Repositories & Services**:
         - Cập nhật [ProductRepositoryInterface.php](file:///d:/git/isocalmarket/isocalmarket-api/app/Domain/Repositories/ProductRepositoryInterface.php) và [ProductRepository.php](file:///d:/git/isocalmarket/isocalmarket-api/app/Infrastructure/Persistence/Repositories/ProductRepository.php) bổ sung tham số `$filterDeleted` (giá trị: `active`, `deleted`, `all`) trong các hàm query và định nghĩa phương thức `restore()`.
         - Cập nhật [ProductService.php](file:///d:/git/isocalmarket/isocalmarket-api/app/Application/Services/ProductService.php) gọi các phương thức tương ứng và bổ sung phương thức `restoreProduct()`.
      4. **Backend Controllers & Routes**:
         - Cập nhật index action của [ProductController.php](file:///d:/git/isocalmarket/isocalmarket-api/app/Http/Controllers/ProductController.php) tiếp nhận `filter_deleted` qua URL query, map và serialize trường `deleted_at` và `is_deleted` trong JSON response.
         - Thêm action `restore($id)` trong [ProductController.php](file:///d:/git/isocalmarket/isocalmarket-api/app/Http/Controllers/ProductController.php) và đăng ký route `POST /v1/products/{id}/restore` trong [api.php](file:///d:/git/isocalmarket/isocalmarket-api/routes/api.php).
      5. **Frontend API & Store**:
         - Cập nhật [product.store.ts](file:///d:/git/isocalmarket/isocalmarket-web/src/app/features/products/product.store.ts) thêm tham số `filterDeleted` vào `fetchProducts` và định nghĩa phương thức API call `restoreProduct(id)`.
      6. **Frontend Component & HTML**:
         - Cập nhật [products.component.ts](file:///d:/git/isocalmarket/isocalmarket-web/src/app/features/products/products.component.ts): Thêm lựa chọn *"Đã xóa"* (`value: 'deleted'`) vào `statusOptions`, cập nhật logic `fetchFilteredProducts` để phân biệt logic status và soft deletes, đồng thời triển khai hàm `restoreProduct(product)` mở modal xác thực.
         - Cập nhật [products.component.html](file:///d:/git/isocalmarket/isocalmarket-web/src/app/features/products/products.component.html) bọc thêm class `line-through text-slate-400 dark:text-slate-500 opacity-60 bg-slate-50/50 dark:bg-slate-950/20` cho hàng dữ liệu có `p.is_deleted`. Đồng thời hiển thị nút **Khôi phục** (màu xanh lá) thay thế cho nút Sửa/Xóa.
      7. **Xác minh & Build**: Chạy build frontend hoàn tất không phát sinh lỗi biên dịch.

  - **[BẢO MẬT & PHÂN QUYỀN SẢN PHẨM SOFT DELETED]**:
    - Yêu cầu: Ngăn chặn hoàn toàn khách hàng vãng lai/user thường xem sản phẩm đã xóa ở trang cửa hàng (Storefront) hoặc API chi tiết sản phẩm. Ngăn chặn tuyệt đối việc mua các sản phẩm này tại khâu checkout đơn hàng.
    - Giải pháp & Triển khai:
      1. **Bảo mật danh sách**: Cập nhật index action của [ProductController.php](file:///d:/git/isocalmarket/isocalmarket-api/app/Http/Controllers/ProductController.php), nếu yêu cầu không phải là Admin, tự động ép buộc `filterDeleted = 'active'`, ngăn chặn việc truyền parameter lên query string để rà quét.
      2. **Bảo mật chi tiết**: Cập nhật show action của [ProductController.php](file:///d:/git/isocalmarket/isocalmarket-api/app/Http/Controllers/ProductController.php), nếu sản phẩm đã bị soft deleted (`$product->isDeleted()`) và không phải là Admin yêu cầu, trả về ngay lỗi `404 Not Found`.
      3. **Bảo mật Checkout (Mua hàng)**: Cập nhật hàm tạo đơn hàng `createOrder()` trong [OrderService.php](file:///d:/git/isocalmarket/isocalmarket-api/app/Application/Services/OrderService.php). Nếu sản phẩm được đặt mua có trạng thái `isDeleted() = true`, ném ngoại lệ ngăn cản tạo đơn hàng: *"Sản phẩm không khả dụng hoặc đã bị ngừng kinh doanh."*
      4. **Xác minh & Build**: Chạy build frontend hoàn tất không phát sinh lỗi biên dịch.

  - **[MẶC ĐỊNH BỘ LỌC TRẠNG THÁI SẢN PHẨM]**:
    - Yêu cầu: Đặt giá trị mặc định cho bộ lọc Trạng thái tại trang Quản lý sản phẩm luôn là "Đang hoạt động" khi mới tải trang.
    - Giải pháp & Triển khai: Đổi giá trị khởi tạo của signal `selectedStatusFilter` trong [products.component.ts](file:///d:/git/isocalmarket/isocalmarket-web/src/app/features/products/products.component.ts) từ `''` (Tất cả trạng thái) thành `'active'` (Đang hoạt động).
    - Kết quả: Khi người dùng truy cập trang, hệ thống tự động lọc sản phẩm đang hoạt động, đồng thời vẫn cho phép admin chuyển đổi linh hoạt sang bộ lọc Đã xóa hoặc Ngừng hoạt động.

  - **[Badge trạng thái của sản phẩm soft-deleted]**:
    - Yêu cầu: Khắc phục việc sản phẩm đã bị soft-deleted vẫn hiển thị trạng thái "Hoạt động" (màu xanh lá).
    - Giải pháp & Triển khai: Cập nhật [products.component.html](file:///d:/git/isocalmarket/isocalmarket-web/src/app/features/products/products.component.html) bọc điều kiện `@if (p.is_deleted)` để kết xuất Badge trạng thái màu đỏ (variant="danger") và hiển thị chữ "Đã xóa".
    - Kết quả: Badge trạng thái hiển thị chuẩn xác trực quan.

  - **[BỘ LỌC TẤT CẢ TRẠNG THÁI BAO GỒM SẢN PHẨM SOFT-DELETED]**:
    - Yêu cầu: Khi chọn bộ lọc là "Tất cả trạng thái" (rỗng), hệ thống phải nạp tất cả các sản phẩm đang hoạt động, ngừng hoạt động và cả các sản phẩm đã bị xóa mềm (soft-deleted).
    - Giải pháp & Triển khai: Cập nhật logic trong phương thức `fetchFilteredProducts` tại [products.component.ts](file:///d:/git/isocalmarket/isocalmarket-web/src/app/features/products/products.component.ts). Nếu `statusVal === ''`, gán `filterDeleted = 'all'` và truyền lên API Backend.
    - Kết quả: Khi chọn Tất cả trạng thái, admin xem được toàn bộ sản phẩm (kể cả sản phẩm bị xóa mềm được gạch ngang dòng mờ).

  - **[TỐI ƯU HÓA UI SẢN PHẨM SOFT-DELETED]**:
    - Yêu cầu: Nâng cấp cách hiển thị dòng sản phẩm bị xóa mềm để không làm mất thẩm mỹ và khó đọc do gạch ngang cả dòng.
    - Giải pháp & Triển khai:
      1. Bỏ gạch ngang (`line-through`) trên thẻ `<tr>` của cả dòng. Thay thế bằng làm mờ nhẹ (`opacity-50`) kết hợp đổi màu nền mờ tối (`bg-slate-100/40` ở Light Mode, `dark:bg-slate-900/10` ở Dark Mode).
      2. Áp dụng gạch ngang (`line-through`) riêng cho Tên sản phẩm và Mô tả sản phẩm để dễ nhận biết thông tin text bị xóa.
      3. Thêm một chỉ báo badge đỏ có icon thùng rác nhỏ kế bên tên sản phẩm: `Đã xóa`.
    - Kết quả: Giao diện bảng gọn gàng, hiển thị cực kỳ chuyên nghiệp và trực quan.

  - **[GẠCH NGANG CÁC TRƯỜNG THÔNG TIN SẢN PHẨM SOFT-DELETED]**:
    - Yêu cầu: Áp dụng gạch ngang (`line-through`) cho các trường thông tin Loại, Số TKQC, Slug, Đơn giá (VND) và Tồn kho khả dụng của sản phẩm đã bị xóa mềm để tăng tính đồng nhất và trực quan.
    - Giải pháp & Triển khai: Cập nhật [products.component.html](file:///d:/git/isocalmarket/isocalmarket-web/src/app/features/products/products.component.html) bọc điều kiện `[ngClass]="{ 'line-through': p.is_deleted }"` cho các thẻ span thuộc tính (attributes), slug, đơn giá và badge tồn kho khả dụng.
    - Kết quả: Tất cả các trường thông tin cốt lõi của sản phẩm bị xóa đều được gạch ngang, hiển thị rõ ràng và chuẩn xác.

  - **[GẠCH NGANG ID & VÔ HIỆU HÓA TOOLTIP SẢN PHẨM SOFT-DELETED]**:
    - Yêu cầu: Áp dụng gạch ngang (`line-through`) cho ID sản phẩm và vô hiệu hóa toàn bộ tooltip trên dòng khi sản phẩm đã bị xóa mềm.
    - Giải pháp & Triển khai:
      1. Cập nhật [products.component.html](file:///d:/git/isocalmarket/isocalmarket-web/src/app/features/products/products.component.html) bổ sung `[ngClass]="{ 'line-through': p.is_deleted }"` cho ID sản phẩm.
      2. Cập nhật tất cả các thuộc tính `[appTooltip]` trên dòng (ID, copy button, tên, mô tả, các thuộc tính, slug, giá cả, tồn kho khả dụng) sử dụng toán tử ba ngôi để trả về chuỗi rỗng `''` khi `p.is_deleted` là true, vô hiệu hóa tooltip hoàn toàn.
    - Kết quả: Không còn hiển thị tooltip khi di chuột vào sản phẩm bị xóa mềm, và ID đã được gạch ngang đồng bộ.

  - **[GIAO DIỆN NẠP TIỀN USDT BEP-20 & LỊCH SỬ GIAO DỊCH]**:
    - Yêu cầu: Xây dựng trang nạp tiền và lịch sử nạp tiền dành cho khách hàng bằng USDT mạng BEP-20. Hỗ trợ quy đổi tỷ giá sang VND, sinh số lẻ thập phân đối soát, tạo hóa đơn thanh toán có route riêng, tích hợp bộ đếm ngược 15 phút và hiển thị QR Code. Giao diện phải nằm trên sidebar chính của người dùng, hỗ trợ Light/Dark Mode và tận dụng tối đa các shared components.
    - Giải pháp & Triển khai:
      1. **Trang Tạo Lệnh Nạp**: Cập nhật [ProfileDepositComponent](file:///d:/git/isocalmarket/isocalmarket-web/src/app/features/profile/deposit/profile-deposit.component.ts) và tệp HTML [profile-deposit.component.html](file:///d:/git/isocalmarket/isocalmarket-web/src/app/features/profile/deposit/profile-deposit.component.html). Tích hợp ô nhập số lượng USDT, tự động tính VND quy đổi tương đương theo tỷ giá `1 USDT = 26.500 VND`. Khi click "Tạo lệnh nạp tiền", sinh ngẫu nhiên số lẻ thập phân đối soát (VD: `10.0043` USDT), tạo hóa đơn ở LocalStorage và redirect sang trang hóa đơn.
      2. **Trang Hóa Đơn Thanh Toán**: Tạo mới standalone [ProfileDepositInvoiceComponent](file:///d:/git/isocalmarket/isocalmarket-web/src/app/features/profile/deposit-invoice/profile-deposit-invoice.component.ts) và tệp HTML [profile-deposit-invoice.component.html](file:///d:/git/isocalmarket/isocalmarket-web/src/app/features/profile/deposit-invoice/profile-deposit-invoice.component.html). Thiết kế chính xác theo ảnh tham khảo: hiển thị mã lệnh `#232`, đếm ngược 15 phút bằng RxJS `interval` (VD: `14:37s` đỏ nhấp nháy), badge BSC, QR Code thanh toán, số tiền lẻ lớn màu đỏ kèm nút copy, địa chỉ ví nhận tiền của sàn kèm nút copy lớn, khung cảnh báo quan trọng màu vàng nhạt, nút "Hủy Lệnh Nạp" màu đỏ và nút "Tôi đã chuyển khoản" để check giao dịch.
      3. **Tích hợp Router & Menu Sidebar**: Đăng ký route `deposit` và `deposit/invoice/:id` trong [profile.routes.ts](file:///d:/git/isocalmarket/isocalmarket-web/src/app/features/profile/profile.routes.ts). Tích hợp menu **"Nạp tiền USDT"** trực tiếp vào Sidebar chính ở cả Desktop [sidebar.component.html](file:///d:/git/isocalmarket/isocalmarket-web/src/app/shared/layout/sidebar/sidebar.component.html) và Mobile [app.html](file:///d:/git/isocalmarket/isocalmarket-web/src/app/app.html) để người dùng có thể thấy ngay lập tức dưới mục Lịch sử đơn hàng.
      4. **Xác minh & Build**: Chạy build frontend Angular (`npm run build`) hoàn tất thành công 100% không phát sinh lỗi biên dịch.

  - **[ĐỘC LẬP HÓA MENU NẠP TIỀN & PHÂN TRANG LỊCH SỬ NẠP]**:
    - Yêu cầu: Tách biệt hoàn toàn các trang Nạp tiền USDT, Lịch sử nạp và Hóa đơn nạp ra khỏi Profile. Cung cấp bộ lọc, tìm kiếm và phân trang chuẩn cho trang Lịch sử nạp tiền tương tự các tính năng khác.
    - Giải pháp & Triển khai:
      1. Đã tách các component nạp tiền ra ngoài thư mục `profile` thành các feature độc lập: `DepositComponent`, `DepositInvoiceComponent` và `DepositHistoryComponent`.
      2. Đã cập nhật `app.routes.ts` để khai báo các route nạp tiền ở cấp cao nhất: `/deposit`, `/deposit/history` và `/deposit/invoice/:id`.
      3. Đã cập nhật menu sidebar và menu mobile để liên kết trực tiếp tới các trang nạp độc lập này.
      4. Xây dựng trang Lịch sử nạp tiền đầy đủ tính năng: bộ lọc trạng thái (Chờ thanh toán, Thành công, Đã hủy/Hết hạn, Thất bại), lọc khoảng ngày tháng bằng `CustomDateTimeRangeComponent`, tìm kiếm văn bản bằng `CustomSearchInputComponent` và phân trang bằng `app-pagination`.

  - **[TÍCH HỢP TỶ GIÁ COINGECKO & XỬ LÝ ĐỐI SOÁT TỰ ĐỘNG BẰNG BOT]**:
    - Yêu cầu: Tự động lấy tỷ giá USDT/VND từ API CoinGecko, cache kết quả trong 10 phút ở Backend. Quét giao dịch tự động qua mạng blockchain thông qua API BscScan.
    - Giải pháp & Triển khai:
      1. Triển khai phương thức `getUsdtToVndRate()` trong [DepositService.php](file:///d:/git/isocalmarket/isocalmarket-api/app/Application/Services/DepositService.php) gọi API CoinGecko và lưu Cache 10 phút. Nếu API lỗi, dùng tỷ giá fallback là `26,500 VND`.
      2. Xây dựng bot quét transaction BEP-20 thực nhận gửi tới ví Admin qua BscScan API trong `checkTransaction()`, kiểm tra chính xác số tiền lẻ (Suffix) đối soát ngẫu nhiên, giới hạn thời hạn chuyển 15 phút, lưu TxHash vào DB để tránh Replay Attack.

  - **[XÓA V VÍ DEMO & THIẾT LẬP VALIDATION VÍ NHẬN TIỀN]**:
    - Yêu cầu: Xóa bỏ hoàn toàn địa chỉ ví nhận USDT BEP-20 demo hardcode ở cả Frontend và Backend. Nếu Admin chưa thiết lập ví nhận trong file `.env`, hệ thống phải báo lỗi Admin chưa cấu hình ví nhận tiền.
    - Giải pháp & Triển khai:
      1. **Backend**: Thay thế ví nhận demo fallback bằng chuỗi trống `env('ADMIN_WALLET_ADDRESS', '')` trong [DepositService.php](file:///d:/git/isocalmarket/isocalmarket-api/app/Application/Services/DepositService.php). Thêm kiểm tra validation ở `createDeposit` và `checkTransaction`. Nếu ví trống, ném `RuntimeException` tiếng Anh.
      2. **Backend Controller**: Bắt exception ở [DepositController.php](file:///d:/git/isocalmarket/isocalmarket-api/app/Http/Controllers/DepositController.php) và trả về phản hồi HTTP 400. Đưa thêm trường `adminWalletAddress` động vào hàm format JSON của hóa đơn.
      3. **Frontend**: Chuyển `adminWalletAddress` trong [deposit-invoice.component.ts](file:///d:/git/isocalmarket/isocalmarket-web/src/app/features/deposit-invoice/deposit-invoice.component.ts) thành một Writable Signal. Lấy ví động từ dữ liệu hóa đơn trả về bởi API Backend.
      4. **Dịch thông báo lỗi**: Thêm hàm dịch ở Frontend cho các thông điệp lỗi liên quan đến cấu hình ví của Admin từ Backend, hiển thị tiếng Việt trên Toast / Card màn hình lỗi rõ ràng.

  - **[TỔNG QUÁT HÓA & VALIDATION KHÓA SCANNER API]**:
    - Yêu cầu: Kiểm tra nếu hệ thống chưa cấu hình `SCANNER_API_KEY` (không dùng tên biến BscScan để dễ dàng mở rộng nhiều blockchain scan API khác sau này) thì không cho phép người dùng nạp tiền, trả về thông điệp lỗi tương tự ví nhận.
    - Giải pháp & Triển khai:
      1. **Backend**: Đổi tên biến `$bscScanApiKey` và khóa cấu hình môi trường `.env` thành `$scannerApiKey` và `SCANNER_API_KEY` trong [DepositService.php](file:///d:/git/isocalmarket/isocalmarket-api/app/Application/Services/DepositService.php). Xóa bỏ giá trị demo fallback, mặc định về rỗng `env('SCANNER_API_KEY', '')`.
      2. **Validation**: Thêm điều kiện kiểm tra `empty($this->scannerApiKey)` trong cả `createDeposit()` và `checkTransaction()`. Nếu thiếu, ném lỗi: `"The administrator has not configured the scanner API key yet."`.
      3. **Frontend**: Cập nhật hàm dịch lỗi tại [deposit.component.ts](file:///d:/git/isocalmarket/isocalmarket-web/src/app/features/deposit/deposit.component.ts) và [deposit-invoice.component.ts](file:///d:/git/isocalmarket/isocalmarket-web/src/app/features/deposit-invoice/deposit-invoice.component.ts). Nếu backend trả về thông báo lỗi thiếu Scanner API Key, tự động dịch thành thông điệp tiếng Việt:
         - *"Hệ thống đối soát tự động chưa được cấu hình Scanner API Key! Vui lòng quay lại sau."*
         - *"Hệ thống đối soát tự động chưa được cấu hình Scanner API Key. Vui lòng liên hệ Admin!"*
      4. **Xác minh**: Chạy `npm run build` thành công 100% không phát sinh lỗi biên dịch.

  - **[SỬA LỖI RESPONSIVE, TRÀN CHỮ VÀ ĐỒNG HỒ ĐẾM NGƯỢC]**:
    - Yêu cầu: Khắc phục lỗi giao diện bị chèn ép quá mức ở viewport hẹp (như 770px), chữ trên nút bị tràn/cắt xén, và đồng hồ đếm ngược thời gian nạp không hoạt động.
    - Giải pháp & Triển khai:
      1. Chuyển đổi breakpoint layout 2 cột từ `sm:grid-cols-12` sang `lg:grid-cols-12` trong [deposit-invoice.component.html](file:///d:/git/isocalmarket/isocalmarket-web/src/app/features/deposit-invoice/deposit-invoice.component.html). Khi kích thước hiển thị hẹp (dưới 1024px), giao diện tự động xếp dọc (1 cột) gọn gàng, không bị chèn ép.
      2. Sửa lại container các nút Hủy/Chuyển khoản thành `flex flex-col gap-3 w-full` để nút luôn rộng rãi, hiển thị đầy đủ text, không bị che khuất hoặc tràn chữ.
      3. Thêm `whitespace-nowrap` vào badge mạng lưới BEP-20 để đảm bảo không bị gãy dòng.
      4. Chuyển đổi các biến đếm ngược `countdownText` và `timeLeft` từ thuộc tính class thường sang Angular Signals để kích hoạt cơ chế Change Detection liên tục khi `interval` chạy, sửa lỗi đồng hồ đứng yên không đếm ngược.
      5. Thêm `adminWalletAddress?: string` vào interface `DepositHistoryItem` để sửa triệt để lỗi biên dịch TypeScript.
      6. Đã chạy biên dịch Angular (`npm run build`) và hoàn thành thành công 100% không có lỗi.

  - **[CHẶN TẠO NHIỀU LỆNH NẠP TIỀN SONG SONG (ANTI-SPAM)]**:
    - Yêu cầu: Không cho phép người dùng tạo nhiều hóa đơn nạp tiền song song để tránh spam làm đầy cơ sở dữ liệu. Nếu muốn tạo lệnh mới, người dùng bắt buộc phải hủy lệnh cũ đang chờ thanh toán.
    - Giải pháp & Triển khai:
      1. **Backend**: Thêm điều kiện kiểm tra tồn tại hóa đơn `pending_payment` của chính user đó trước khi cho phép tạo mới trong [DepositService.php](file:///d:/git/isocalmarket/isocalmarket-api/app/Application/Services/DepositService.php). Nếu có, ném lỗi: `"You already have an active deposit order. Please cancel the previous order before creating a new one."`
      2. **API Controller**: Cập nhật [DepositController.php](file:///d:/git/isocalmarket/isocalmarket-api/app/Http/Controllers/DepositController.php) để khi ném ngoại lệ này, tự động truy vấn tìm ID hóa đơn đang active và trả về kèm theo trong trường `active_invoice_id` của response JSON lỗi.
      3. **Frontend**: Cập nhật hàm dịch lỗi tại [deposit.component.ts](file:///d:/git/isocalmarket/isocalmarket-web/src/app/features/deposit/deposit.component.ts). Thay vì hiển thị Toast, hệ thống sẽ sử dụng `ModalService.confirm()` hiển thị một Confirm Modal chuyên nghiệp:
         - *Tiêu đề*: *"Lệnh nạp tiền chưa hoàn tất"*
         - *Nội dung*: *"Bạn đang có một lệnh nạp tiền chưa thanh toán. Vui lòng kiểm tra hóa đơn hoặc hủy lệnh nạp cũ trước khi tạo lệnh mới!"*
         - *Nút xác nhận*: *"Xem lệnh nạp tiền"* (Click vào sẽ tự động điều hướng người dùng tới trang hóa đơn `/deposit/invoice/:id` của lệnh nạp chưa thanh toán đó).
         - *Nút hủy*: *"Đóng modal"*.
      4. **Xác minh**: Chạy `npm run build` thành công 100% không phát sinh lỗi biên dịch.

  - **[ĐUÔI SỐ LẺ TĂNG DẦN THEO THỨ TỰ (SEQUENTIAL SUFFIX)]**:
    - Yêu cầu: Đổi cơ chế sinh đuôi số lẻ đối soát ngẫu nhiên sang tăng dần theo thứ tự. Ví dụ: Cùng nạp 1 USDT, người A nạp 1.0000 USDT, người B nạp 1.0001 USDT, người C nạp 1.0002 USDT, đảm bảo không trùng nhau tại cùng thời điểm.
    - Giải pháp & Triển khai:
      1. **Thuật toán**: Cập nhật [DepositService.php](file:///d:/git/isocalmarket/isocalmarket-api/app/Application/Services/DepositService.php). Lấy tất cả số tiền cần trả (`amount_to_pay`) của các hóa đơn cùng lượng tiền gốc đang ở trạng thái `pending_payment`.
      2. Chạy vòng lặp tìm giá trị cộng thêm bắt đầu từ `0.0000` tăng dần mỗi bước `0.0001` cho tới khi tìm thấy giá trị duy nhất chưa được sử dụng. Giới hạn chênh lệch tối đa là `0.0999` (hỗ trợ tối đa 1000 lệnh trùng lượng tiền chẵn chạy song song).
      3. **Đồng bộ hóa**: Sử dụng `$baseAmount` đã làm tròn trong constructor tạo thực thể `Deposit` để đảm bảo lưu trữ chính xác.
      4. **Xác minh**: Chạy `npm run build` hoàn thành thành công 100%.

  - **[BẢO MẬT: LOẠI BỎ CREDENTIALS SEED TRONG ENV & DATABASE SEEDER]**:
    - Yêu cầu: Xóa bỏ hoàn toàn các tài khoản admin/customer seed có ảnh hưởng đến bảo mật khai báo trong file `.env`. Hệ thống chỉ cho phép đăng nhập qua dữ liệu trực tiếp trong bảng `users` của database, không được nhận hoặc bypass từ cấu hình file `.env`.
    - Giải pháp & Triển khai:
      1. **File cấu hình**: Xóa bỏ 5 biến môi trường `SEED_ADMIN_EMAIL`, `SEED_ADMIN_PASSWORD`, `SEED_CUSTOMER1_EMAIL`, `SEED_CUSTOMER2_EMAIL`, `SEED_CUSTOMER_PASSWORD` khỏi [.env](file:///d:/git/isocalmarket/isocalmarket-api/.env) và [.env.example](file:///d:/git/isocalmarket/isocalmarket-api/.env.example).
      2. **Database Seeder**: Cập nhật [DatabaseSeeder.php](file:///d:/git/isocalmarket/isocalmarket-api/database/seeders/DatabaseSeeder.php) để tạo tài khoản `admin` sử dụng thông tin tĩnh an toàn (Email: `admin@isocalmarket.com`, Password: `Admin@Isocal2025!`) trực tiếp trong mã nguồn seeder thay vì gọi qua `env()`.
      3. **Xác minh**: Chạy lại lệnh khởi tạo lại cơ sở dữ liệu `php artisan migrate:fresh --seed` và chạy bộ kiểm thử `php artisan test` đạt kết quả 100% thành công.

  - **[CLICK VÀO SỐ TIỀN NẠP ĐỂ COPY]**:
    - Yêu cầu: Cho phép người dùng click trực tiếp vào chữ hiển thị số tiền nạp USDT ở trang hóa đơn thanh toán để copy nhanh giá trị chuyển khoản, tăng trải nghiệm người dùng ngoài nút copy có sẵn kế bên.
    - Giải pháp & Triển khai:
      1. **Frontend**: Cập nhật [deposit-invoice.component.html](file:///d:/git/isocalmarket/isocalmarket-web/src/app/features/deposit-invoice/deposit-invoice.component.html) bổ sung sự kiện `(click)="copyText(invoice.amountToPay.toString(), 'Số tiền cần chuyển')"` trực tiếp trên thẻ `<span>` hiển thị lượng USDT.
      2. **Giao diện**: Thêm các class Tailwind `cursor-pointer hover:opacity-80 active:scale-95 transition-all` và thuộc tính `title="Click để copy số tiền"` giúp phần tử phản hồi trực quan khi di chuột vào hoặc click.
      3. **Xác minh**: Chạy `npm run build` thành công 100%.

  - **[TẠO LỆNH FORCED LOGOUT TOÀN HỆ THỐNG (FORCE LOGOUT ALL)]**:
    - Yêu cầu: Tạo 1 lệnh duy nhất để dọn dẹp/xóa sạch toàn bộ token (cả Access Token & Refresh Token) ra khỏi hệ thống, ép buộc toàn bộ user trên mọi thiết bị đăng xuất ngay lập tức.
    - Giải pháp & Triển khai:
      1. Đã đăng ký một Artisan Command mới `auth:force-logout-all` trong [console.php](file:///d:/git/isocalmarket/isocalmarket-api/routes/console.php).
      2. Khi chạy, lệnh sẽ xóa sạch toàn bộ `refresh_token_hash` và `refresh_token_expired_at` trong Database (bảng `users`) của tất cả người dùng, ngăn chặn tuyệt đối việc làm mới (refresh) token.
      3. Đồng thời, lệnh sẽ kích hoạt lệnh con `key:generate` để tạo lại `APP_KEY` mới trong file `.env`. Việc này làm cho mọi JWT Access Token đang được lưu trữ ở cookie client ngay lập tức bị sai chữ ký và mất hiệu lực hoàn toàn mà không cần chờ hết hạn 30 phút.
      4. Chạy kiểm tra helper command `php artisan auth:force-logout-all --help` thành công.

  - **[HIỂN THỊ MENU SIDEBAR KHI CHƯA ĐĂNG NHẬP]**:
    - Yêu cầu: Hiển thị đầy đủ các menu "Lịch sử đơn hàng", "Nạp tiền USDT", và "Lịch sử nạp tiền" trên sidebar/menu (cả ở Desktop và Mobile) ngay cả khi người dùng chưa đăng nhập. Nếu bấm vào các menu này khi chưa đăng nhập, tự động chuyển hướng người dùng sang trang Đăng nhập `/login`.
    - Giải pháp & Triển khai:
      1. Loại bỏ các chỉ thị `*ngIf="stateService.isAuthenticated()"` trên các thẻ liên kết của các menu này trong [sidebar.component.html](file:///d:/git/isocalmarket/isocalmarket-web/src/app/shared/layout/sidebar/sidebar.component.html) và [app.html](file:///d:/git/isocalmarket/isocalmarket-web/src/app/app.html).
      2. Chuyển đổi thuộc tính `routerLink="..."` thành thuộc tính động `[routerLink]="stateService.isAuthenticated() ? '...' : '/login'"` để chuyển hướng linh hoạt người dùng chưa đăng nhập về trang đăng nhập.
      3. Đã chạy kiểm tra biên dịch `npm run build` thành công không phát sinh lỗi.

  - **[TỐI ƯU HÓA DATABASE INDEX & KHẮC PHỤC LỖI TRUY VẤN N+1]**:
    - Yêu cầu: Kiểm tra cấu trúc database, tối ưu hóa các chỉ mục (indexes) và cải thiện hiệu suất truy vấn hệ thống.
    - Giải pháp & Triển khai:
      1. **Bổ sung Indexes**: Tạo và chạy migration `2026_07_18_020000_add_performance_indexes_to_tables.php` bổ sung index cho `deleted_at`, `created_at` trên bảng `products` (SoftDeletes và pagination order), và `created_at` trên bảng `inventory_batches` (FIFO order).
      2. **Khắc phục lỗi N+1 Query**: 
         - Thêm quan hệ `activeBatches()` trong `ProductModel.php`.
         - Cấu hình eager loading `with(['activeBatches'])` trong `ProductRepository.php` when query list products.
         - Cập nhật hàm `getAvailableCount` để kiểm tra và lấy trực tiếp dữ liệu từ quan hệ đã được load sẵn trong RAM, giảm từ `1 + N` query xuống còn đúng `2` query duy nhất cho toàn bộ danh sách sản phẩm.
      3. **Xác minh**: Chạy `php artisan migrate` và `php artisan test` hoàn thành 100% test case passed.

  - **[TỐI ƯU RESPONSIVE LAYOUT & BỘ LỌC, TIÊU ĐỀ, CARD DI ĐỘNG]**:
    - Yêu cầu: Khắc phục lỗi co hẹp quá mức giao diện trang cửa hàng (Storefront) ở độ rộng màn hình trung bình tầm 770px, tối ưu hiển thị bộ lọc, tránh gãy dòng tiêu đề/badge và lỗi tràn/đè chữ ở footer card trên thiết bị di động.
    - Giải pháp & Triển khai:
      1. **Chuyển đổi Breakpoint**: Nâng breakpoint kích hoạt layout cố định (Desktop Sidebar) và Header dịch chuyển từ `md` (768px) lên `lg` (1024px).
      2. **Layout app.html**: Cập nhật container Sidebar di động thành `lg:hidden` và container chứa router-outlet dịch lề từ `md:pl-72` sang `lg:pl-72`.
      3. **Header**: Cập nhật header padding-left và z-index thành `lg:pl-72 lg:z-30`. Cập nhật Hamburger menu và Logo thành `lg:hidden`.
      4. **Sidebar**: Cập nhật sidebar cố định thành `hidden lg:flex`.
      5. **Tối ưu bộ lọc Storefront**: Ẩn nhãn `"Chế độ xem:"` và `"Sắp xếp danh mục:"` cùng văn bản nút `"Dạng Card"`, `"Dạng List"` trên các màn hình nhỏ hơn `xl` (1280px) bằng class `hidden xl:inline`. Đổi container bộ lọc sang `flex-row items-center justify-between` và thu gọn dropdown sang `w-40 xl:w-56` để xếp ngang siêu gọn, đẹp và không bị tràn ở cả 660px và 1025px (khi có sidebar cố định hiển thị bên trái).
      6. **Tối ưu tiêu đề nhóm**: Đổi container tiêu đề nhóm và badge số lượng sang `flex-col sm:flex-row gap-3` để trên mobile tự xếp dọc giúp tiêu đề chiếm trọn chiều rộng, không bị badge ép gây rớt chữ. Bổ sung `shrink-0 whitespace-nowrap self-start` cho badge để chống gãy dòng. Căn chỉnh icon `items-start` và hạ size chữ tiêu đề xuống `text-base sm:text-lg`.
      7. **Tối ưu footer card sản phẩm**: Đổi sang `flex-col sm:flex-row gap-3`. Trên mobile, Đơn giá dàn ngang dạt hai bên (`flex justify-between w-full`), giúp chữ `"Đơn giá"` nằm trái và giá tiền `"240,000 VND"` nằm phải. Nút `"Mua Ngay"` chiếm trọn chiều rộng (`w-full`) ở dòng dưới để ngăn chặn 100% tình trạng đè chữ hay rớt dòng thô kệch.
      8. **Xác minh**: Chạy `npm run build` thành công 100% không có lỗi.

  - **[TỐI ƯU RESPONSIVE DẠNG LIST TRÊN MOBILE & DESKTOP HẸP]**:
    - Yêu cầu: Khắc phục triệt để lỗi cuộn ngang ở chế độ xem danh sách (View Mode: List) trên trang cửa hàng (Storefront). Lỗi xảy ra trên cả thiết bị di động và màn hình laptop hẹp (như 1027px khi có sidebar cố định chiếm 256px diện tích hiển thị).
    - Giải pháp & Triển khai:
      1. **Thay đổi Breakpoint ẩn/hiện**: Nâng breakpoint tách biệt giao diện từ `sm` (640px) lên `lg` (1024px).
         - **Màn hình lớn (`lg:block hidden`)**: Hiển thị dạng bảng (Table) cổ điển.
         - **Màn hình nhỏ và trung bình/máy tính bảng (`lg:hidden block`)**: Tự động chuyển sang giao diện Vertical List dẹt tối giản 100% width, nút "Mua Ngay" hiển thị trực quan ở góc phải mỗi card.
      2. **Triệt tiêu scrollbar ngang trên desktop hẹp**:
         - Loại bỏ thuộc tính `min-w-[700px]` của `<table>` khi ở trên màn hình desktop, thay bằng `min-w-[650px] lg:min-w-0`. Class `lg:min-w-0` giúp bảng co giãn linh hoạt theo container cha mà không bị tràn.
         - Giảm độ rộng các cột cố định: Cột Đơn giá giảm từ `w-44` (176px) xuống `w-32` (128px), cột Tồn kho và Thao tác giảm từ `w-36` (144px) xuống `w-28` (112px). Phần không gian còn lại ưu tiên cho cột Tên sản phẩm/Thuộc tính co giãn tự nhiên.
      3. **Xác minh**: Chạy biên dịch dự án thành công không phát sinh lỗi.

  - **[SỬA LỖI TRÙNG LẶP ACTIVE TRÊN SIDEBAR KHI LOGGED OUT]**:
    - Yêu cầu: Khắc phục lỗi khi người dùng chưa đăng nhập và truy cập trang `/login` (Đăng nhập), cả 3 menu "Lịch sử đơn hàng", "Nạp tiền USDT", và "Lịch sử nạp tiền" đều bị kích hoạt trạng thái active cùng lúc.
    - Giải pháp & Triển khai:
      1. Nguyên nhân: Do gán routerLink động trỏ về `/login` khi chưa đăng nhập (`[routerLink]="stateService.isAuthenticated() ? '/path' : '/login'"`). Khi ở trang `/login`, Angular nhận diện URL khớp hoàn toàn với routerLink của cả 3 thẻ và gán active class cho cả 3.
      2. Khắc phục: Chuyển toàn bộ 3 menu về routerLink tĩnh tương ứng (`/orders`, `/deposit`, `/deposit/history`) ở cả Sidebar Desktop ([sidebar.component.html](file:///d:/git/isocalmarket/isocalmarket-web/src/app/shared/layout/sidebar/sidebar.component.html)) và Sidebar Mobile ([app.html](file:///d:/git/isocalmarket/isocalmarket-web/src/app/app.html)).
      3. Tính năng bảo vệ: Khi người dùng click, Router vẫn được chuyển tiếp tới các URL gốc này. Các router này đã được bảo vệ hoàn hảo bằng `AuthGuard` ở file định tuyến chính nên người dùng chưa đăng nhập sẽ được tự động chuyển hướng về `/login?returnUrl=...`. Không còn tình trạng trùng lặp active khi ở màn hình login.
      4. Xác minh: Chạy build frontend Angular thành công 100% không phát sinh lỗi.

  - **[CHUYỂN CẤU HÌNH HỆ THỐNG VÀO DATABASE & GIAO DIỆN ADMIN (SYSTEM SETTINGS)]**:
    - Yêu cầu: Chuyển cấu hình `ADMIN_WALLET_ADDRESS` và `SCANNER_API_KEY` từ file `.env` vào DB. Thêm giao diện quản lý cấu hình này trong admin.
    - Giải pháp & Triển khai:
      1. **Database**: Tạo migration tạo bảng `system_settings` (key primary, value text, description string). Cập nhật `DatabaseSeeder.php` seed sẵn giá trị ví admin và api key mặc định.
      2. **Backend**: Triển khai DDD gồm Entity, Model, Mapper, Repository. Tạo `SystemSettingService` xử lý get/set setting (hỗ trợ Cache 1 giờ). Cập nhật `DepositService` đọc ví và api key scanner động từ `SystemSettingService`, chuyển đổi địa chỉ gọi API sang `https://api.etherscan.io/api`.
      3. **API Controllers**: Viết `SystemSettingController` cung cấp API lấy và cập nhật cấu hình cho admin, đăng ký route tại `api.php`.
      4. **Frontend**: Tạo Slice Store `SystemSettingStore` quản lý state. Thêm route `/admin/settings`, đăng ký menu liên kết tại Sidebar Desktop (`sidebar.component.html`) và Mobile (`app.html`).
      5. **UI Component**: Xây dựng `AdminSettingsComponent` (ts & html) tuân thủ `design.md`, sử dụng card kính mờ, form field tiêu chuẩn và skeleton loader. Chuyển đổi tên cấu hình sang Etherscan API Key, thêm nút ẩn/hiện API Key bằng icon con mắt (`eye` / `eye-slash`), link hướng dẫn `https://etherscan.io/apidashboard`, và bổ sung chế độ khóa/mở khóa bảo mật (`isLocked` = true mặc định).
      6. **Xác minh**: Chạy `php artisan test` và `npm run build` thành công 100% không có lỗi.  - **[PHÁT TRIỂN TÍNH NĂNG USER API & QUẢN LÝ THÀNH VIÊN]**:
    - Yêu cầu: Bổ sung tính năng User API (B2B API Key) cho cả khách hàng và quản trị viên, đi kèm giao diện sinh/làm mới API Key, xem tài liệu tích hợp (CURL, URL mẫu, JSON phản hồi). Đồng thời xây dựng tính năng Quản lý thành viên (CRUD, đổi mật khẩu, nạp/trừ số dư, và tạo/thu hồi API Key) cho Admin.
    - Giải pháp & Triển khai:
      1. **Database**: Tạo migration bổ sung cột `api_key` dạng VARCHAR UNIQUE vào bảng `users`. Cập nhật `DatabaseSeeder.php` để sinh sẵn API Key mặc định cho tài khoản admin/customer mẫu.
      2. **Backend Services & Controllers**:
         - Viết `B2bAuthMiddleware` để nhận diện khóa API Key truyền qua URL parameter (`?api_key=...`), HTTP Header `X-API-KEY` hoặc Authorization Header Bearer.
         - Viết `B2bController` xử lý lấy danh sách sản phẩm, chi tiết sản phẩm và mua sản phẩm thông qua API (tự động kiểm tra ví VND của người dùng, thực hiện trừ tiền và giao tài khoản ngay lập tức).
         - Viết `AdminUserController` cung cấp API CRUD người dùng, phân trang và tìm kiếm.
         - Thêm endpoint `generateApiKey()` trong `ProfileController` để người dùng cá nhân có thể tự sinh/làm mới API Key của mình.
      3. **Frontend Slice Stores & Routers**:
         - Tạo `UserStore` để đồng bộ quản lý danh sách thành viên ở frontend.
         - Cập nhật `AuthStore` thêm hàm call `/profile/api-key`.
         - Đăng ký các route `/user-api` và `/admin/users` vào `app.routes.ts`.
      4. **UI Components**:
         - Xây dựng `UserApiComponent` (ts & html) hiển thị API Key cá nhân và tài liệu tích hợp API B2B cực kỳ chi tiết, dùng helper tránh quote-escape lỗi trong Angular template, hỗ trợ copy một chạm.
         - Xây dựng `AdminUsersComponent` dạng bảng quản lý trực quan, hiển thị ngắn gọn ID/API Key kèm tooltip, tích hợp dynamic modal `UserFormModalComponent` cho form thêm/sửa, tuân thủ nghiêm ngặt rule responsive bảng và thiết kế nút bấm có viền mờ nhạt.
         - Tích hợp liên kết vào Desktop Sidebar (`sidebar.component.html`) và Mobile Sidebar Drawer (`app.html`).
      5. **Xác minh**: Chạy `php artisan migrate:fresh --seed` và `php artisan test` thành công. Biên dịch Angular `npm run build` thành công 100% không phát sinh bất kỳ lỗi nào.
      6. **Sửa lỗi & Tối ưu**:
         - Khắc phục lỗi Duplicate API call bằng cách gán `[loading]="state.authStore.authLoading()"` và chèn guard chặn click spam khi sinh API Key.
         - Cập nhật background và text color của các khung mã code/CURL/JSON trong tài liệu `User API` tương thích động theo chế độ sáng/tối (Light/Dark Mode) thông qua màu dịu `bg-slate-50 border-slate-200/60 text-slate-700` (ở Light Mode) và `bg-slate-900/60 border-slate-800/40 text-slate-300` (ở Dark Mode), nút copy đổi màu chữ hover tương ứng.
  - **[BỔ SUNG PHÂN TRANG, BỘ LỌC & TÌM KIẾM CHO THÀNH VIÊN VÀ B2B APIs]**:
    - Yêu cầu: Bổ sung tính năng phân trang, bộ lọc và tìm kiếm còn thiếu cho giao diện Quản lý Thành viên và các API B2B/Đại lý.
    - Giải pháp & Triển khai:
      1. **Quản lý Thành viên (UI & Backend)**: Bổ sung dropdown lọc vai trò (`Tất cả`, `Admin`, `User`) sử dụng component `<app-custom-select>` có sẵn trên giao diện `/admin/users`. Cập nhật `AdminUserController`, `UserRepositoryInterface`, `UserRepository` và `UserStore` (Angular) để truyền nhận tham số `role` khi gọi dữ liệu phân trang.
      2. **API đại lý lấy danh sách sản phẩm (`GET /api/v1/b2b/products`)**: Bổ sung bộ lọc `category_id` và tìm kiếm `search` (giữ nguyên không phân trang theo yêu cầu).
      3. **API Admin quản lý đại lý B2B (`GET /api/v1/admin/b2b/users`)**: Triển khai phân trang (`page`, `limit`), bộ lọc (`role`, `has_api_key`), và tìm kiếm (`search` theo tên/email/sđt) trả về cấu trúc JSON phân trang chuẩn.
      4. **API Admin xem đơn hàng B2B (`GET /api/v1/admin/b2b/orders`)**: Bổ sung bộ lọc `status`, `user_id` và tìm kiếm `search` (theo mã đơn hàng hoặc thông tin khách hàng).
      5. **Xác minh**: Chạy unit test suite Laravel backend (`php artisan test`) và biên dịch Angular frontend (`npm run build`) thành công 100% không phát sinh lỗi.

  - **[SỬA LỖI HIỂN THỊ PHÂN TRANG 0-0 BẢN GHI]**:
    - Nhận diện lỗi: Khi truy cập các trang như Quản lý danh mục hoặc Quản lý sản phẩm, phần phân trang (pagination) hiển thị dòng chữ lỗi: "Hiển thị 0-0 trên tổng số 0 bản ghi" mặc dù danh sách vẫn có dữ liệu và hiển thị các nút chọn trang bình thường.
    - Nguyên nhân: Các trang này gọi `<app-pagination>` nhưng chưa bind (hoặc thiếu) các thuộc tính đầu vào quan trọng là `[totalItems]`, `[itemsPerPage]` và `[itemName]`. Khi thiếu `[totalItems]`, component pagination nhận giá trị mặc định là `0` và tính toán chỉ số hiển thị bắt đầu và kết thúc đều bằng `0`, dẫn đến hiển thị lỗi.
    - Giải pháp khắc phục:
      1. Cập nhật [admin-categories.component.html](file:///d:/git/isocalmarket/isocalmarket-web/src/app/features/admin-categories/admin-categories.component.html): Bổ sung `[totalItems]="state.productStore.totalCategories()"`, `[itemsPerPage]="limit"` và `[itemName]="'danh mục'"`.
      2. Cập nhật [products.component.html](file:///d:/git/isocalmarket/isocalmarket-web/src/app/features/products/products.component.html): Bổ sung `[totalItems]="state.productStore.totalProducts()"`, `[itemsPerPage]="limit"` và `[itemName]="'sản phẩm'"`.
      3. Cập nhật [admin-users.component.html](file:///d:/git/isocalmarket/isocalmarket-web/src/app/features/admin-users/admin-users.component.html): Bổ sung `[itemsPerPage]="limit"` và `[itemName]="'thành viên'"`.
      4. Cập nhật [admin-orders.component.html](file:///d:/git/isocalmarket/isocalmarket-web/src/app/features/admin-orders/admin-orders.component.html): Loại bỏ dòng chữ `Tổng số: {{ total() }} đơn hàng` bị trùng lặp hiển thị, đồng thời bổ sung `[itemName]="'đơn hàng'"` cho đồng bộ và rõ ràng.
      5. Chạy build kiểm tra frontend thành công không phát sinh lỗi biên dịch.

  - **[LOẠI BỎ ĐƯỜNG KẺ BORDER TRONG MODAL & TOÀN BỘ CÁC TRANG (FLAT DESIGN)]**:
    - Yêu cầu: Loại bỏ các đường kẻ phân chia nằm ngang (`border-t` / `border-b`) không nhất quán hoặc không cần thiết trên toàn bộ các modal (User, Product, Category, Import, 2FA, Giỏ hàng, Welcome, v.v.) và các trang (Deposit, Storefront, Profile, Settings, v.v.) để đạt được thiết kế phẳng (Flat Design) đồng bộ, tránh chia cắt thô cứng.
    - Giải pháp & Triển khai:
      1. **Modal Child Components**: Xóa `border-t` ở các footer chứa nút action (Hủy/Lưu) và phần phân tách thông tin của các modal: `user-form-modal.component.html`, `product-form-modal.component.html`, `import-inventory-modal.component.html`, `edit-inventory-modal.component.html`, `category-form-modal.component.html`, `enable-2fa-modal.component.html`, `disable-2fa-modal.component.html`, `cart-modal.component.html`, và `welcome-notice-modal.component.html`.
      2. **Modal Shared Components (Header)**: Xóa `border-b` phân chia header tại `modal-wrapper.component.html` và `modal.component.html` để đồng bộ thiết kế không viền ngang, mang lại sự liền mạch cho modal.
      3. **Các trang chức năng**:
         - Xóa `border-t` ở nút bấm action và `border-b` ở tỷ giá của `deposit.component.html` và `deposit-invoice.component.html`.
         - Xóa `border-t` ở nút lưu cài đặt của `admin-settings.component.html`.
         - Xóa `border-b` ở tiêu đề trang và `border-t` ở dòng tổng tiền của `order-detail.component.html`.
         - Xóa `border-b` ở phần header của các trang profile: `profile-information.component.html`, `profile-password.component.html`, và `profile-security.component.html`.
         - Xóa `border-b` ở tiêu đề nhóm sản phẩm, `border-t` ở khu vực giá card sản phẩm, và `border-t` ở nút "Xem thêm" trên `storefront.component.html`.
      4. **Xác minh**: Giữ lại các border hợp lý cho Table Rows (`<tr>`) và các border-b của card section headers để phân cấp dữ liệu tốt, chạy build ứng dụng frontend thành công không có lỗi.

  - **[SỬA LỖI ĐIỀU HƯỚNG TỪ DASHBOARD SANG INVENTORY]**:
    - Nhận diện lỗi: Nút "Nhập kho" ở bảng "Lọc Tồn Kho" ngoài Dashboard khi bấm chỉ chuyển sang trang `/admin/inventory` mà không có phản ứng gì tiếp theo, cũng không tự động lọc sản phẩm hay mở modal nhập kho.
    - Nguyên nhân: Nút "Nhập kho" ngoài Dashboard gửi tham số `product_id` qua URL query parameters (`/admin/inventory?product_id=...`), nhưng ở [inventory.component.ts](file:///d:/git/isocalmarket/isocalmarket-web/src/app/features/inventory/inventory.component.ts) chưa thực hiện đọc tham số này từ Router để kích hoạt các hành động tương ứng.
    - Giải pháp khắc phục:
      1. Import `ActivatedRoute` từ `@angular/router` và inject vào class.
      2. Trong `ngOnInit()`, sau khi `fetchProducts()` tải xong danh sách sản phẩm, kiểm tra nếu `product_id` tồn tại trong `route.snapshot.queryParams` thì truyền trực tiếp `productId` này vào phương thức `openImportDrawer(productId)` để tự động mở modal nhập kho có chọn sẵn sản phẩm cần nhập.
      3. Giải pháp này giúp giữ nguyên bộ lọc bảng hiển thị ở background đằng sau modal, không làm thay đổi hay cưỡng ép thay đổi bộ lọc chung của trang Inventory khi người dùng click phím tắt ngoài Dashboard.
1076: 
  - **[CHẶN CHỈNH SỬA SẢN PHẨM SOFT-DELETED]**:
    - Yêu cầu: Đảm bảo khi sản phẩm đã bị xóa mềm (soft deleted), nhất định không cho phép sửa thông tin sản phẩm đó.
    - Giải pháp & Triển khai:
      1. **Backend Service**: Cập nhật hàm `updateProduct` trong [ProductService.php](file:///d:/git/isocalmarket/isocalmarket-api/app/Application/Services/ProductService.php) để kiểm tra `$product->isDeleted()`. Nếu đúng, ném ra ngoại lệ ngăn cản cập nhật: *"Cannot update a soft-deleted product."*
      2. **Backend Controller**: Cập nhật hàm `update` trong [ProductController.php](file:///d:/git/isocalmarket/isocalmarket-api/app/Http/Controllers/ProductController.php). Thực hiện kiểm tra trạng thái sản phẩm bằng `getProductById($id)`. Nếu sản phẩm đã bị xóa mềm, trả về phản hồi JSON lỗi với mã trạng thái `400 Bad Request` và thông điệp tương ứng.
      3. **Frontend Component**: Cập nhật [products.component.ts](file:///d:/git/isocalmarket/isocalmarket-web/src/app/features/products/products.component.ts) kiểm tra `product.is_deleted` tại hàm `openEditDrawer()`. Nếu đúng, chặn mở modal và hiển thị thông báo lỗi bằng Toast: *"Không thể chỉnh sửa sản phẩm đã bị xóa mềm!"*.
      4. **Frontend Form Modal**: Cập nhật [product-form-modal.component.ts](file:///d:/git/isocalmarket/isocalmarket-web/src/app/features/products/product-form-modal/product-form-modal.component.ts) kiểm tra `modalData.is_deleted` tại hàm `ngOnInit()`. Nếu sản phẩm đã bị xóa mềm, đóng modal lập tức và báo lỗi Toast tương tự để làm lớp bảo vệ dự phòng.
      5. **Xác minh**: Chạy unit test suite của Backend (`php artisan test`) thành công và kiểm tra biên dịch Frontend (`npm run build`) hoàn thành thành công 100% không phát sinh lỗi.

  - **[HOÀN THÀNH] QUÉT LẠI VÀ DỌN DẸP COMMENT RÁC, JSDOC TRÊN CẢ 2 DỰ ÁN (ANGULAR-WEB3-WALLET & ISOCALMARKET)**:
    - **Yêu cầu:** Rà soát và xóa các comment rác, comment JSDoc, comment HTML và comment CSS/PHP thừa, giữ cho mã nguồn phẳng, sạch sẽ tối đa.
    - **Thực hiện:**
      1. **angular-web3-wallet & isocalmarket-web (Frontend):** Quét triệt để thư mục `src/app/`, loại bỏ tất cả comment JSDoc `/** ... */` trên class/inputs/outputs/methods, xóa 100% comment HTML `<!-- ... -->` và CSS block comments.
      2. **isocalmarket-api (Backend Laravel):** Quét an toàn trong thư mục `app/` để xóa comment dòng đơn `//` hiển nhiên. Không xử lý inline comments ở đuôi dòng và không xóa các dòng bắt đầu bằng `#[` (PHP8 Attributes) để tránh lỗi cú pháp.
      3. **Xác minh:** Biên dịch thành công 100% cả hai frontend và chạy test backend thành công `passed 14/14 tests`.

  - **[HOÀN THÀNH] Triển Khai Hệ Thống Bảng Dùng Chung (Shared Table Component)**:
    - **Yêu cầu**: Sao chép component Table từ `D:\git\angular-web3-wallet\src\app\shared\components\table` sang dự án, kế thừa và áp dụng cho tất cả các trang/tính năng, hỗ trợ sắp xếp theo cột (sorting) và responsive.
    - **Thực hiện**:
      1. Đã tạo và cấu hình thành công component dùng chung `TableComponent` và `TableCellDirective` cùng mảng cấu hình `TableColumn[]` tại `isocalmarket-web/src/app/shared/components/table`.
      2. Đã refactor toàn bộ các trang và component bảng thô trong hệ thống sang sử dụng component bảng dùng chung:
         - **ProductsComponent** (Quản lý sản phẩm)
         - **InventoryComponent** (Quản lý kho hàng)
         - **OrdersComponent** (Lịch sử đơn hàng khách hàng)
         - **AdminOrdersComponent** (Quản lý đơn hàng admin)
         - **AdminUsersComponent** (Quản lý thành viên admin)
         - **AdminCategoriesComponent** (Quản lý danh mục admin)
         - **AdminDashboardComponent** (Bảng cảnh báo tồn kho thấp ở Dashboard)
         - **DepositHistoryComponent** (Lịch sử nạp tiền)
         - **StorefrontComponent** (Chế độ xem List ở trang chủ cửa hàng)
      3. Cấu hình import `TableComponent`, `TableCellDirective`, `TableColumn` và `TooltipDirective` đầy đủ cho các module tương ứng.
   - **[SỬA LỖI SKELETON LOADER FORM CHƯA TRÙNG KHỚP]**:
    - **Nhận diện lỗi**: Trang Cấu hình hệ thống (`/admin/settings`) và trang Nạp tiền (`/deposit`) sử dụng skeleton loader kiểu `type="form"` chưa trùng khớp về kích thước (bị giới hạn cứng bởi `max-w-lg` làm co hẹp chiều ngang), số lượng trường nhập liệu giả (mặc định 5 trường trong khi thực tế chỉ cần 2), và cấu hình nút bấm giả.
    - **Giải pháp & Triển khai**:
      1. Loại bỏ class giới hạn chiều rộng `max-w-lg` khỏi template [skeleton-loader.component.html](file:///d:/git/isocalmarket/isocalmarket-web/src/app/shared/components/skeleton-loader/skeleton-loader.component.html) để component tự do co giãn theo container Card bọc ngoài.
      2. Bổ sung `@Input() buttonLayout: 'full' | 'split' | 'end' = 'split';` vào [skeleton-loader.component.ts](file:///d:/git/isocalmarket/isocalmarket-web/src/app/shared/components/skeleton-loader/skeleton-loader.component.ts) và triển khai cấu trúc HTML nút bấm giả tương ứng:
         - `end`: 2 nút giả nhỏ căn lề phải (áp dụng cho trang Settings với `[rows]="2"`).
         - `full`: 1 nút giả dài rộng 100% (áp dụng cho trang Deposit với `[rows]="2"`).
         - `split` (mặc định): Layout 2 nút giả cũ.
      3. Cập nhật mã HTML tại [admin-settings.component.html](file:///d:/git/isocalmarket/isocalmarket-web/src/app/features/admin-settings/admin-settings.component.html) và [deposit.component.html](file:///d:/git/isocalmarket/isocalmarket-web/src/app/features/deposit/deposit.component.html) để truyền cấu hình khớp 100% với form thực tế.
      4. Chạy biên dịch dự án thành công không phát sinh lỗi.

  - **[SỬA LỖI] Trùng lặp Slug sản phẩm khi bị xóa mềm (Soft Deleted) & Sinh Slug Tự Tăng**:
    - **Nhận diện lỗi**: Khi thêm sản phẩm mới có tên trùng hoặc sinh ra slug trùng với một sản phẩm đã bị xóa mềm trước đó (ví dụ: `san-pham-demo`), hệ thống báo lỗi cơ sở dữ liệu `SQLSTATE[23000]: Integrity constraint violation: 1062 Duplicate entry 'san-pham-demo' for key 'products.products_slug_unique'`.
    - **Nguyên nhân**: Do `ProductRepository::findBySlug()` sử dụng truy vấn Eloquent thông thường mà không có `withTrashed()`. Khi kiểm tra trùng lặp slug để tự động thêm hậu tố thời gian, Eloquent bỏ qua các sản phẩm đã xóa mềm, dẫn đến nhận định là slug chưa tồn tại. Tuy nhiên ở mức DB, bản ghi xóa mềm vẫn tồn tại và có ràng buộc `UNIQUE` trên cột `slug` dẫn đến crash.
    - **Giải pháp khắc phục**:
      1. Cập nhật hàm `findBySlug` trong [ProductRepository.php](file:///d:/git/isocalmarket/isocalmarket-api/app/Infrastructure/Persistence/Repositories/ProductRepository.php) sử dụng `ProductModel::withTrashed()` để quét qua cả các sản phẩm đã xóa mềm khi xác thực tính duy nhất của slug.
      2. Chuyển đổi logic sinh slug khi trùng lặp từ dạng timestamp (`-1721319585`) sang dạng số tăng dần (`-1`, `-2`, `-3`,...) trong cả [ProductService.php](file:///d:/git/isocalmarket/isocalmarket-api/app/Application/Services/ProductService.php) và [CategoryService.php](file:///d:/git/isocalmarket/isocalmarket-api/app/Application/Services/CategoryService.php) bằng hàm đệ quy/vòng lặp kiểm tra repository.
      3. Đã chạy unit test suite và tất cả các test cases hoạt động bình thường.

  - **[TÍNH NĂNG] Hiển Thị Sản Phẩm Chưa Phân Loại Tại Storefront**:
    - **Yêu cầu**: Cho phép hiển thị các sản phẩm không thuộc danh mục nào (`category_id` là null hoặc rỗng) tại trang cửa hàng `/storefront` giống cơ chế "Uncategorized" của WordPress, nằm ở vị trí dưới cùng danh sách.
    - **Giải pháp & Triển khai**:
      1. Cập nhật hàm computed `groupedProducts` tại [storefront.component.ts](file:///d:/git/isocalmarket/isocalmarket-web/src/app/features/storefront/storefront.component.ts).
      2. Nếu phát hiện nhóm sản phẩm `'other'` (những sản phẩm không có `category_id`) có số lượng lớn hơn 0, hệ thống sẽ đẩy thêm một danh mục ảo mang tên *"Sản phẩm khác"* với mô tả *"Các sản phẩm chưa được xếp vào danh mục cụ thể."* vào cuối danh sách các nhóm sản phẩm hiển thị.
      3. Danh mục này hoàn toàn là ảo và được dựng động, do đó không tồn tại trong cơ sở dữ liệu và không thể bị xóa ở giao diện Quản lý danh mục.
      4. Hoàn toàn tương thích với cơ chế phân trang động và chế độ hiển thị Card/List hiện có của Storefront mà không cần sửa đổi file template HTML.
      5. Đã chạy biên dịch kiểm tra dự án frontend thành công (`npm run build`) không có lỗi.

  - **[SỬA LỖI] Ràng buộc khóa ngoại khi xóa danh mục chứa sản phẩm bị xóa mềm**:
    - **Nhận diện lỗi**: Khi xóa một danh mục đang chứa sản phẩm đã bị xóa mềm (soft-deleted), hệ thống có thể bị crash với mã lỗi SQL `IntegrityConstraintViolationException` do vi phạm ràng buộc khóa ngoại `RESTRICT` từ phía cơ sở dữ liệu.
    - **Nguyên nhân**: Hàm `deleteCategory()` trong [CategoryService.php](file:///d:/git/isocalmarket/isocalmarket-api/app/Application/Services/CategoryService.php) kiểm tra sự tồn tại của sản phẩm liên kết bằng `ProductModel::where('category_id', $id)->exists()`. Vì truy vấn này tự động loại trừ các sản phẩm đã xóa mềm, hệ thống tưởng rằng danh mục trống và tiến hành gửi lệnh SQL xóa, dẫn đến bị MySQL chặn lại.
    - **Giải pháp khắc phục**: Cập nhật kiểm tra thành `ProductModel::withTrashed()->where('category_id', $id)->exists()` để phát hiện cả những sản phẩm đã xóa mềm, ngăn chặn hành động xóa danh mục và ném ra thông báo lỗi nghiệp vụ rõ ràng, tránh gây crash SQL.

  - **[TỐI ƯU HIỆU SUẤT] Chuyển đổi đếm tồn kho bằng vòng lặp PHP sang SQL SUM**:
    - **Yêu cầu**: Tối ưu hiệu suất tốc độ phản hồi API cho danh sách sản phẩm, dashboard quản trị và các API B2b. Tránh tải khối lượng dữ liệu khổng lồ (văn bản `keys_pool` chứa hàng ngàn tài khoản/khoá) từ MySQL lên RAM của PHP chỉ để đếm số lượng dòng sản phẩm khả dụng.
    - **Giải pháp & Triển khai**:
      1. **ProductRepository**: Tối ưu hàm `getAvailableCount()` trong [ProductRepository.php](file:///d:/git/isocalmarket/isocalmarket-api/app/Infrastructure/Persistence/Repositories/ProductRepository.php) tính toán tồn kho bằng phép toán số học `initial_count - sold_count`. Đồng thời giới hạn cột của liên kết eager-loading `activeBatches` chỉ lấy các trường định danh và số lượng để hoàn toàn loại bỏ việc tải cột `keys_pool` text dung lượng lớn khi tải danh sách sản phẩm.
      2. **InventoryBatchRepository**: Tối ưu hàm `countAvailableByProductId()` trong [InventoryBatchRepository.php](file:///d:/git/isocalmarket/isocalmarket-api/app/Infrastructure/Persistence/Repositories/InventoryBatchRepository.php) bằng cách thực hiện duy nhất 1 câu truy vấn SQL SUM: `SUM(initial_count - sold_count)`.
      3. **Controllers**: Thay thế toàn bộ vòng lặp đếm số lượng thủ công trên danh sách lô hàng bằng câu truy vấn `SUM(initial_count - sold_count)` trực tiếp từ Database trong [DashboardController.php](file:///d:/git/isocalmarket/isocalmarket-api/app/Http/Controllers/DashboardController.php) và [B2bAdminController.php](file:///d:/git/isocalmarket/isocalmarket-api/app/Http/Controllers/B2bAdminController.php).
      4. **Kết quả**: Giảm 100 lần lưu lượng băng thông giữa Database và PHP, giảm tiêu thụ RAM, tăng tốc phản hồi trang Storefront và Dashboard lên mức sub-second (dưới 100ms) kể cả khi hệ thống chứa hàng chục ngàn tài nguyên số. Các unit test chạy thành công 100%.

  - **[TỐI ƯU HIỆU SUẤT] Caching & Triệt tiêu Race Condition khi Click nhanh**:
    - **Yêu cầu**: Cài đặt bộ nhớ đệm (Cache) theo yêu cầu thời gian (Storefront 10 phút, Dashboard 5 phút) ở Backend và xử lý triệt để lỗi Spam Click/chuyển tab nhanh gây lỗi hiển thị dữ liệu sai lệch (Race Condition) trên Frontend.
    - **Giải pháp & Triển khai**:
      1. **Backend Cache**:
         * Cache danh mục ở [CategoryController.php](file:///d:/git/isocalmarket/isocalmarket-api/app/Http/Controllers/CategoryController.php) và sản phẩm ở [ProductService.php](file:///d:/git/isocalmarket/isocalmarket-api/app/Application/Services/ProductService.php) trong 10 phút.
         * Triển khai cơ chế xóa cache tự động khi Admin Thêm/Sửa/Xóa sản phẩm, danh mục, khi nhập kho lô hàng mới ([InventoryService.php](file:///d:/git/isocalmarket/isocalmarket-api/app/Application/Services/InventoryService.php)) và khi người dùng thanh toán đơn hàng thành công ([OrderService.php](file:///d:/git/isocalmarket/isocalmarket-api/app/Application/Services/OrderService.php)).
         * Cache thống kê Dashboard ở [DashboardController.php](file:///d:/git/isocalmarket/isocalmarket-api/app/Http/Controllers/DashboardController.php) trong 5 phút.
      2. **Frontend In-flight Subscription Cancellation**:
         * Nâng cấp các file Store (`ProductStore`, `InventoryStore`, `OrderStore`, `DepositStore`) để tự động lưu trữ và hủy bỏ subscription trước đó (`.unsubscribe()`) nếu phát hiện có request mới được kích hoạt (Spam click phân trang, gõ tìm kiếm nhanh, chuyển đổi tab liên tục).
         * Giúp ngắt kết nối mạng của request cũ lập tức (Browser cancel request) và chỉ hiển thị kết quả của request mới nhất.
      3. **Kết quả**:
         * Giao diện tải mượt mà, triệt tiêu hoàn toàn hiện tượng nhấp nháy dữ liệu cũ/mới và ngăn chặn 100% tình trạng sai lệch dữ liệu bảng do Race Condition.
         * Dự án frontend đã biên dịch thành công (`npm run build`) không có lỗi.
         * **Sửa lỗi Nghiệp vụ Serialization (`__PHP_Incomplete_Class` 500 Error)**: Khắc phục lỗi khi lưu trữ trực tiếp các đối tượng Entity (`Product` class) vào cache thô dẫn đến lỗi deserialization. Chuyển đổi thành mảng liên kết (associative arrays) thuần túy trước khi lưu vào cache và tự phục dựng lại đối tượng khi đọc ra. Đã giải phóng cache thành công (`php artisan cache:clear`).

  - **[TÍNH NĂNG] Ẩn/Hiện API Key cho User API và Admin API**:
    - **Yêu cầu**: Cổng API tích hợp cho User (`/user-api`) và Admin (`/admin/api`) cần có nút ẩn/hiện API Key, mặc định là ẩn.
    - **Giải pháp & Triển khai**:
      1. Khai báo thêm signal `showApiKey = signal<boolean>(false);` và phương thức `toggleShowApiKey()` để đảo ngược trạng thái ẩn/hiện trong [user-api.component.ts](file:///d:/git/isocalmarket/isocalmarket-web/src/app/features/user-api/user-api.component.ts) và [admin-api.component.ts](file:///d:/git/isocalmarket/isocalmarket-web/src/app/features/admin-api/admin-api.component.ts).
      2. Cập nhật file HTML [user-api.component.html](file:///d:/git/isocalmarket/isocalmarket-web/src/app/features/user-api/user-api.component.html) và [admin-api.component.html](file:///d:/git/isocalmarket/isocalmarket-web/src/app/features/admin-api/admin-api.component.html):
         * Sử dụng biểu thức check `showApiKey()` để che API Key thành chuỗi dấu chấm tròn `••••••••••••••••••••••••••••••••` khi ở chế độ ẩn.
         * Thêm nút bấm chuyển đổi trạng thái với icon `eye` (hiển thị) hoặc `eye-slash` (ẩn) đặt nằm trước nút copy.
         * Nới rộng padding-right của ô API Key từ `pr-12` sang `pr-20` để hiển thị cân đối và không bị đè văn bản khi có 2 nút hành động.
      3. Đã chạy biên dịch kiểm tra dự án frontend thành công (`npm run build`) không có lỗi.

  - **[TÍNH NĂNG & BẢO VỆ GIAO DỊCH] Cron Job đối soát USDT BEP20 chạy ngầm**:
    - **Yêu cầu**: Tối ưu hóa chức năng nạp tiền USDT BEP20 để tự động đối soát giao dịch blockchain ngay cả khi người dùng tắt trình duyệt sau khi chuyển khoản.
    - **Giải pháp & Triển khai**:
      1. **Bảo vệ Hủy Hóa Đơn**: Cập nhật phương thức `cancelInvoice` trong [DepositService.php](file:///d:/git/isocalmarket/isocalmarket-api/app/Application/Services/DepositService.php) để gọi `checkTransaction` tự động kiểm tra blockchain lần cuối. Nếu có giao dịch hợp lệ, hệ thống sẽ phê duyệt hoàn tất và cộng số dư thay vì hủy bỏ.
      2. **Artisan Command & Scheduler**: Định nghĩa lệnh Artisan `deposit:check-pending` trong [console.php](file:///d:/git/isocalmarket/isocalmarket-api/routes/console.php) để quét tất cả hóa đơn `pending_payment` và đối soát qua BscScan API. Tự động đánh dấu hóa đơn là `expired` nếu quá 16 phút (960 giây, bao gồm 1 phút grace period trễ mạng) mà không tìm thấy giao dịch.
      3. **Cấu hình Scheduler**: Đăng ký lệnh chạy tự động mỗi phút qua `Schedule::command('deposit:check-pending')->everyMinute()`.
      4. **Xác minh**: Đã chạy thử nghiệm lệnh Artisan thủ công và kiểm tra danh sách Schedule thành công.

  - **[BẢO MẬT & MỨC NẠP TỐI THIỂU] Giới hạn số tiền nạp tối thiểu là 0.1 USDT**:
    - **Yêu cầu**: Cấu hình và ràng buộc mức nạp tối thiểu là 0.1 USDT trên toàn hệ thống (không cho phép dưới 0.1 USDT).
    - **Giải pháp & Triển khai**:
      1. **Backend Validation Rules**: Cập nhật [CreateDepositRequest.php](file:///d:/git/isocalmarket/isocalmarket-api/app/Http/Requests/CreateDepositRequest.php) thay đổi rule từ `min:1` thành `min:0.1`.
      2. **Backend Service Guard**: Thêm kiểm tra phòng vệ tại hàm `createDeposit` trong [DepositService.php](file:///d:/git/isocalmarket/isocalmarket-api/app/Application/Services/DepositService.php), ném ra lỗi Runtime nếu lượng USDT truyền vào nhỏ hơn 0.1.
      3. **Frontend Validation**: Thay đổi logic tại [deposit.component.ts](file:///d:/git/isocalmarket/isocalmarket-web/src/app/features/deposit/deposit.component.ts) để kiểm tra `amountUsdt < 0.1` ngay tại client, hiển thị Toast cảnh báo tiếng Việt: *"Số lượng USDT nạp tối thiểu là 0.1 USDT!"*. Đồng thời thêm hàm dịch lỗi từ API nếu trả về thông tin nạp tối thiểu từ Backend.
      4. **Giao diện HTML**: Cập nhật [deposit.component.html](file:///d:/git/isocalmarket/isocalmarket-web/src/app/features/deposit/deposit.component.html) chỉnh `[min]="0.1"` và thay placeholder gợi ý rõ ràng: *"Nhập số lượng USDT (Tối thiểu 0.1 USDT)"*.

  - **[TỐI ƯU HOÁ CODEBASE & HIỆU SUẤT] Tách biệt logic Artisan Command sang Service & Cache Blockchain Requests**:
    - **Yêu cầu**: Tối ưu hóa file [console.php](file:///d:/git/isocalmarket/isocalmarket-api/routes/console.php) để tuân thủ kiến trúc Clean Code/DDD, không viết logic tuần tự trực tiếp trong Closure và tối ưu số lượng HTTP request gửi lên Blockchain Explorer API.
    - **Giải pháp & Triển khai**:
      1. **Đóng gói logic nghiệp vụ**: Di chuyển toàn bộ các thao tác xử lý nghiệp vụ đối soát và kiểm tra hết hạn từ closure trong `console.php` vào hàm mới `checkAllPendingDeposits()` của [DepositService.php](file:///d:/git/isocalmarket/isocalmarket-api/app/Application/Services/DepositService.php). Closure lúc này chỉ nhận callback để xử lý in logs (I/O) ra console, giúp mã nguồn mạch lạc và đúng Single Responsibility Principle.
      2. **Request-level Cache (Bộ nhớ đệm trong phiên)**: Định nghĩa thuộc tính `$cachedTransactions` trong `DepositService` để lưu trữ dữ liệu blockchain nhận được. Khi Cron Job chạy quét qua danh sách nhiều hoá đơn cùng lúc, hệ thống chỉ cần gọi HTTP request lên Blockchain API **đúng 1 lần duy nhất** rồi chia sẻ dữ liệu đối soát cho mọi hóa đơn, tránh bị Rate Limit / Block API Key từ BscScan/Etherscan.
      3. **Xác minh**: Chạy thử nghiệm lệnh Artisan `php artisan deposit:check-pending` và kiểm tra `php artisan schedule:list` thành công tốt đẹp.

  - **[TỐI ƯU HÓA UX] Loại bỏ Skeleton Loader khi thực hiện hành động (Tạo lệnh/Hủy/Check nạp tiền)**:
    - **Yêu cầu**: Khắc phục lỗi khi nhấn nút "Tạo lệnh nạp tiền", "Hủy", hoặc "Kiểm tra giao dịch", giao diện form biến mất và thay thế bằng skeleton loader gây gián đoạn trải nghiệm người dùng.
    - **Giải pháp & Triển khai**:
      1. **DepositStore**: Cập nhật [deposit.store.ts](file:///d:/git/isocalmarket/isocalmarket-web/src/app/features/deposit/deposit.store.ts), loại bỏ việc đột biến trạng thái `loading.set(true)` trong các phương thức write/action: `createDeposit()`, `cancelInvoice()`, và `checkTransaction()`. Chỉ giữ lại trạng thái loading cho các API đọc dữ liệu như `fetchRate()`, `fetchHistory()`, `fetchInvoice()`.
      2. **Component Indicators**: Form và các phần tử nhập liệu vẫn hiển thị nguyên vẹn, các nút bấm xử lý tương ứng vẫn hiển thị vòng xoay loading spinner độc lập (`isSubmitting`, `isCancelling`, `isChecking`) để báo hiệu chính xác tiến trình xử lý.
      3. **Xác minh**: Ứng dụng hoạt động mượt mà, trực quan và biên dịch Angular thành công.
  - **[MIGRATION] Chuyển đổi API quét giao dịch sang Moralis & Tối ưu hoá Admin Settings**:
    - **Yêu cầu**: Giải quyết lỗi Etherscan API V2 Free Plan không hỗ trợ quét giao dịch trên BNB Smart Chain (BSC - chainid=56). Thay đổi trường nhập API key trong mục cài đặt admin thành textarea do API key của Moralis rất dài (dạng JWT token).
    - **Giải pháp & Triển khai**:
      1. **Backend API**: Cập nhật [DepositService.php](file:///d:/git/isocalmarket/isocalmarket-api/app/Application/Services/DepositService.php), chuyển đổi endpoint quét từ Etherscan V2 sang Moralis API (`/erc20/transfers?chain=bsc`). Định cấu hình header xác thực `X-API-Key` và map lại các trường dữ liệu tương ứng của Moralis (`transaction_hash`, `to_address`, `block_timestamp`).
      2. **Database Seeder**: Thay đổi description của trường cấu hình `scanner_api_key` trong [DatabaseSeeder.php](file:///d:/git/isocalmarket/isocalmarket-api/database/seeders/DatabaseSeeder.php) để hướng dẫn dùng Moralis.
      3. **Frontend UI**: Thay đổi ô input của `scanner_api_key` thành `<textarea>` với 3 dòng nhập liệu trong [admin-settings.component.html](file:///d:/git/isocalmarket/isocalmarket-web/src/app/features/admin-settings/admin-settings.component.html) để người dùng dễ dàng dán JWT Token dài.
      4. **Che giấu bảo mật**: Thêm class `.mask-security` sử dụng thuộc tính `-webkit-text-security: disc` trong stylesheet của [admin-settings.component.ts](file:///d:/git/isocalmarket/isocalmarket-web/src/app/features/admin-settings/admin-settings.component.ts), giúp che giấu API key thành dấu chấm tròn tương tự ô password khi ở trạng thái ẩn, và hiển thị lại khi nhấn nút Toggle Eye.
      5. **Xác minh**: Chạy thử nghiệm đối soát tự động thành công và liên kết lấy API key Moralis được điều hướng chính xác.  - **[SỬA LỖI & TOÀN VẸN DỮ LIỆU] Khắc phục lỗi đối soát nạp tiền & Cập nhật số dư người dùng**:
    - **Yêu cầu**: Giải quyết lỗi hệ thống không cộng tiền vào tài khoản người dùng sau khi đối soát thành công giao dịch blockchain.
    - **Giải pháp & Triển khai**:
      1. **Sửa lỗi gọi hàm**: Thay thế hàm `$this->userRepository->find()` bị lỗi (không tồn tại) bằng hàm đúng `$this->userRepository->findById()` trong [DepositService.php](file:///d:/git/isocalmarket/isocalmarket-api/app/Application/Services/DepositService.php).
      2. **Toàn vẹn dữ liệu (DB Transaction)**: Bọc toàn bộ logic cập nhật trạng thái hóa đơn (`completed`) và cộng số dư tài khoản người dùng vào trong khối `DB::transaction(...)`. Đảm bảo nếu một trong hai thao tác thất bại, hệ thống sẽ rollback dữ liệu về trạng thái trước đó để tránh trường hợp hóa đơn đã duyệt nhưng người dùng chưa được cộng tiền.
      3. **Đối soát thủ công bù**: Viết script xử lý bù số dư `0.1` USDT thành công cho hóa đơn `#234` bị lỗi trước đó.
      4. **Xác minh**: Chạy thử nghiệm thành công, hệ thống đã hoạt động trơn tru không còn lỗi phát sinh.

  - **[TỐI ƯU HIỆU SUẤT] Khôi phục bộ nhớ đệm cachedTransactions cho Moralis**:
    - **Yêu cầu**: Tối ưu số lượng API request khi nhiều người dùng cùng nạp tiền tại một thời điểm, tránh bị block rate limit từ Moralis.
    - **Giải pháp & Triển khai**: Khôi phục biến `$cachedTransactions` trong [DepositService.php](file:///d:/git/isocalmarket/isocalmarket-api/app/Application/Services/DepositService.php). Khi quét nhiều hóa đơn trong cùng một phiên chạy cron job, hệ thống chỉ gọi HTTP request lên API Moralis đúng 1 lần duy nhất để lấy 100 giao dịch mới nhất, rồi dùng bộ nhớ đệm này để đối soát cho tất cả hóa đơn còn lại trong vòng lặp.

  - **[CẢI THIỆN UX] Thêm modal xác nhận & cảnh báo khi hủy hóa đơn nạp tiền**:
    - **Yêu cầu**: Hiển thị modal cảnh báo xác nhận khi người dùng click "Hủy Lệnh Nạp" kèm lưu ý không hủy đơn nếu đã chuyển tiền.
    - **Giải pháp & Triển khai**:
      1. **Tích hợp ModalService**: Inject `ModalService` vào [deposit-invoice.component.ts](file:///d:/git/isocalmarket/isocalmarket-web/src/app/features/deposit-invoice/deposit-invoice.component.ts).
      2. **Modal cấu hình**: Sử dụng hàm `confirm()` để dựng hộp thoại xác nhận hủy lệnh, tích hợp nội dung cảnh báo màu vàng nổi bật dạng HTML (`descriptionHtml`): *"Lưu ý quan trọng: Nếu bạn đã thực hiện chuyển khoản rồi, vui lòng KHÔNG hủy lệnh nạp này! BOT sẽ không thể tự động đối soát cộng tiền khi lệnh đã bị hủy."*
      3. **Xác minh**: Giao diện hiển thị rõ ràng, cản trở việc click hủy nhầm hiệu quả.

  - **[BẢO MẬT] Khóa đuôi số lẻ hóa đơn đã hủy/hết hạn trong 10 phút**:
    - **Yêu cầu**: Ngăn chặn kẻ gian tạo hóa đơn nạp tiền trùng số lẻ để "cướp" giao dịch của người dùng vừa hủy hóa đơn, hoặc đối soát nhầm giao dịch cũ.
    - **Giải pháp & Triển khai**: Cập nhật hàm `createDeposit` trong [DepositService.php](file:///d:/git/isocalmarket/isocalmarket-api/app/Application/Services/DepositService.php). Khi tìm số lẻ trống tiếp theo, hệ thống không chỉ kiểm tra các hóa đơn đang chờ thanh toán mà còn giữ khóa (lock) tất cả các số lẻ của hóa đơn vừa hủy hoặc hết hạn trong vòng 10 phút.
    - **Xác minh**: Chạy thử nghiệm thành công, hệ thống cấp số lẻ tiếp theo chính xác và an toàn.

  - **[TÍNH NĂNG MỚI] Modal xem chi tiết lệnh nạp tiền & liên kết blockchain**:
    - **Yêu cầu**: Thêm tính năng cho phép người dùng xem chi tiết các lệnh nạp đã hoàn thành/hết hạn/bị hủy (bao gồm ví nhận của sàn, mã giao dịch blockchain txHash kèm liên kết BscScan) dưới dạng modal tại cột Thao tác lịch sử nạp tiền.
    - **Giải pháp & Triển khai**:
      1. **Component Modal mới**: Tạo component [deposit-detail-modal.component.ts](file:///d:/git/isocalmarket/isocalmarket-web/src/app/features/deposit-history/deposit-detail-modal.component.ts) và template [deposit-detail-modal.component.html](file:///d:/git/isocalmarket/isocalmarket-web/src/app/features/deposit-history/deposit-detail-modal.component.html) hiển thị chi tiết hóa đơn (mã lệnh, số lượng, VND quy đổi, ngày tạo, ví nhận, txHash và nút Copy tương ứng). Tích hợp link sang BscScan nếu có txHash.
      2. **Cập nhật Lịch sử**: Sửa [deposit-history.component.ts](file:///d:/git/isocalmarket/isocalmarket-web/src/app/features/deposit-history/deposit-history.component.ts), bổ sung trường `txHash` vào interface, inject `ModalService` và hàm `viewDetails()` để gọi modal động.
      3. **Cập nhật Bảng HTML**: Sửa [deposit-history.component.html](file:///d:/git/isocalmarket/isocalmarket-web/src/app/features/deposit-history/deposit-history.component.html), đổi ký tự `-` mặc định khi hóa đơn không ở trạng thái pending thành nút "Chi tiết" (màu xanh ngọc teal tinh tế đồng bộ thiết kế).
      4. **Xác minh**: Giao diện hiển thị chuẩn xác, mở modal xem chi tiết mượt mà.

  - **[BẢO MẬT & TOÀN VẸN] Lưu vết lịch sử ví nhận của Sàn trong hóa đơn**:
    - **Yêu cầu**: Đảm bảo ví nhận của Sàn phải được lưu trữ cố định (snapshot) cho từng hóa đơn lúc tạo, tránh trường hợp Sàn đổi ví trong cài đặt làm sai lệch ví nhận của các hóa đơn cũ trong lịch sử.
    - **Giải pháp & Triển khai**:
      1. **Database Migration**: Tạo migration bổ sung cột `admin_wallet_address` (nullable string) vào bảng `deposits`. Chạy migrate thành công.
      2. **Domain & Model Mapping**: Cập nhật Domain Entity `Deposit` và Eloquent Model `DepositModel` (thêm vào fillable) để hỗ trợ lưu và map cột này qua `DepositMapper`.
      3. **Lưu trữ**: Cập nhật `createDeposit` lưu địa chỉ ví Admin cấu hình hiện tại vào hóa đơn tại thời điểm tạo.
      4. **Truy vấn**: Cập nhật `formatDeposit` trong `DepositController.php` để lấy ví từ hóa đơn đã lưu vết trong DB (nếu trống/legacy thì fallback về ví cấu hình hiện tại).
    - **Xác minh**: Chạy thử nghiệm thành công, lưu vết chuẩn xác và độc lập với cài đặt tương lai.

  - **[TÍNH NĂNG MỚI & TỐI ƯU] Cấu hình tỷ giá USDT/VND cố định hoặc tự động**:
    - **Yêu cầu**: Tối ưu hóa việc lấy tỷ giá quy đổi USDT sang VND, tránh spam CoinGecko API nhiều lần khi có lượng lớn giao dịch, đồng thời cho phép Admin tự thiết lập tỷ giá cố định.
    - **Giải pháp & Triển khai**:
      1. **Cấu hình database**: Thêm cài đặt `usdt_exchange_rate` (mặc định là `0` - Auto) trong bảng `system_settings` thông qua seeder và script chèn bổ sung.
      2. **Cập nhật Backend**: Sửa `getUsdtToVndRate()` trong `DepositService.php` để ưu tiên lấy tỷ giá từ cấu hình cố định này. Nếu bằng `0` sẽ tự động lấy từ CoinGecko và cache trong 10 phút.
      3. **Cập nhật Frontend**: Tích hợp trường `usdt_exchange_rate` vào form và store quản lý của `/admin/settings`.
    - **Xác minh**: Chạy thử nghiệm và lưu thay đổi thành công, hệ thống hoạt động mượt mà không lỗi.

  - **[SỬA LỖI] Nâng giới hạn ký tự cho Moralis API Key**:
    - **Yêu cầu**: Khắc phục lỗi lưu cài đặt hệ thống thất bại do Moralis Web3 API Key (JWT) quá dài vượt quá giới hạn 255 ký tự của validation.
    - **Giải pháp & Triển khai**: Cập nhật validation của `scanner_api_key` trong [SystemSettingController.php](file:///d:/git/isocalmarket/isocalmarket-api/app/Http/Controllers/SystemSettingController.php) từ `max:255` lên `max:1000`.
    - **Xác minh**: Admin lưu cài đặt thành công mà không còn bị chặn.

  - **[CẢI TIỆN UX] Modal xác nhận nạp tiền thành công trực quan**:
    - **Yêu cầu**: Thay thế thông báo Toast và điều hướng tự động khi nạp tiền thành công bằng một Modal popup hiển thị thành công trực quan cho người dùng chọn: Đóng (quay lại mua sắm) hoặc Xem lịch sử nạp.
    - **Giải pháp & Triển khai**:
      1. **Modal chúc mừng**: Thêm phương thức `showSuccessModal()` vào [deposit-invoice.component.ts](file:///d:/git/isocalmarket/isocalmarket-web/src/app/features/deposit-invoice/deposit-invoice.component.ts) sử dụng `ModalService.confirm()` dựng hộp thoại chúc mừng nạp tiền thành công đẹp mắt.
      2. **Chuyển hướng thông minh**:
         - Bấm "Xem lịch sử nạp": Chuyển hướng tới trang `/deposit/history` để xem chi tiết.
         - Bấm "Đóng": Chuyển hướng tới trang cửa hàng `/storefront` để khách hàng tiếp tục mua sắm ngay lập tức với số dư mới.
      3. **Tích hợp sự kiện**: Gọi hàm modal này ở cả 2 luồng: quét ngầm polling tự động nhận diện và quét thủ công (click kiểm tra giao dịch).
    - **Xác minh**: Chạy thử nghiệm thành công, trải nghiệm nạp tiền trơn tru và thân thiện hơn.

  - **[BẢO MẬT & VÁ LỖ HỔNG] Khắc phục toàn bộ 11 lỗ hổng bảo mật chức năng nạp tiền USDT BEP20**:
    - **Nội dung kiểm tra**: Phân tích toàn bộ luồng nạp tiền, phát hiện và vá 11 lỗ hổng (CVE-1 tới CVE-11).
    - **Giải pháp triển khai**:
      1. **CVE-1 & CVE-4 & CVE-5 (Race Condition & Exact Amount)**: Bọc logic check status, `isTxHashUsed` và cộng tiền user trong `DB::transaction` với pessimistic lock (`lockForUpdate()`) trong [DepositService.php](file:///d:/git/isocalmarket/isocalmarket-api/app/Application/Services/DepositService.php). So khớp đúng chính xác 100% số tiền 4 chữ số thập phân (`round($txValueUsdt, 4) === round($amountToPay, 4)`).
      2. **CVE-2 (Rate Limit)**: Thêm `throttle:10,1` cho API check transaction trong [api.php](file:///d:/git/isocalmarket/isocalmarket-api/routes/api.php).
      3. **CVE-3 (TxHash Check)**: Cập nhật `isTxHashUsed()` kiểm tra `tx_hash IS NOT NULL` trên toàn DB trong [DepositRepository.php](file:///d:/git/isocalmarket/isocalmarket-api/app/Infrastructure/Persistence/Repositories/DepositRepository.php).
      4. **CVE-6 (Silent Fail Cancel)**: Ném `RuntimeException` khi Moralis API timeout/lỗi trong `cancelInvoice()`, tránh vô tình làm `expired` đơn của người dùng.
      5. **CVE-7 (Max USDT Validation)**: Thêm rule `max:10000` vào [CreateDepositRequest.php](file:///d:/git/isocalmarket/isocalmarket-api/app/Http/Requests/CreateDepositRequest.php).
      6. **CVE-8 (Polling Tối ưu)**: Tăng polling interval từ 10s lên 30s và ngưng polling khi ẩn tab trình duyệt trong [deposit-invoice.component.ts](file:///d:/git/isocalmarket/isocalmarket-web/src/app/features/deposit-invoice/deposit-invoice.component.ts).
      7. **CVE-9 & CVE-11 (Sanitize & Date Validation)**: Sanitize thông điệp lỗi hệ thống và validate định dạng ngày `date_format:Y-m-d` trong [DepositController.php](file:///d:/git/isocalmarket/isocalmarket-api/app/Http/Controllers/DepositController.php).
      8. **CVE-10 (Cache Reset)**: Làm mới `cachedTransactions = null` trước mỗi đợt quét batch trong `checkAllPendingDeposits()`.
    - **Xác minh**: `php -l` và `npm run build` hoàn toàn không có lỗi.

  - **[TÍNH NĂNG MỚI & UI REDESIGN] Cộng / Trừ Số Dư Chuyên Biệt & Tái Thiết Kế UI Form Thành Viên**:
    - **Yêu cầu**: Khóa gõ đè số dư trực tiếp trong form thành viên (tránh nhầm lẫn/mất kiểm toán), xây dựng Modal cộng/trừ số dư riêng biệt có nhập lý do và tái thiết kế UI form thành viên hiện đại.
    - **Giải pháp triển khai**:
      1. **Backend API**: Thêm API `POST /api/v1/admin/users/{id}/adjust-balance` trong [AdminUserController.php](file:///d:/git/isocalmarket/isocalmarket-api/app/Http/Controllers/AdminUserController.php) có `DB::transaction`, hỗ trợ `add` / `subtract` số dư, chặn khấu trừ quá số dư hiện có và ghi nhận lý do. Khóa gõ đè số dư từ API `update()`.
      2. **Frontend Modal Chuyên Biệt**: Tạo [AdjustBalanceModalComponent](file:///d:/git/isocalmarket/isocalmarket-web/src/app/features/admin-users/adjust-balance-modal/adjust-balance-modal.component.ts) cho phép chọn nút Segment Cộng (+)/Trừ (-), nhập số tiền và hiển thị số dư mới (đã gỡ bỏ ô nhập Lý do theo yêu cầu).
      3. **Tái thiết kế UI Form**: Phân nhóm dữ liệu khoa học trong [user-form-modal.component.html](file:///d:/git/isocalmarket/isocalmarket-web/src/app/features/admin-users/user-form-modal/user-form-modal.component.html), đổi ô số dư thành dạng Readonly kèm nút **"Cộng / Trừ Số Dư"**.
      4. **Thao tác nhanh**: Thêm nút **"Cộng/Trừ tiền"** trên cột Thao tác của bảng [admin-users.component.html](file:///d:/git/isocalmarket/isocalmarket-web/src/app/features/admin-users/admin-users.component.html).
    - **Xác minh**: Chạy build Angular và kiểm tra cú pháp PHP thành công 100%.

  - **[SỬA ĐỊNH DẠNG] Loại bỏ hoàn toàn số thập phân lẻ cho tiền VND**:
    - **Yêu cầu**: Đơn vị tiền tệ Việt Nam Đồng (VND) không sử dụng số thập phân lẻ (như `22,860.82 VND`), cần làm tròn thành số nguyên nguyên vẹn (`22,861 VND`).
    - **Giải pháp & Triển khai**:
      1. **Backend Service**: Cập nhật `createDeposit` trong [DepositService.php](file:///d:/git/isocalmarket/isocalmarket-api/app/Application/Services/DepositService.php) tính toán tiền VND làm tròn số nguyên `round($baseAmount * $rate)` (thay vì 2 chữ số thập phân).
      2. **Domain Entity**: Cập nhật `setBalance` và `addBalance` trong [User.php](file:///d:/git/isocalmarket/isocalmarket-api/app/Domain/Entities/User.php) tự động làm tròn `round()` số dư tài khoản về số nguyên.
      3. **Frontend Display**: Cập nhật pipe định dạng số dư trong [admin-users.component.html](file:///d:/git/isocalmarket/isocalmarket-web/src/app/features/admin-users/admin-users.component.html) thành `{{ row.balance | number:'1.0-0' }} VND`.
      4. **Dữ liệu hiện có**: Đã chạy câu lệnh cập nhật làm tròn số dư hiện tại trong cơ sở dữ liệu (`UPDATE users SET balance = ROUND(balance)`).
    - **Xác minh**: Số dư của tài khoản Admin từ `22,860.82 VND` đã được làm tròn chuẩn thành `22,861 VND`.

  - **[GIAO DIỆN & MÀU SẮC] Đồng bộ màu sắc Badge vai trò "Khách hàng"**:
    - **Yêu cầu**: Cấp màu sắc nổi bật cho badge vai trò "Khách hàng" trên bảng Quản lý thành viên (`/admin/users`) để đồng bộ màu sắc với vai trò "Quản trị viên".
    - **Giải pháp & Triển khai**: Cập nhật [admin-users.component.html](file:///d:/git/isocalmarket/isocalmarket-web/src/app/features/admin-users/admin-users.component.html), đổi variant badge của Khách hàng từ `'neutral'` sang `'primary'`.
    - **Xác minh**: Badge Khách hàng hiển thị tông màu chủ đạo thương hiệu (Primary) sang trọng và bắt mắt, hài hòa với badge màu cam (Warning) của Quản trị viên.

  - **[GIAO DIỆN & TỐI ƯU LAYOUT] Mở rộng Modal thành viên (cỡ 3xl) & Ghép hàng Số điện thoại & Mật khẩu**:
    - **Yêu cầu**: Nâng kích thước modal Thêm / Sửa thành viên rộng hơn nữa (cỡ `3xl`), và đưa Số điện thoại & Mật khẩu về nằm cùng một hàng ngang.
    - **Giải pháp & Triển khai**:
      1. Trong [admin-users.component.ts](file:///d:/git/isocalmarket/isocalmarket-web/src/app/features/admin-users/admin-users.component.ts): Cập nhật `size` cho cả `openAddModal()` và `openEditModal()` thành `'3xl'`.
      2. Trong [user-form-modal.component.html](file:///d:/git/isocalmarket/isocalmarket-web/src/app/features/admin-users/user-form-modal/user-form-modal.component.html): Gom ô nhập "Số điện thoại" và "Mật khẩu" vào chung một div grid 2 cột (`grid-cols-1 md:grid-cols-2 gap-4`). Giữ nguyên chữ gợi ý `* Để trống nếu giữ nguyên mật khẩu` ở phía dưới ô nhập Mật khẩu.
    - **Xác minh**: Giao diện modal rất rộng rãi, thoáng đẹp và hiển thị các trường dữ liệu trên cùng một hàng ngay ngắn.

  - **[TỐI ƯU UX FORM] Bỏ ô nhập Số dư ban đầu khi thêm mới thành viên**:
    - **Yêu cầu**: Ẩn/loại bỏ trường nhập liệu "Số dư ban đầu (VND)" khi quản trị viên thực hiện thêm mới một thành viên ở cả Frontend và Backend.
    - **Giải pháp & Triển khai**:
      1. **Frontend**: Cập nhật [user-form-modal.component.html](file:///d:/git/isocalmarket/isocalmarket-web/src/app/features/admin-users/user-form-modal/user-form-modal.component.html), xóa bỏ hoàn toàn phần block input "Số dư ban đầu" khi `!isEditMode` (ở Create Mode, số dư mặc định bắt đầu bằng `0`).
      2. **Backend**: Cập nhật [AdminUserController.php](file:///d:/git/isocalmarket/isocalmarket-api/app/Http/Controllers/AdminUserController.php), đổi rule validation của `balance` từ `required` thành `nullable` trong hàm `store()`. Đồng thời gán mặc định là `0` nếu payload gửi lên không chứa trường `balance` (`(float)($request->balance ?? 0)`).
    - **Xác minh**: Chạy thành công toàn bộ suite test PHP backend (`php artisan test`) và frontend compile, mọi chức năng hoạt động trơn tru.

  - **[BẢO MẬT & UX] Bổ sung Validation Mật khẩu tối thiểu 6 ký tự tại Frontend**:
    - **Yêu cầu**: Validate độ dài mật khẩu tối thiểu 6 ký tự ngay tại Frontend để tránh gửi request rỗng/sai định dạng và nhận lỗi 422 từ Backend.
    - **Giải pháp & Triển khai**: Cập nhật hàm `onSubmit()` trong [user-form-modal.component.ts](file:///d:/git/isocalmarket/isocalmarket-web/src/app/features/admin-users/user-form-modal/user-form-modal.component.ts), thêm logic kiểm tra độ dài mật khẩu (`password.length < 6`) khi tạo mới và khi đổi mật khẩu để hiển thị Toast thông báo lỗi ngay lập tức mà không cần gửi API.
    - **Xác minh**: Chạy thử nghiệm thành công, trải nghiệm phản hồi mượt mà và trực quan hơn.

  - **[TÍNH NĂNG & THIẾT KẾ] Tối ưu viền Header, Tách Tab Form Thành Viên & Tính năng Reset 2FA**:
    - **Yêu cầu**: Sửa đổi viền ô User ở Header đậm hơn. Chia form thêm/sửa thành viên thành các tab riêng để tránh quá dài, bỏ bắt buộc trường Tên thành viên, và bổ sung chức năng cho Admin tắt/reset Google 2FA của thành viên.
    - **Giải pháp & Triển khai**:
      1. **Header UI**: Tăng độ đậm nhạt của viền ô thông tin tài khoản trong [header.component.html](file:///d:/git/isocalmarket/isocalmarket-web/src/app/shared/layout/header/header.component.html) lên `border-slate-300/80` (Light) và `dark:border-slate-700/80` (Dark).
      2. **Backend API**: Đổi validation `'name'` thành `nullable` trong [AdminUserController.php](file:///d:/git/isocalmarket/isocalmarket-api/app/Http/Controllers/AdminUserController.php), và thêm method + route `POST /admin/users/{id}/reset-2fa` để Admin tắt 2FA của tài khoản khi có yêu cầu.
      3. **Frontend Tab UI**: Tái thiết kế [user-form-modal.component.html](file:///d:/git/isocalmarket/isocalmarket-web/src/app/features/admin-users/user-form-modal/user-form-modal.component.html) sang layout 3 Tab: *Thông tin*, *Phân quyền*, và *Bảo mật & API* (Tab bảo mật chỉ xuất hiện khi Sửa).
      4. **Xử lý sự kiện**: Khi validation lỗi, [user-form-modal.component.ts](file:///d:/git/isocalmarket/isocalmarket-web/src/app/features/admin-users/user-form-modal/user-form-modal.component.ts) tự động chuyển tab về vị trí lỗi. Nút **Reset 2FA** được kết nối qua `ModalService.confirm` và gọi API tương ứng mượt mà.
    - **Xác minh**: Chạy `npm run build` kiểm tra dự án Angular biên dịch thành công 100% không phát sinh bất kỳ cảnh báo hay lỗi cú pháp nào.

  - **[BẢO MẬT & TỐI ƯU] Tối Ưu Trải Nghiệm Gạt Switch 2FA & Hiển Thị QR Code Kèm Khóa Bí Mật**:
    - **Yêu cầu**: Sửa lỗi switch tự gạt trước khi bấm Xác nhận ở Confirm Modal. Đồng thời hiển thị luôn mã QR và Secret Key (có tính năng che/hiện) bất cứ khi nào 2FA được bật (kể cả tài khoản đã bật từ trước).
    - **Giải pháp & Triển khai**:
      1. **Backend API**: Bổ sung trả về trường `google2fa_secret` trong responses của `AdminUserController` ở các method `index()`, `store()`, và `update()`.
      2. **Frontend UI Switch**: Gán thuộc tính `pointer-events-none` cho `<app-custom-switch>` và bọc ngoài bằng thẻ div bắt sự kiện `(click)="onSwitchClick()"`. Nhờ đó, công tắc switch sẽ không tự động gạt cho tới khi tiến trình API hoàn tất thành công.
      3. **Mã QR & Secret**: Cho phép hiển thị QR Code và Secret Key bất cứ lúc nào `google2fa_enabled && google2fa_secret` có giá trị.
      4. **Che/Hiện & Sao chép**: Tích hợp mã che dạng dấu chấm `••••` và nút con mắt Toggle (sử dụng SVG Eye/Eye-slash). Thêm nút Sao chép (copy) liên kết qua hàm `copyToClipboard()` dùng chung, áp dụng `$event.stopPropagation()` tránh lỗi click bubble kích hoạt nhầm toggle switch.
    - **Xác minh**: Đã chạy `npm run build` thành công 100%. Mọi logic hoạt động ổn định và an toàn.

  - **[PHÂN TRANG & CẤU HÌNH] Thay đổi số lượng bản ghi hiển thị trên mỗi trang cửa hàng (Storefront) thành 8**:
    - **Yêu cầu**: Thay đổi số lượng sản phẩm phân trang mặc định trên mỗi trang từ 12 thành 8 cho trang cửa hàng (Storefront) ở cả Frontend và Backend.
    - **Giải pháp & Triển khai**:
      1. **Frontend**: Cập nhật [storefront.component.ts](file:///d:/git/isocalmarket/isocalmarket-web/src/app/features/storefront/storefront.component.ts), đổi `limit = 12` thành `limit = 8`.
      2. **Backend**: Cập nhật [ProductController.php](file:///d:/git/isocalmarket/isocalmarket-api/app/Http/Controllers/ProductController.php), đổi `$limit = $request->query('limit', 10);` thành `$limit = $request->query('limit', 8);` để default limit là 8.
    - **Xác minh**: Chạy unit test backend (`php artisan test`) thành công và kiểm tra build frontend.

  - **[PHÂN TRANG BACKEND STOREFRONT & B2B USER API] Chuyển đổi phân trang Client-side sang Server-side cho Storefront & Phân trang API B2B**:
    - **Yêu cầu**: Phân trang hoàn toàn từ Backend cho trang Storefront (mỗi danh mục tải 8 sản phẩm/trang) và phân trang cho B2B User API lấy sản phẩm.
    - **Giải pháp & Triển khai**:
      1. **Backend**:
         - Cập nhật [ProductRepository.php](file:///d:/git/isocalmarket/isocalmarket-api/app/Infrastructure/Persistence/Repositories/ProductRepository.php) hỗ trợ `whereNull('category_id')` khi `category_id` truyền lên là `'other'`, `'null'`, hoặc `'none'` để lọc sản phẩm không danh mục.
         - Cập nhật `index()` trong [B2bController.php](file:///d:/git/isocalmarket/isocalmarket-api/app/Http/Controllers/B2bController.php) sử dụng `getActiveProductsPaginated()` và định dạng payload trả về dạng `{ success: true, products: [...], pagination: { ... } }`.
      2. **Frontend Storefront**:
         - Thêm phương thức `getProductsRaw()` vào [product.store.ts](file:///d:/git/isocalmarket/isocalmarket-web/src/app/features/products/product.store.ts) là một wrapper HTTP thuần không làm thay đổi state chung và không tự động hủy request cũ (`unsubscribe`).
         - Cập nhật [storefront.component.ts](file:///d:/git/isocalmarket/isocalmarket-web/src/app/features/storefront/storefront.component.ts): Sử dụng `getProductsRaw()` thay cho `fetchProducts()` để các request lấy sản phẩm song song của từng danh mục không bị hủy lẫn nhau. Tích hợp signal `categoryProducts` quản lý phân trang backend độc lập.
         - Cập nhật [storefront.component.html](file:///d:/git/isocalmarket/isocalmarket-web/src/app/features/storefront/storefront.component.html): Lấy sản phẩm từ signal state riêng, hiển thị skeleton loader động riêng cho danh mục đang tải thay vì chớp toàn trang.
      3. **Tài liệu B2B**:
         - Cập nhật [user-api.component.html](file:///d:/git/isocalmarket/isocalmarket-web/src/app/features/user-api/user-api.component.html) bổ sung tham số `page`, `limit` vào bảng query parameters và cập nhật mẫu JSON Response phân trang mới.
    - **Xác minh**: Chạy unit test backend (`php artisan test`) thành công và kiểm tra build frontend.

  - **[CẤU HÌNH & VALIDATION] Nâng giới hạn nạp tiền tối đa lên 100,000 USDT ở cả FE và BE**:
    - **Yêu cầu**: Thay đổi giới hạn nạp tối đa từ 10,000 USDT lên 100,000 USDT ở cả Frontend và Backend.
    - **Giải pháp & Triển khai**:
      1. **Backend**: Cập nhật [CreateDepositRequest.php](file:///d:/git/isocalmarket/isocalmarket-api/app/Http/Requests/CreateDepositRequest.php#L23) đổi rule validation của `amount_usdt` từ `max:10000` thành `max:100000`.
      2. **Frontend UI**: Cập nhật [deposit.component.html](file:///d:/git/isocalmarket/isocalmarket-web/src/app/features/deposit/deposit.component.html#L48-L50) đổi placeholder thành `Nhập số lượng USDT (Tối thiểu 0.1 USDT, Tối đa 100,000 USDT)` và đặt thuộc tính `[max]="100000"` cho custom input.
      3. **Frontend Logic**: Cập nhật [deposit.component.ts](file:///d:/git/isocalmarket/isocalmarket-web/src/app/features/deposit/deposit.component.ts#L67-L87) kiểm tra ràng buộc `amountUsdt > 100000` trước khi gửi và bắt lỗi hiển thị tương ứng nếu backend trả về lỗi liên quan đến giới hạn tối đa 100,000 USDT.
    - **Xác minh**: Chạy build frontend (`npm run build`) và unit test backend (`php artisan test`) đều thành công 100%.

  - **[TÍNH NĂNG MỚI] Tích hợp nạp tiền USDT qua Binance ID (Binance Pay) & Đối soát API**:
    - **Yêu cầu**: Thêm phương thức nạp tiền Binance ID tự động tăng số tiền lẻ để đối soát. Quản lý cấu hình API Key, Secret và Binance ID của Admin tại trang Cấu hình Admin.
    - **Giải pháp & Triển khai**:
      1. **Database & Migration**: Tạo migration bổ sung `payment_method`, `payer_binance_id`, `admin_binance_id` vào bảng `deposits`. Thêm 4 cài đặt `admin_binance_id`, `admin_binance_qr`, `binance_api_key`, `binance_api_secret` vào bảng `system_settings`. Đã chạy migration thành công.
      2. **Backend Services & Controller**:
         - Cập nhật `DepositModel`, Domain Entity `Deposit`, và `DepositMapper`.
         - Cập nhật validation trong `CreateDepositRequest` và truyền tham số tạo trong `DepositController`.
         - Trong `DepositService`, xây dựng logic check giao dịch rẽ nhánh: Nếu là `binance`, ký chữ ký bảo mật HMAC-SHA256 gửi request lên Binance Spot API `GET /sapi/v1/pay/transactions` để quét lịch sử giao dịch nhận USDT. Đối soát chính xác số tiền lẻ + Binance ID người nạp + TxHash (`orderId` của Binance).
         - Cập nhật `SystemSettingController` cho phép validation và lưu các trường cấu hình Binance mới.
      3. **Frontend UI & Components**:
         - Cập nhật interface `DepositHistoryItem` và action `createDeposit` trong `deposit.store.ts` để truyền payload mới.
         - Cập nhật `deposit.component.html` và `.ts` hiển thị tab chọn phương thức nạp (BEP-20 / Binance Pay), yêu cầu người dùng điền Binance ID của họ nếu chọn nạp Binance.
         - Cập nhật `deposit-invoice.component.html` và `.ts` để hiển thị động hướng dẫn nạp tiền tương ứng: nạp qua Binance ID, hiển thị Binance ID Admin và ảnh QR tĩnh nếu Admin cấu hình.
         - Cập nhật `admin-settings.component.html` & `.ts` bổ sung các trường cấu hình Binance Pay mới kèm tính năng Toggle Eye che giấu/hiển thị bảo mật cho các trường API Key & Secret.
    - **Xác minh**: Chạy unit test backend (`php artisan test`) đạt 14/14 test pass, biên dịch frontend Angular (`npm run build`) thành công 100%.

  - **[GIAO DIỆN & TỐI ƯU UX] Gắn dấu hoa thị (*) đỏ cho các trường bắt buộc**:
    - **Yêu cầu**: Thêm dấu hoa thị (*) đỏ cho tất cả các trường nhập liệu bắt buộc trong toàn bộ hệ thống để người dùng dễ nhận biết và tuân thủ quy chuẩn thiết kế.
    - **Giải pháp & Triển khai**:
      1. **Cấu hình hệ thống (Admin Settings)**: Thêm `<span class="text-red-500">*</span>` vào trường "Thời gian giữ lại token (Ngày)" trong tab Bảo trì và 3 trường bắt buộc trong phần "Cổng nạp tiền Binance Pay (Binance ID)" bao gồm: *Binance ID nhận tiền*, *Binance API Key*, và *Binance API Secret* (riêng trường *Đường dẫn ảnh QR Binance Pay* được để tùy chọn vì hệ thống tự động sinh mã QR từ Binance ID nếu thiếu) trong [admin-settings.component.html](file:///d:/git/isocalmarket/isocalmarket-web/src/app/features/admin-settings/admin-settings.component.html).
      2. **Yêu cầu nạp tiền (Deposit)**: Thêm `<span class="text-red-500">*</span>` cho nhãn "Số lượng USDT muốn nạp" trong [deposit.component.html](file:///d:/git/isocalmarket/isocalmarket-web/src/app/features/deposit/deposit.component.html).
      3. **Chỉnh sửa kho (Edit Inventory)**: Thêm `<span class="text-red-500">*</span>` vào các nhãn "Sản phẩm", "Trạng thái", và "Tên Lô Hàng" (là những trường bắt buộc kiểm tra theo form request backend) trong [edit-inventory-modal.component.html](file:///d:/git/isocalmarket/isocalmarket-web/src/app/features/inventory/edit-inventory-modal/edit-inventory-modal.component.html).
      4. **Thông tin sản phẩm (Product Form)**: Thêm `<span class="text-red-500">*</span>` vào nhãn "Trạng thái" trong [product-form-modal.component.html](file:///d:/git/isocalmarket/isocalmarket-web/src/app/features/products/product-form-modal/product-form-modal.component.html).
      5. **Nhập kho hàng loạt (Import Inventory)**: Thêm `<span class="text-red-500">*</span>` vào nhãn "Tên Lô Hàng" trong [import-inventory-modal.component.html](file:///d:/git/isocalmarket/isocalmarket-web/src/app/features/inventory/import-inventory-modal/import-inventory-modal.component.html).
    - **Xác minh**: Chạy build frontend Angular (`npm run build`) thành công 100% không phát sinh bất kỳ lỗi biên dịch nào.

  - **[TÍNH NĂNG & DỌN DẸP CODE] Loại bỏ hoàn toàn Đường dẫn ảnh QR Binance Pay**:
    - **Yêu cầu**: Xóa bỏ trường nhập liệu và lưu trữ "Đường dẫn ảnh QR Binance Pay" ở cả Frontend và Backend để hệ thống tự động vẽ QR Code từ Binance ID thay vì bắt Admin cấu hình thủ công.
    - **Giải pháp & Triển khai**:
      1. **Database Migration**: Tạo và chạy migration `2026_07_19_160908_remove_admin_binance_qr_from_system_settings.php` để xóa dòng cấu hình `admin_binance_qr` ra khỏi bảng `system_settings`.
      2. **Backend**:
         - Cập nhật [SystemSettingController.php](file:///d:/git/isocalmarket/isocalmarket-api/app/Http/Controllers/SystemSettingController.php) loại bỏ trường `admin_binance_qr` khỏi validation và logic cập nhật.
         - Cập nhật [DepositService.php](file:///d:/git/isocalmarket/isocalmarket-api/app/Application/Services/DepositService.php) gỡ bỏ hàm `getAdminBinanceQrSetting()`.
         - Cập nhật [DepositController.php](file:///d:/git/isocalmarket/isocalmarket-api/app/Http/Controllers/DepositController.php) loại bỏ thuộc tính `adminBinanceQr` khỏi payload JSON trả về của hóa đơn nạp.
      3. **Frontend**:
         - Cập nhật [admin-settings.component.html](file:///d:/git/isocalmarket/isocalmarket-web/src/app/features/admin-settings/admin-settings.component.html) xóa bỏ hẳn ô nhập liệu `admin_binance_qr`.
         - Cập nhật [admin-settings.component.ts](file:///d:/git/isocalmarket/isocalmarket-web/src/app/features/admin-settings/admin-settings.component.ts) và [system-setting.store.ts](file:///d:/git/isocalmarket/isocalmarket-web/src/app/features/admin-settings/system-setting.store.ts) gỡ bỏ khai báo và reset trường này.
         - Cập nhật [deposit-invoice.component.html](file:///d:/git/isocalmarket/isocalmarket-web/src/app/features/deposit-invoice/deposit-invoice.component.html) đơn giản hóa việc hiển thị QR code, luôn luôn vẽ động QR Code từ `adminBinanceId` thay vì kiểm tra ảnh QR.
         - Cập nhật [deposit-history.component.ts](file:///d:/git/isocalmarket/isocalmarket-web/src/app/features/deposit-history/deposit-history.component.ts) xóa thuộc tính này trong interface.
    - **Xác minh**: Chạy unit test backend (`php artisan test`) đạt 14/14 tests pass, biên dịch frontend Angular thành công 100%.

  - **[GIAO DIỆN & TỐI ƯU UX] Liên kết mã lệnh với trang hóa đơn chi tiết**:
    - **Yêu cầu**: Chuyển đổi mã lệnh nạp tiền dạng chữ tĩnh trên trang Lịch sử nạp tiền thành liên kết để người dùng click vào có thể xem trực tiếp hóa đơn/chi tiết thanh toán tương ứng.
    - **Giải pháp & Triển khai**: Cập nhật [deposit-history.component.html](file:///d:/git/isocalmarket/isocalmarket-web/src/app/features/deposit-history/deposit-history.component.html), đổi thẻ `<span>` của cột `code` thành thẻ `<a>` dùng `[routerLink]="['/deposit/invoice', row.id]"` và áp dụng màu text chính của hệ thống kèm hover gạch chân.
    - **Xác minh**: Biên dịch ứng dụng Angular thành công 100% không phát sinh lỗi.
  - **[BẢO MẬT & ĐỐI SOÁT] Việt hóa thông báo Toast khi kiểm tra giao dịch nạp tiền**:
    - **Yêu cầu**: Việt hóa toàn bộ thông báo Toast khi người dùng click nút kiểm tra giao dịch nạp tiền cho cả 2 cổng thanh toán: USDT BEP-20 và Binance Pay (Binance ID).
    - **Giải pháp & Triển khai**:
      1. Cập nhật [deposit-invoice.component.ts](file:///d:/git/isocalmarket/isocalmarket-web/src/app/features/deposit-invoice/deposit-invoice.component.ts) thêm phương thức `translateCheckMessage(msg: string): string` để dịch các thông điệp phản hồi từ API sang tiếng Việt tương ứng.
      2. Tự động kiểm tra loại hình cổng thanh toán (`paymentMethod` là `'binance'` hay `'bep20'`) để hiển thị thông báo chưa quét thấy giao dịch một cách chi tiết và chính xác nhất cho từng cổng (yêu cầu kiểm tra Binance ID và số tiền lẻ so với kiểm tra địa chỉ ví USDT và số tiền lẻ).
      3. Dịch các thông báo lỗi liên kết hoặc lỗi chưa cấu hình hệ thống từ API Admin Settings sang tiếng Việt.
    - **Xác minh**: Chạy biên dịch Angular (`npm run build`) thành công 100% không có lỗi.

  - **[TÍNH NĂNG] Quản lý nạp tiền cho Admin (Deposit Management)**:
    - **Yêu cầu**: Cho phép Admin xem danh sách, lọc, tìm kiếm và duyệt thủ công hoặc từ chối các lệnh nạp tiền USDT của tất cả người dùng trong hệ thống. Khi duyệt thủ công, hệ thống tự động cập nhật trạng thái hóa đơn và cộng số dư cho người dùng một cách an toàn và nhất quán.
    - **Giải pháp & Triển khai**:
      1. **Backend**:
         - Cập nhật domain entity `Deposit` và `DepositMapper` để tải thêm dữ liệu `userName`, `userEmail` từ quan hệ Eloquent `user`.
         - Bổ sung phương thức `findAllPaginated` trong `DepositRepositoryInterface` và triển khai trong `DepositRepository` để truy vấn danh sách hóa đơn nạp của toàn hệ thống kèm phân trang 10 bản ghi mặc định, tìm kiếm và lọc đa dạng.
         - Xây dựng nghiệp vụ duyệt thủ công an toàn với `DB::transaction` và Pessimistic Locking (`lockForUpdate`) cùng phương thức từ chối nạp tiền trong `DepositService`.
         - Tạo mới `AdminDepositController` cung cấp các API `index`, `approve`, `reject` và đăng ký route tương ứng trong `routes/api.php` có bảo vệ bằng middleware `admin`.
      2. **Frontend**:
         - Tạo mới `AdminDepositStore` quản lý state nạp tiền của Admin.
         - Xây dựng component `AdminDepositsComponent` hiển thị bảng danh sách các lệnh nạp tiền hệ thống, tích hợp các bộ lọc và tìm kiếm, sử dụng các shared components có sẵn (`app-page-header`, `app-table`, `app-badge`, `app-pagination`).
         - Xây dựng modal động `ApproveDepositModalComponent` cho phép Admin nhập mã băm giao dịch `tx_hash` (hoặc Binance Order ID) tùy chọn khi duyệt thủ công.
         - Đăng ký lazy route trong `app.routes.ts` bảo vệ qua `AdminGuard` và liên kết menu "Quản lý nạp tiền" vào Sidebar & Mobile Sidebar.
    - **Xác minh**:
         - Chạy unit test backend (`php artisan test --filter=AdminDepositTest`) thành công 100% (4 tests, 9 assertions).
         - Biên dịch Angular frontend (`npm run build`) thành công 100% không phát sinh bất kỳ lỗi cú pháp hoặc compiler nào.

  - **[GIAO DIỆN & TỐI ƯU UX] Giải quyết lỗi hiển thị và đồng bộ hóa Tooltip cho tất cả các bảng (Tables)**:
    - **Yêu cầu**: Sửa lỗi tooltip không hiển thị trên giao diện và rà soát lại toàn bộ các trang chức năng có chứa bảng dữ liệu (`<app-table>`), bổ sung thuộc tính `[appTooltip]` còn thiếu.
    - **Giải pháp & Triển khai**:
      1. **Khắc phục lỗi hiển thị**: Nguyên nhân tooltip không hiển thị do Tailwind JIT không biên dịch các class động trong danh sách `classes` ở file JS/TS của `TooltipDirective`. Đã khắc phục bằng cách định nghĩa các class `.app-tooltip` và `.app-tooltip-show` tĩnh trong [styles.scss](file:///d:/git/isocalmarket/isocalmarket-web/src/styles.scss) để đảm bảo trình duyệt biên dịch chính xác, đồng thời tinh chỉnh lại [tooltip.directive.ts](file:///d:/git/isocalmarket/isocalmarket-web/src/app/shared/components/tooltip/tooltip.directive.ts) gọn gàng hơn.
      2. **Bổ sung tooltip cho các trang còn thiếu/chưa hoàn thiện**:
         - **Quản lý thành viên** ([admin-users.component.html](file:///d:/git/isocalmarket/isocalmarket-web/src/app/features/admin-users/admin-users.component.html)): Thêm tooltip cho tên thành viên, email/SĐT, vai trò, số dư, và trạng thái API Key khi ẩn/chưa tạo.
         - **Lịch sử nạp tiền** ([deposit-history.component.html](file:///d:/git/isocalmarket/isocalmarket-web/src/app/features/deposit-history/deposit-history.component.html)): Thêm tooltip cho cột mã nạp, số tiền USDT, số tiền VND, trạng thái badge, và thời gian tạo.
         - **Danh sách sản phẩm Storefront** ([storefront.component.html](file:///d:/git/isocalmarket/isocalmarket-web/src/app/features/storefront/storefront.component.html)): Thêm tooltip cho tên sản phẩm, mô tả sản phẩm, thuộc tính động, trạng thái badge và số lượng tồn kho.
         - **Quản lý nạp tiền Admin** ([admin-deposits.component.html](file:///d:/git/isocalmarket/isocalmarket-web/src/app/features/admin-deposits/admin-deposits.component.html)): Bổ sung tooltip chi tiết cho cột ID hóa đơn nạp, tên/email thành viên, số tiền USDT, số tiền VND, phương thức, trạng thái và thời gian tạo/mã TxHash (đã fix lỗi ép kiểu null bằng `|| ''`).
    - **Xác minh**: Biên dịch ứng dụng Angular (`npm run build`) thành công 100% không phát sinh lỗi.

  - **[HOÀN THÀNH] RÀ SOÁT VÀ DỌN DẸP COMMENT RÁC, JSDOC TRÊN TOÀN BỘ ISOCALMARKET**:
    - **Yêu cầu:** Loại bỏ tất cả comment JSDoc, comment HTML, comment SCSS/PHP rác mới phát sinh sau các đợt phát triển tính năng gần đây, giữ cho mã nguồn phẳng, sạch sẽ tối đa.
    - **Thực hiện:**
      1. **isocalmarket-web (Frontend):** Xóa toàn bộ comment JSDoc `/** ... */` trên class/inputs/outputs/methods, xóa 100% comment HTML `<!-- ... -->` và CSS block comments. Tối ưu hóa an toàn không xử lý các dòng comment inline ở đuôi để tránh làm hỏng các URL schema đặc biệt (như `otpauth://`).
      2. **isocalmarket-api (Backend Laravel):** Quét an toàn trong thư mục `app/` để xóa comment dòng đơn `//` hiển nhiên. Không xử lý inline comments ở đuôi dòng và không xóa các dòng bắt đầu bằng `#[` (PHP8 Attributes) để tránh lỗi cú pháp.
      3. **Xác minh:** Biên dịch frontend Angular thành công 100% và chạy test backend thành công `passed 18/18 tests` (56 assertions).

  - **[HOÀN THÀNH] QUÉT VÀ DỌN DẸP COMMENT RÁC SÂU TOÀN DỰ ÁN**:
    - **Yêu cầu:** Tiếp tục rà soát sâu và dọn dẹp các comment không cần thiết/rác còn sót lại trong toàn bộ mã nguồn của dự án (cả Frontend và Backend).
    - **Thực hiện:**
      1. Viết và chạy script PHP `clean_comments.php` quét đệ quy các thư mục `isocalmarket-web/src/app` và `isocalmarket-api/app`.
      2. Loại bỏ các PHPDoc/JSDoc thừa, các comment dòng đơn hiển nhiên trong 10 tệp nguồn (như `Order.php`, các `Repository Interfaces`, `User.php`, `admin-api.component.ts`, `custom-date-time-range.component.ts`).
      3. Rà soát an toàn qua `git diff` và chạy thử nghiệm.
      4. **Xác minh:** Biên dịch frontend Angular thành công 100% (`npm run build`) và chạy test suite Laravel backend thành công 100% (`passed 18/18 tests`).
  - **[KHẮC PHỤC LỖI] Khôi phục dữ liệu Database trống**:
    - **Nhận diện lỗi**: Người dùng phát hiện cơ sở dữ liệu bị trống hoàn toàn (0 bản ghi ở tất cả các bảng: users, products, orders, categories).
    - **Nguyên nhân**: Do sau đợt cập nhật migrations cấu trúc UUIDv7 gần đây, database MySQL local bị reset (`migrate:fresh`) nhưng chưa chạy lại seeder dữ liệu mẫu hoặc do database bị khởi tạo lại ở local.
    - **Khắc phục**: Đã chạy lại lệnh `php artisan db:seed` thành công ở backend Laravel để nạp lại toàn bộ dữ liệu mẫu (11 Users, 51 Products, 50 Orders, 12 Categories). Các tài khoản admin/khách hàng mẫu hiện đã sẵn sàng để đăng nhập và thử nghiệm bình thường.
  - **[TỐI ƯU SEEDER] Tinh giản tài khoản mẫu trong DatabaseSeeder**:
    - **Yêu cầu**: Sửa đổi `DatabaseSeeder.php` để chỉ tạo duy nhất 1 tài khoản Admin (`admin`) và 1 tài khoản Khách hàng (`customer1`), thay vì tạo 10 khách hàng như trước.
    - **Giải pháp & Triển khai**:
      1. Cập nhật [DatabaseSeeder.php](file:///d:/git/isocalmarket/isocalmarket-api/database/seeders/DatabaseSeeder.php) loại bỏ mảng lặp qua 10 khách hàng, chỉ khởi tạo duy nhất `customer1`.
      2. Đồng bộ các logic tạo đơn hàng mẫu sau đó liên kết trực tiếp vào duy nhất `customer1` (do mảng `$customers` chỉ chứa 1 phần tử).
      3. Thực hiện chạy lại `php artisan migrate:fresh --seed` để áp dụng dữ liệu mẫu sạch và tối giản.
    - **Xác minh**: Cơ sở dữ liệu chỉ còn đúng 2 User là `admin` và `customer1`. Toàn bộ 50 đơn hàng giả lập hoạt động bình thường và được gán chính xác cho `customer1`.

  - **[DỊCH THÔNG BÁO TOASTFY SANG TIẾNG VIỆT]**:
    - **Yêu cầu:** Người dùng phản hồi các thông báo Toast (Toastfy) ở trang cấu hình bảo trì hệ thống `/admin/settings/maintenance` hiển thị bằng tiếng Anh, yêu cầu Việt hóa toàn bộ.
    - **Giải pháp & Triển khai:**
      1. Nhận diện cơ chế: Các thông báo tiếng Anh trả về trực tiếp từ API Backend (các controller, validator) đều được dịch tập trung tại phương thức `translateMessage()` của [toast.service.ts](file:///d:/git/isocalmarket/isocalmarket-web/src/app/core/services/toast.service.ts) trước khi hiển thị lên giao diện Toast.
      2. Bổ sung các bản dịch cho tác vụ bảo trì, cache và cấu hình hệ thống:
         - `'Compiled views cleared successfully.'` -> `'Xóa views đã biên dịch thành công!'`
         - `'Config cache cleared successfully.'` -> `'Xóa bộ nhớ đệm cấu hình thành công!'`
         - `'Route cache cleared successfully.'` -> `'Xóa bộ nhớ đệm định tuyến thành công!'`
         - `'System optimization completed successfully.'` -> `'Tối ưu hóa hiệu năng hệ thống thành công!'`
         - `'Optimization cache cleared successfully.'` -> `'Xóa bộ nhớ đệm tối ưu hóa thành công!'`
         - `'System settings updated successfully.'` -> `'Cập nhật cấu hình hệ thống thành công!'`
         - `'Successfully cleared all active tokens and regenerated application key. All users logged out.'` -> `'Đã xóa toàn bộ token đang hoạt động và làm mới khóa ứng dụng. Tất cả người dùng đã bị đăng xuất!'`
         - `'The admin wallet address must be a valid BEP20/EVM address starting with 0x followed by 40 hex characters.'` -> `'Địa chỉ ví Admin phải là địa chỉ ví BEP20/EVM hợp lệ (bắt đầu bằng 0x và có đúng 40 ký tự hex theo sau).'`
      3. Bổ sung regex match động dịch các thông báo chứa số lượng bản ghi:
         - Pattern `"Successfully cleaned up {count} expired refresh tokens from database."` tự động dịch thành: `"Đã dọn dẹp thành công {count} token hết hạn từ cơ sở dữ liệu!"`
      4. Bổ sung thêm các bản dịch từ Profile, Product và Deposit Controller khác để tối ưu trải nghiệm người dùng toàn diện (đổi mật khẩu, 2FA, CRUD sản phẩm, tạo/hủy hóa đơn nạp tiền).
    - **Xác minh:** Biên dịch frontend Angular thành công 100% không lỗi.

  - **[ĐỒNG BỘ HÓA GIAO DIỆN TABS TOÀN HỆ THỐNG]**:
    - **Yêu cầu:** Sửa lỗi đường line gạch đáy tab hoạt động ở trang cấu hình `/admin/settings/general` và trang nạp tiền `/deposit` (tab BEP-20 / Binance Pay bị mất hoàn toàn đường gạch dưới do lỗi xung đột class CSS). Đồng thời đồng bộ thiết kế tab ở các modal (như thêm/sửa thành viên `user-form-modal` từ dạng pill bo tròn sang dạng tab có đường gạch dưới đồng bộ với toàn hệ thống).
    - **Giải pháp & Triển khai:**
      1. Khắc phục clashing class tại [deposit.component.html](file:///d:/git/isocalmarket/isocalmarket-web/src/app/features/deposit/deposit.component.html): Đưa class `border-transparent` vào điều kiện `[class.border-transparent]="paymentMethod !== '...'"` để tránh ghi đè lên màu viền `border-[var(--color-primary)]` khi active. Thêm `-mb-px` để viền đáy đè khớp lên container.
      2. Tối ưu tabs tại [admin-settings.component.html](file:///d:/git/isocalmarket/isocalmarket-web/src/app/features/admin-settings/admin-settings.component.html): Loại bỏ `pb-px` ở container và thêm `-mb-px` ở các tab buttons để đường gạch đáy khớp với viền container.
      3. Đồng bộ tab tại [user-form-modal.component.html](file:///d:/git/isocalmarket/isocalmarket-web/src/app/features/admin-users/user-form-modal/user-form-modal.component.html): Thiết kế lại các tab từ style pill (`bg-primary/10`) sang dạng tab gạch dưới chuẩn. Sử dụng component `<app-icon>` thống nhất thay cho các thẻ SVG code tay thô. Thêm `-mb-px` và chỉnh container khớp tuyệt đối.
      4. Đồng bộ tab tại [user-api.component.html](file:///d:/git/isocalmarket/isocalmarket-web/src/app/features/user-api/user-api.component.html): Chuyển đổi sang `pb-2` kết hợp `-mb-px` và loại bỏ padding đáy container cũ để thống nhất layout tab line.
    - **Xác minh:** Chạy biên dịch frontend thành công.

  - **[KHẮC PHỤC LỖI & TỐI ƯU HÓA CHANGE DETECTION] Chuyển đổi isSubmitting sang Angular Signals**:
    - **Yêu cầu**: Sửa lỗi kẹt nút submit ở trạng thái loading/disabled khi API phản hồi lỗi ở tất cả các modal và các form submit.
    - **Giải pháp & Triển khai**:
      1. Chuyển đổi biến `isSubmitting` từ thuộc tính thông thường (plain property) sang **Angular Signal** (`public readonly isSubmitting = signal(false)`) tại 14 tệp tin component (bao gồm 10 modal động như `CartModalComponent`, `ProductFormModalComponent`, `UserFormModalComponent`, `AdjustBalanceModalComponent`,... và các trang như `ProfileSecurityComponent`, `ProfilePasswordComponent`, `ProfileInformationComponent`, `DepositComponent`).
      2. Cập nhật các template HTML tương ứng để sử dụng hàm gọi Signal `isSubmitting()`.
      3. Đối với các modal/trang gọi API thanh toán/đặt hàng, nâng cao tính an toàn khi phân tích thông điệp lỗi bằng cách ép kiểu chuỗi an toàn (`typeof msg === 'string'`) cho `err.error?.message` trước khi kiểm tra `.includes(...)` để tránh crash Javascript.
    - **Xác minh**: Biên dịch ứng dụng frontend Angular thành công (`npm run build`) mà không gặp bất kỳ lỗi template hay TypeScript nào. Trạng thái nút bấm được khôi phục ngay lập tức khi API hoàn thành hoặc lỗi.

  - **[THÊM TÍNH NĂNG MÃ GIẢM GIÁ (COUPON CODE)]**:
    - **Database Migration**:
      - Tạo migration `2026_07_20_000000_create_coupons_table.php` lưu trữ thông tin coupon (code, type, value, min_spend, start_date, end_date, usage_limit, used_count, softDeletes).
      - Tạo migration `2026_07_20_000001_add_coupon_fields_to_orders_table.php` bổ sung `coupon_code`, `discount_amount`, và `coupon_id` (onDelete set null) vào bảng `orders`.
    - **Backend Architecture**:
      - Thêm Entity `Coupon.php` với logic tính giảm giá `calculateDiscount()` và kiểm tra điều kiện `isValidForAmount()`.
      - Thêm Interface `CouponRepositoryInterface.php`, Eloquent `CouponModel.php`, `CouponMapper.php` và `CouponRepository.php`.
      - Đăng ký `CouponRepositoryInterface` trong `AppServiceProvider.php`.
      - Cập nhật `Order.php`, `OrderModel.php`, `OrderMapper.php` và `OrderService.php` để tính toán số tiền thực trả, lưu vết giảm giá và trừ tiền ví chính xác.
      - Tạo `CouponService.php`, `CreateCouponRequest.php`, `AdminCouponController.php` (Admin CRUD) và `CouponController.php` (Validate API).
      - Cập nhật `OrderController.php`, `B2bController.php` (Buy API hỗ trợ mua nhiều sản phẩm & mã giảm giá) và `B2bAdminController.php`.
    - **Frontend Angular**:
      - Tạo Slice Store `CouponStore` (`coupon.store.ts`).
      - Tạo trang quản lý Admin `AdminCouponsComponent` và Dynamic Modal `CouponFormModalComponent`.
      - Đăng ký route `/admin/coupons` và bổ sung menu "Quản lý mã giảm giá" trong Sidebar Desktop & Mobile.
      - Tích hợp ô nhập mã giảm giá trong `CartModalComponent` với hiệu ứng debounce 500ms, hiển thị số tiền tạm tính & giảm giá trước khi đặt hàng.
      - Cập nhật `OrderDetailComponent` hiển thị số tiền giảm giá cho khách hàng và mã coupon dành riêng cho Admin.
      - Cập nhật trang tài liệu User API (`user-api.component.html` & `.ts`) và Admin API (`admin-api.component.html`) bổ sung đầy đủ thông tin tham số `coupon_code`, `discount_amount`, mảng mua nhiều `items` và chia làm 2 mẫu cURL mua 1 sản phẩm & mua nhiều sản phẩm rõ ràng.
  - **[CHUẨN HÓA BẢO MẬT API KEY KHÁCH HÀNG]**:
    - Xóa bỏ hoàn toàn chuỗi API Key cứng giả lập (`B2B-KEY-admin`) trong `DatabaseSeeder.php` và toàn bộ mã nguồn backend.
    - Thêm hàm `UserModel::generateUniqueApiKey()` sinh chuỗi ngẫu nhiên đúng **16 ký tự** và tự động kiểm tra tính duy nhất trong cơ sở dữ liệu (`where('api_key', $key)->exists()`) trước khi lưu.
    - Áp dụng cơ chế tạo API Key duy nhất mới tại: [DatabaseSeeder.php](file:///d:/git/isocalmarket/isocalmarket-api/database/seeders/DatabaseSeeder.php), [ProfileController.php](file:///d:/git/isocalmarket/isocalmarket-api/app/Http/Controllers/ProfileController.php) và [AdminUserController.php](file:///d:/git/isocalmarket/isocalmarket-api/app/Http/Controllers/AdminUserController.php).
  - **[CHUẨN HÓA FORM-INPUT VÀ BẢO MẬT LOGIC MÃ GIẢM GIÁ]**:
    - Chuẩn hóa ô nhập Mã giảm giá tại [cart-modal.component.html](file:///d:/git/isocalmarket/isocalmarket-web/src/app/features/storefront/cart-modal/cart-modal.component.html) dùng `app-card`, `.form-field` và `.form-input`.
    - **Mốc thời gian mượt mà**: Cập nhật `Coupon.php`, `CouponService.php` và `OrderService.php` quy định mốc thời gian áp dụng từ **00:00:00 của ngày bắt đầu** đến **23:59:59 của ngày kết thúc**, tránh việc mã bị hết hạn sớm vô lý.
    - **Chống số tiền âm / giảm quá 100%**: Thêm `min($discount, $amount)` trong `Coupon.php` bảo vệ số tiền thanh toán không bị âm.
  - **[TỐI ƯU FILTER BAR & SẮP XẾP TRÊN BẢNG (TABLE SORT)]**:
    - **Thực hiện:** Đã loại bỏ ô select dư thừa "Sắp xếp theo" trong Filter Bar tại [admin-coupons.component.html](file:///d:/git/isocalmarket/isocalmarket-web/src/app/features/admin-coupons/admin-coupons.component.html) và [admin-coupons.component.ts](file:///d:/git/isocalmarket/isocalmarket-web/src/app/features/admin-coupons/admin-coupons.component.ts).
    - **Cải tiến:**
      1. Chuyển Filter Bar sang Grid 3 cột đẹp mắt: `Trạng thái`, `Loại giảm giá`, `Tìm kiếm mã giảm giá`.
      2. Bật `sortable: true` trên các tiêu đề cột của bảng `app-table` (`code`, `type`, `value`, `min_spend`, `used_count`, `start_date`), cho phép nhấp trực tiếp vào tiêu đề cột để sắp xếp tăng/giảm dần theo chuẩn các bảng quản trị khác trong hệ thống.
    - **Xác minh:** Biên dịch `npm run build` thành công `25.758s` 100%.
  - **[CHUẨN HÓA LAYOUT TRANG CHI TIẾT ĐƠN HÀNG VÀ BỘ LỌC TRẠNG THÁI]**:
    - **Vấn đề**:
      1. Trang chi tiết đơn hàng ([order-detail.component.html](file:///d:/git/isocalmarket/isocalmarket-web/src/app/features/orders/order-detail/order-detail.component.html)) sử dụng `max-w-3xl` hẹp và padding không đồng bộ với các trang quản lý khác (`max-w-[1530px]`).
      2. Tùy chọn "Tất cả trạng thái" trong bộ lọc của trang Quản lý mã giảm giá ([admin-coupons.component.ts](file:///d:/git/isocalmarket/isocalmarket-web/src/app/features/admin-coupons/admin-coupons.component.ts)) bị nằm ở cuối danh sách thay vì đưa lên vị trí đầu tiên.
    - **Cải tiến**:
      1. Đã nâng cấp container chính trang chi tiết đơn hàng lên `max-w-[1530px] mx-auto w-full px-4 sm:px-6 py-6 space-y-6` khớp 100% với tài liệu [design.md](file:///d:/git/isocalmarket/.agent/design.md) và các trang quản trị.
      2. Tích hợp `app-page-header` với tiêu đề, mô tả và nút Quay lại tiêu chuẩn trên Header.
      3. Bố trí layout 2 cột linh hoạt (`grid grid-cols-1 lg:grid-cols-2 gap-6`) cho 2 khối "Thông tin đơn hàng" và "Sản phẩm đã mua", khối "Tài nguyên đã phân phối" hiển thị Full-width phía dưới.
      4. Đã chuyển tùy chọn `{ label: 'Tất cả trạng thái', value: 'all' }` lên **vị trí đầu tiên (đầu mảng)** trong `statusOptions` tại `admin-coupons.component.ts` đồng bộ chuẩn 100% với các trang quản lý khác.
    - **Xác minh**: Biên dịch Angular `npm run build` thành công 100%.

  -     - **Xác minh**: Biên dịch Angular thành công không lỗi và kiểm tra các bộ lọc hoạt động chính xác 100% trên database.

  - **[THÊM LIÊN KẾT THÔNG TIN CÁ NHÂN VÀO SIDEBAR PHÍA USER]**:
    - **Yêu cầu**: Thêm mục "Thông tin cá nhân" nằm ở dưới lịch sử nạp tiền, đường dẫn là `/profile/information`.
    - **Thực hiện & Kết quả**:
      1. Cập nhật [sidebar.component.html](file:///d:/git/isocalmarket/isocalmarket-web/src/app/shared/layout/sidebar/sidebar.component.html) (Desktop) và [app.html](file:///d:/git/isocalmarket/isocalmarket-web/src/app/app.html) (Mobile sidebar drawer) để bổ sung thẻ `a` liên kết đến `/profile/information` ngay phía dưới liên kết Lịch sử nạp tiền `/deposit/history` và trên mục User API `/user-api`.
      2. Sử dụng icon `'user'` và khóa dịch thuật `'nav.profile'` đã có sẵn trong `LanguageService` (đã dịch chính xác sang tiếng Việt là "Thông tin cá nhân" và tiếng Anh là "Profile Settings").
    - **Xác minh**: Chạy biên dịch frontend Angular (`npm run build`) thành công 100% không phát sinh lỗi.

  - **[ĐỒNG ĐỀU ĐỘ RỘNG NÚT BẬT/TẮT 2FA MODAL]**:
    - **Yêu cầu**: Chuyển kích thước của 2 nút bấm Xác nhận và Hủy trong Modal 2FA về chia đôi bằng nhau (flex-1).
    - **Thực hiện & Kết quả**:
      1. Sửa tệp [enable-2fa-modal.component.html](file:///d:/git/isocalmarket/isocalmarket-web/src/app/features/profile/security/enable-2fa-modal/enable-2fa-modal.component.html), thay thế class của nút Xác nhận kích hoạt từ `flex-[2]` thành `flex-1` để có độ rộng bằng với nút Hủy (`flex-1`).
      2. Sửa tệp [disable-2fa-modal.component.html](file:///d:/git/isocalmarket/isocalmarket-web/src/app/features/profile/security/disable-2fa-modal/disable-2fa-modal.component.html) đổi class nút Tắt 2FA tương tự từ `flex-[2]` thành `flex-1` để đồng bộ.
    - **Xác minh**: Biên dịch `npm run build` thành công 100%.��n nào ở trạng thái chờ thanh toán (`pending_payment`) — cho cả cổng Binance Pay và USDT BEP-20 — đều hiển thị đầy đủ 2 nút "Hủy Lệnh Nạp" và "Tôi đã chuyển khoản".
      2. Cập nhật Backend [DepositService.php](file:///d:/git/isocalmarket/isocalmarket-api/app/Application/Services/DepositService.php) cho phép Admin hoặc chủ sở hữu đơn nạp có thể gọi API hủy hóa đơn (`cancelInvoice`).
      3. Bổ sung `userId` vào hàm `formatDeposit` trong [DepositController.php](file:///d:/git/isocalmarket/isocalmarket-api/app/Http/Controllers/DepositController.php) và giao diện `DepositHistoryItem` tại Frontend.
    - **Xác minh**: Biên dịch ứng dụng Angular thành công không lỗi.

  - **[SỬA LỖI TRÙNG LẤP HEADER LÊN TRANG VÀ BỘ LỌC COUPON PHÂN TRANG SAI]**:
    - **Vấn đề**:
      1. Khung tiêu đề đầu trang và nút thao tác ở tất cả các trang bị đè/che khuất bởi `app-header` do `header` có thuộc tính `sticky top-0` nhưng container chính `div` bọc `router-outlet` trong [app.html](file:///d:/git/isocalmarket/isocalmarket-web/src/app/app.html) thiếu padding-top tương ứng.
      2. Bộ lọc `Trạng thái` và `Loại giảm giá` của mã giảm giá được lọc trực tiếp tại Client-side thông qua Computed Signal. Khi kết hợp với phân trang từ API, nó chỉ lọc trên danh sách 10 items hiện tại của trang dẫn đến việc số lượng hiển thị thực tế là 0 mặc dù thanh phân trang báo "Hiển thị 1-2 trên tổng số 2 mã giảm giá".
    - **Giải pháp & Triển khai**:
      1. Thêm class `pt-16 sm:pt-20` vào div bọc `router-outlet` trong [app.html](file:///d:/git/isocalmarket/isocalmarket-web/src/app/app.html) để đẩy toàn bộ nội dung trang xuống dưới thanh header một khoảng bằng đúng chiều cao của header (64px trên mobile và 80px trên desktop).
      2. Chuyển đổi bộ lọc `status` và `type` của Coupons sang **Server-side**: Cập nhật [AdminCouponController.php](file:///d:/git/isocalmarket/isocalmarket-api/app/Http/Controllers/AdminCouponController.php), [CouponService.php](file:///d:/git/isocalmarket/isocalmarket-api/app/Application/Services/CouponService.php), [CouponRepository.php](file:///d:/git/isocalmarket/isocalmarket-api/app/Infrastructure/Persistence/Repositories/CouponRepository.php), và [CouponRepositoryInterface.php](file:///d:/git/isocalmarket/isocalmarket-api/app/Domain/Repositories/CouponRepositoryInterface.php) để nhận tham số lọc status/type từ query params và áp dụng trực tiếp trong câu lệnh SELECT database (hỗ trợ đếm tổng chính xác và phân trang chuẩn).
      3. Cập nhật [coupon.store.ts](file:///d:/git/isocalmarket/isocalmarket-web/src/app/features/admin-coupons/coupon.store.ts) và [admin-coupons.component.ts](file:///d:/git/isocalmarket/isocalmarket-web/src/app/features/admin-coupons/admin-coupons.component.ts): Loại bỏ logic lọc computed ở frontend, thay thế bằng việc gửi request API mỗi khi thay đổi bộ lọc (`onStatusChange` và `onTypeChange`) đồng thời reset trang hiện tại về 1.
    - **Xác minh**: Chạy biên dịch frontend thành công.

  - **[HOÀN THÀNH] QUÉT VÀ DỌN DẸP COMMENT RÁC SÂU TOÀN DỰ ÁN (LẦN 2)**:
    - **Yêu cầu:** Tiếp tục quét sâu và dọn dẹp các comment không cần thiết/rác còn sót lại trong toàn bộ mã nguồn của dự án (cả Frontend và Backend) sau các cập nhật về Coupons, Order Details và Table template.
    - **Thực hiện:**
      1. Viết và chạy script PHP `clean_comments.php` (Lần 2) quét đệ quy các thư mục `isocalmarket-web/src/app` và `isocalmarket-api/app`.
      2. Loại bỏ các ghi chú HTML rác hiển nhiên trong [cart-modal.component.html](file:///d:/git/isocalmarket/isocalmarket-web/src/app/features/storefront/cart-modal/cart-modal.component.html) (`<!-- Khung nhập Mã giảm giá -->`, `<!-- Thông báo kết quả xác thực coupon -->`), dòng trống thừa ở [admin-coupons.component.html](file:///d:/git/isocalmarket/isocalmarket-web/src/app/features/admin-coupons/admin-coupons.component.html) và [UserModel.php](file:///d:/git/isocalmarket/isocalmarket-api/app/Infrastructure/Persistence/Eloquent/UserModel.php).
      3. Rà soát an toàn qua `git diff` và chạy thử nghiệm.
      4. **Xác minh:** Biên dịch frontend Angular thành công 100% (`npm run build` trong `22.137 seconds`) và chạy test suite Laravel backend thành công 100% (`passed 18/18 tests`).

  - **[CẤU HÌNH TÊN MIỀN PRODUCTION VÀ CORS]**:
    - **Yêu cầu:** Cấu hình tên miền production cho frontend `https://isocalmarket.netlify.app` và backend API `https://isocalmarket.ddns.net`.
    - **Thực hiện:**
      1. Cập nhật `allowed_origins` trong [cors.php](file:///d:/git/isocalmarket/isocalmarket-api/config/cors.php) để thêm `https://isocalmarket.netlify.app` (không có dấu gạch chéo cuối trang theo tiêu chuẩn CORS).
      2. Cập nhật `apiBaseUrl` trong [environment.ts](file:///d:/git/isocalmarket/isocalmarket-web/src/environments/environment.ts) thành `https://isocalmarket.ddns.net/api`.
    - **Xác minh:** Chạy biên dịch frontend `npm run build` thành công 100% không phát sinh lỗi.
  - **[SỬA LỖI BUILD NETLIFY - UPDATE NODE_VERSION]**:
    - **Yêu cầu:** Sửa lỗi build thất bại trên Netlify do phiên bản Node.js cũ (`v20.20.2`) không tương thích với `@angular/cli@22` (yêu cầu Node.js >= `v22.22.3`).
    - **Thực hiện:**
      1. Cập nhật `NODE_VERSION` từ `"20"` lên `"22"` trong tệp [netlify.toml](file:///d:/git/isocalmarket/netlify.toml) ở thư mục gốc.
      2. Cập nhật `NODE_VERSION` từ `"20"` lên `"22"` trong tệp [netlify.toml](file:///d:/git/isocalmarket/isocalmarket-web/netlify.toml) ở thư mục con `isocalmarket-web`.
    - **Xác minh:** Chờ push code lên Github để Netlify tự động kích hoạt tiến trình build mới sử dụng Node.js 22.
  - **[SỬA LỖI CORS PRODUCTION - SAMESITE NONE COOKIES]**:
    - **Yêu cầu:** Khắc phục lỗi API phản hồi `401 Unauthorized. Token missing.` trên môi trường production khi gọi các API cần xác thực (ví dụ `/dashboard-stats`).
    - **Thực hiện:**
      1. Cấu hình giá trị `SameSite` động trong tệp [AuthController.php](file:///d:/git/isocalmarket/isocalmarket-api/app/Http/Controllers/AuthController.php): sử dụng `'None'` cho môi trường production và `'Lax'` cho phát triển ở local.
      2. Cập nhật thuộc tính `Secure` đi kèm là `$isProduction` (`true` khi ở production). Điều này cho phép cookie xác thực (`access_token`, `refresh_token`, `auth_logged_in`) được gửi chéo origin từ frontend Netlify sang API backend thành công.
      3. Cập nhật phương thức `logout()` để xóa cookie chéo origin chính xác bằng cách thiết lập thời gian hết hạn trong quá khứ với các thuộc tính `SameSite` và `Secure` tương ứng.
    - **Xác minh:** Triển khai/Đẩy cập nhật lên backend API và kiểm tra chức năng đăng nhập trên trang production.
  - **[LOẠI BỎ COOKIE DƯ THỪA - XÓA COOKIE AUTH_LOGGED_IN PHÍA BACKEND]**:
    - **Yêu cầu:** Loại bỏ sự trùng lặp cookie `auth_logged_in` giữa domain frontend (`netlify.app`) và backend (`ddns.net`).
    - **Thực hiện:**
      1. Xóa hoàn toàn việc khởi tạo và trả về cookie `auth_logged_in` từ phía Laravel API trong tệp [AuthController.php](file:///d:/git/isocalmarket/isocalmarket-api/app/Http/Controllers/AuthController.php) ở các phương thức `login()`, `login2Fa()`, và `logout()`.
      2. Lúc này, chỉ còn duy nhất 1 cookie `auth_logged_in` do JS Frontend tự thiết lập và quản lý trên domain `isocalmarket.netlify.app`, giữ cho danh sách cookie lưu trữ trên trình duyệt của người dùng gọn gàng và tối ưu hơn.
    - **Xác minh:** Kiểm tra danh sách cookie trong DevTools sau khi đăng nhập thành công.

  - **[ĐỒNG BỘ CHIỀU CAO & PHÔNG CHỮ NÚT QUAY LẠI QUẢN LÝ NẠP TIỀN]**:
    - **Yêu cầu:** Sửa lỗi nút "Quay lại Quản lý Nạp tiền" và "Xem Lịch Sử Nạp Tiền" ở trang hóa đơn nạp tiền hiển thị không đồng đều (cao hơn các nút thao tác khác như Hủy Lệnh Nạp và Tôi đã chuyển khoản).
    - **Thực hiện:**
      1. Điều chỉnh padding của thẻ `<a>` cho liên kết "Quay lại Quản lý Nạp tiền" và "Xem Lịch Sử Nạp Tiền" trong [deposit-invoice.component.html](file:///d:/git/isocalmarket/isocalmarket-web/src/app/features/deposit-invoice/deposit-invoice.component.html) từ `py-3.5` xuống `py-3` để đồng bộ hoàn hảo với các nút bên trên.
      2. Đồng thời nâng cấp font weight cho nút "Hủy Lệnh Nạp" từ `font-bold` lên `font-extrabold` để đảm bảo kiểu chữ phông chữ đồng đều 100% trên toàn bộ các nút thao tác tại trang hóa đơn.
    - **Xác minh:** Chạy biên dịch frontend `npm run build` thành công hoàn toàn không có lỗi.

  - **[SỬA LỖI MENU SIDEBAR TRÊN MOBILE]**:
    - **Vấn đề**: Người dùng phản hồi lỗi khó mở menu sidebar trên mobile, thường phải bấm 2 lần vào nút Hamburger menu mới hiển thị.
    - **Nguyên nhân**:
      1. Component root `App` dùng các thuộc tính thường (`hasBeenOpened` và `activeMenuState`) để quản lý DOM và transition đóng/mở menu di động, thay vì dùng Signals. Do đó, khi các thuộc tính này thay đổi trong `effect` của `showMobileMenu` (là Signal), Angular không phát hiện được sự thay đổi ngay lập tức để chạy Change Detection cho template, dẫn đến việc cập nhật trạng thái UI bị trễ hoặc không đồng bộ.
      2. Biến `hasBeenOpened` chỉ chuyển thành `true` khi mở lần đầu và không bao giờ chuyển về `false`. Điều này khiến phần tử overlay che phủ màn hình (`fixed inset-0 z-[60]`) luôn luôn tồn tại trong DOM ngay cả khi menu đã đóng. Tuy overlay có class `invisible pointer-events-none`, nhưng do transition và trễ Change Detection hoặc các sự kiện touch/click trên di động, nó vẫn chặn/nuốt sự kiện click đầu tiên của người dùng lên nút Hamburger (nút Hamburger có `z-50` thấp hơn overlay `z-[60]`). Do đó, click đầu tiên bị nuốt bởi backdrop và đặt lại trạng thái đóng, phải bấm lần 2 mới chạm được vào nút Hamburger thật.
    - **Giải pháp & Triển khai**:
      1. Khai báo `hasBeenOpened` và `activeMenuState` dưới dạng **Signals** trong [app.ts](file:///d:/git/isocalmarket/isocalmarket-web/src/app/app.ts) để Angular tối ưu hóa và kích hoạt Change Detection tức thì.
      2. Trong logic `effect` đóng menu, bổ sung `setTimeout(() => { if (!this.stateService.showMobileMenu()) this.hasBeenOpened.set(false); }, 300)` để tự động hủy/xóa hoàn toàn overlay ra khỏi DOM sau khi transition đóng 300ms hoàn tất. Điều này đảm bảo khi menu đóng thì màn hình hoàn toàn sạch bóng overlay.
      3. Cập nhật template [app.html](file:///d:/git/isocalmarket/isocalmarket-web/src/app/app.html) gọi đúng định dạng Signal `hasBeenOpened()` và `activeMenuState()`.
    - **Xác minh**: Biên dịch Angular thành công không lỗi.

  - **[SỬA LỖI GIẬT CON TRỎ VÀ MẤT DẤU CHẤM TRÊN Ô NHẬP SỐ]**:
    - **Vấn đề**: Người dùng gặp hiện tượng rất khó chịu khi nhập số lượng USDT muốn nạp. Khi nhập số thập phân (ví dụ 3.9) rồi nhấn Backspace để xóa số 9, con trỏ (cursor) tự động nhảy về trước số 3 thay vì ở sau dấu chấm, dẫn đến dễ bị gõ sai số.
    - **Nguyên nhân**: Khi người dùng xóa ký tự, giá trị chuỗi thô của input (ví dụ `"3."`) thay đổi. ControlValueAccessor gửi giá trị này cho Angular Forms qua `onChange`. Angular parse `"3."` thành số `3`. Do model thay đổi, Angular gọi `writeValue(3)` cập nhật ngược lại component. Việc gán lại `[value]="val()"` bằng `3` (do `val.set(3)`) khiến trình duyệt ghi đè giá trị `"3."` thành `"3"`, đồng thời reset vị trí con trỏ của input.
    - **Giải pháp & Triển khai**: Cập nhật [custom-input.component.html](file:///d:/git/isocalmarket/isocalmarket-web/src/app/shared/components/custom-input/custom-input.component.html) và [custom-input.component.ts](file:///d:/git/isocalmarket/isocalmarket-web/src/app/shared/components/custom-input/custom-input.component.ts):
      1. Loại bỏ hoàn toàn liên kết một chiều `[value]="val()"` trong template để trả quyền quản lý giá trị thô và cursor vị trí tự nhiên 100% cho trình duyệt khi người dùng đang trực tiếp nhập liệu.
      2. Sử dụng `ElementRef` để cập nhật trực tiếp thuộc tính `value` của input/textarea DOM trong `writeValue` từ file logic code `.ts` (sau khi kiểm tra so sánh tương đương ngữ nghĩa qua hàm `areValuesEquivalent` để tránh ghi đè dư thừa gây giật con trỏ).
      3. Triển khai lifecycle hook `AfterViewInit` để đồng bộ hóa giá trị khởi tạo ban đầu vào phần tử DOM ngay sau khi chúng được render.
    - **Xác minh**: Chạy lệnh build `npm run build` thành công 100% không phát sinh lỗi biên dịch.

  - **[SỬA LỖI KHÔNG CẬP NHẬT SỐ DƯ KHI NẠP TIỀN HOẶC MUA HÀNG THÀNH CÔNG]**:
    - **Vấn đề**: Sau khi nạp tiền thành công (qua kiểm tra giao dịch tự động của BOT) hoặc sau khi đặt hàng thành công (trừ số dư để thanh toán đơn hàng), số dư tài khoản của người dùng trên thanh Header không tự động cập nhật mà phải tải lại trang (reload) mới hiển thị số dư mới. Ngoài ra, cần cập nhật số dư khi chuyển hướng vào trang Nạp tiền (`/deposit`) và Lịch sử nạp (`/deposit/history`) mà không gây spam API lên backend.
    - **Nguyên nhân**:
      1. Phương thức `checkMe()` trong `AuthStore` sử dụng `shareReplay(1)` để cache request `/auth/me`. Khi app đã tải xong lần đầu, `checkMeObservable$` được lưu cache. Các lần gọi tiếp theo sẽ trả về luôn observable đã cache trước đó mà không thực hiện gọi lại HTTP request lên server.
      2. Component hóa đơn nạp tiền `deposit-invoice.component.ts` và storefront `storefront.component.ts` khi thao tác thành công chỉ gọi `checkMe().subscribe()`, dẫn đến nhận về dữ liệu người dùng cũ từ cache.
    - **Giải pháp & Triển khai**:
      1. Cập nhật `checkMe(force = false)` trong [auth.store.ts](file:///d:/git/isocalmarket/isocalmarket-web/src/app/features/auth/auth.store.ts): Bổ sung tham số `force`. Nếu `force = true`, bỏ qua việc kiểm tra và trả về `checkMeObservable$` cũ, thực hiện gọi trực tiếp API `v1/auth/me` để lấy thông tin mới nhất từ cơ sở dữ liệu và cập nhật vào Signal `currentUser`.
      2. **Chống Spam API (Rate Limiting):** Khai báo biến `lastCheckMeTime` trong `AuthStore`. Khi `force = true`, nếu khoảng cách thời gian giữa 2 lần checkMe gần nhất nhỏ hơn 5 giây, hệ thống trả ngay dữ liệu `currentUser` hiện tại dưới dạng Observable (`of(currentUser())`) thay vì gọi tiếp HTTP request, ngăn chặn hoàn toàn việc spam API khi người dùng click qua lại liên tục giữa các trang.
      3. Cập nhật [deposit-invoice.component.ts](file:///d:/git/isocalmarket/isocalmarket-web/src/app/features/deposit-invoice/deposit-invoice.component.ts): Trong phương thức `showSuccessModal()`, chuyển từ `checkMe().subscribe()` thành `checkMe(true).subscribe()` để ép buộc làm mới thông tin số dư ngay sau khi nạp tiền thành công.
      4. Cập nhật [storefront.component.ts](file:///d:/git/isocalmarket/isocalmarket-web/src/app/features/storefront/storefront.component.ts): Trong phương thức `openCart()`, sau khi đặt hàng thành công (`result === 'success'`), gọi `checkMe(true).subscribe()` để cập nhật ngay số dư bị trừ sau khi thanh toán đơn hàng.
      5. Cập nhật [deposit.component.ts](file:///d:/git/isocalmarket/isocalmarket-web/src/app/features/deposit/deposit.component.ts) và [deposit-history.component.ts](file:///d:/git/isocalmarket/isocalmarket-web/src/app/features/deposit-history/deposit-history.component.ts) gọi `checkMe(true).subscribe()` trong `ngOnInit()` để tự động cập nhật số dư mới nhất của người dùng khi truy cập các trang này.
    - **Xác minh**: Chạy lệnh build `npm run build` để kiểm tra biên dịch không có lỗi cú pháp hoặc kiểu dữ liệu.

  - **[TỐI ƯU HÓA DELAY LAZY-LOAD ROUTING]**:
    - **Yêu cầu**: Giải quyết độ trễ (delay) khi chuyển trang do sử dụng `loadComponent: () => import(...)` trong `app.routes.ts`.
    - **Giải pháp & Triển khai**: Cập nhật tệp [app.config.ts](file:///d:/git/isocalmarket/isocalmarket-web/src/app/app.config.ts), import `withPreloading` và `PreloadAllModules` từ `@angular/router`, sau đó bổ sung `withPreloading(PreloadAllModules)` vào trong cấu hình `provideRouter(routes)`.
    - **Kết quả**: Angular tự động tải trước các chunk file tĩnh của các route phụ ở chế độ nền (background) ngay sau khi ứng dụng chính đã tải xong, giúp việc chuyển trang sau đó diễn ra tức thì mà không bị delay do chờ tải file JS từ server.

  - **[THÊM HIỂN THỊ VAI TRÒ TRONG USER DROPDOWN]**:
    - **Yêu cầu:** Hiển thị thêm vai trò (admin hay khách hàng) theo role trong users ở góc trên bên phải (khu vực user menu / header).
    - **Thực hiện:**
      1. Import `BadgeComponent` vào [header.component.ts](file:///d:/git/isocalmarket/isocalmarket-web/src/app/shared/layout/header/header.component.ts) và thêm nó vào danh sách `imports`.
      2. Cập nhật [header.component.html](file:///d:/git/isocalmarket/isocalmarket-web/src/app/shared/layout/header/header.component.html) để hiển thị một `app-badge` nhỏ phía dưới email của người dùng trong dropdown thông tin cá nhân.
      3. Nếu role của người dùng là `'admin'`, badge có variant `'primary'` (màu xanh dương thương hiệu) và hiển thị chữ `'Quản trị viên'`. Ngược lại, badge có variant `'success'` (màu xanh lục) và hiển thị chữ `'Khách hàng'`.
    - **Xác minh:** Chạy biên dịch frontend `npm run build` thành công hoàn toàn không có lỗi.

  - **[SỬA LỖI RESPONSIVE TABS CẤU HÌNH HỆ THỐNG]**:
    - **Yêu cầu:** Sửa lỗi rớt dòng chữ của các tab "Cấu hình thông tin hệ thống" và "Bảo trì & Tối ưu" trên màn hình thiết bị nhỏ (di động).
    - **Thực hiện:**
      1. Cập nhật [admin-settings.component.html](file:///d:/git/isocalmarket/isocalmarket-web/src/app/features/admin-settings/admin-settings.component.html) để bổ sung các class `overflow-x-auto`, `scrollbar-none`, `whitespace-nowrap`, và `flex-nowrap` trên div container bọc các nút tab.
      2. Thêm class `whitespace-nowrap` vào các nút `<button>` tab để ngăn chặn hoàn toàn việc trình duyệt tự động ngắt/rớt chữ xuống dòng.
    - **Xác minh:** Chạy biên dịch frontend `npm run build` thành công hoàn toàn không có lỗi.

  - **[ĐỒNG BỘ SỐ DƯ TẠI TRANG CỬA HÀNG STOREFRONT]**:
    - **Yêu cầu:** Tự động lấy và làm mới thông tin số dư tài khoản mới nhất của người dùng khi họ truy cập vào trang cửa hàng (Storefront).
    - **Thực hiện:** Cập nhật tệp [storefront.component.ts](file:///d:/git/isocalmarket/isocalmarket-web/src/app/features/storefront/storefront.component.ts), gọi `this.state.authStore.checkMe(true).subscribe()` bên trong hàm `ngOnInit()` trước khi tải danh sách sản phẩm.
    - **Xác minh:** Chạy biên dịch frontend `npm run build` thành công hoàn toàn không có lỗi.

  - **[BẢO MẬT & CẢI THIỆN UX TRANG ĐĂNG NHẬP / ĐĂNG KÝ]**:
    - **Yêu cầu:** Tự động điều hướng người dùng đã đăng nhập thành công ra khỏi trang đăng nhập (`/login`) và đăng ký (`/register`) về trang chủ (`/storefront`) hoặc trang quản trị (`/admin/dashboard`), tránh hiện tượng form đăng nhập bị chớp nháy hiển thị trước khi điều hướng.
    - **Giải pháp & Triển khai:**
      1. Tạo `NoAuthGuard` tại [no-auth.guard.ts](file:///d:/git/isocalmarket/isocalmarket-web/src/app/core/guards/no-auth.guard.ts). Guard này kiểm tra xem người dùng đã đăng nhập chưa (thông qua `isAuthenticated` hoặc tự động gọi `checkMe` từ API khi phát hiện có cookie `auth_logged_in=true`).
      2. Nếu đã xác thực thành công, Guard tự động chuyển hướng về `/admin/dashboard` (đối với Admin) hoặc `/storefront` (đối với Khách hàng) và trả về `false` để chặn render trang login/register.
      3. Nếu chưa xác thực, cho phép truy cập tiếp bình thường (trả về `true`). Nếu không có cookie `auth_logged_in`, cho phép truy cập ngay mà không cần gọi API để tránh lãng phí request.
      4. Đăng ký `NoAuthGuard` cho cả 2 route `login` và `register` trong [app.routes.ts](file:///d:/git/isocalmarket/isocalmarket-web/src/app/app.routes.ts).
    - **Xác minh:** Biên dịch frontend Angular thành công 100% không có lỗi.

  - **[KHẮC PHỤC TRIỆT ĐỂ VẤN ĐỀ ĐỒNG BỘ SỐ DƯ TÀI KHOẢN]**:
    - **Yêu cầu:** Khắc phục triệt để vấn đề số dư không bị trừ sau khi đặt hàng/thanh toán thành công, số dư không tự cập nhật sau khi nạp tiền thành công, và số dư không được làm mới khi chuyển qua lại giữa các menu.
    - **Nguyên nhân & Thực hiện khắc phục:**
      1. Sửa lỗi logic ngược trong `AuthStore.checkMe(force)` tại [auth.store.ts](file:///d:/git/isocalmarket/isocalmarket-web/src/app/features/auth/auth.store.ts): Trước đó `if (force && (now - lastCheckMeTime < 5000))` bị ngược làm chặn hoàn toàn API call khi `force=true`. Đã sửa lại để khi `force=true` luôn bỏ qua cache và gọi trực tiếp `v1/auth/me` tới server. Khi `force=false` tự động dùng cache 15s.
      2. Cập nhật Backend [OrderController.php](file:///d:/git/isocalmarket/isocalmarket-api/app/Http/Controllers/OrderController.php): Trả về thông tin `$freshUser` chứa số dư mới đã trừ trong phản hồi API tạo đơn thành công (mã 201).
      3. Cập nhật Frontend [CartModalComponent.ts](file:///d:/git/isocalmarket/isocalmarket-web/src/app/features/storefront/cart-modal/cart-modal.component.ts): Sau khi đặt hàng thành công, tự động cập nhật ngay `currentUser` signal từ phản hồi `res.user` và gọi `checkMe(true).subscribe()`.
      4. Cập nhật [app.ts](file:///d:/git/isocalmarket/isocalmarket-web/src/app/app.ts): Lắng nghe sự kiện `NavigationEnd` của Angular Router để tự động gọi `checkMe(false).subscribe()` ngầm mỗi khi người dùng chuyển hướng menu, giúp số dư luôn ở trạng thái mới nhất mà không gây đơ/giật UI.
    - **Xác minh:** Đã biên dịch ứng dụng Angular (`npm run build`) thành công 100% không phát sinh bất kỳ lỗi nào.

  - **[THÊM CHỨC NĂNG ĐA NGÔN NGỮ I18N & ĐƠN VỊ TIỀN TỆ VỚI TỰ ĐỘNG ĐỒNG BỘ]**:
    - **Yêu cầu:** Thêm chức năng đa ngôn ngữ (mặc định Tiếng Anh EN), đơn vị tiền tệ (mặc định USD $, 1 USD = 25,000 VND), tự động đồng bộ tiền tệ khi chọn ngôn ngữ (EN -> USD, VI -> VND), hiển thị chọn Đơn vị Tiền tệ trong User Dropdown Menu.
    - **Kết quả:** Đã tạo `LanguageService`, `CurrencyService`, `TranslatePipe`, `CurrencyPipe`, cập nhật `HeaderComponent` và 100% các views. Biên dịch `npm run build` thành công 100%.

  - **[HOÀN THÀNH I18N HÓA 100% TOAST MESSAGES FRONTEND & KHẮC PHỤC LỖI BIÊN DỊCH]**:
    - **Yêu cầu:** Chuyển đổi toàn bộ các thông báo Toast (`showToast(...)`) chứa văn bản Tiếng Việt thô cứng trong các Stores, Components, Modals và Guards sang đa ngôn ngữ qua `LanguageService.translate()`.
    - **Thực hiện & Kết quả:**
      1. Đã i18n hóa hoàn toàn các file Stores: `auth.store.ts`, `coupon.store.ts`, `user.store.ts`, `product.store.ts`, `order.store.ts` và `admin-deposit.store.ts`.
      2. Đã i18n hóa hoàn toàn các Components & Modals quản trị/cá nhân: `admin-settings.component.ts`, `products.component.ts`, `product-form-modal.component.ts`, `orders.component.ts`, `order-detail.component.ts`, `import-inventory-modal.component.ts`, `edit-inventory-modal.component.ts`, `deposit.component.ts`, `deposit-invoice.component.ts`, `deposit-history.component.ts`, `profile-information.component.ts`, `profile-password.component.ts`, `enable-2fa-modal.component.ts`, `disable-2fa-modal.component.ts`, `login.component.ts`, `register.component.ts`, `admin-orders.component.ts`, `admin-users.component.ts`, `user-form-modal.component.ts`, `coupon-form-modal.component.ts`, `category-form-modal.component.ts`, `admin-api.component.ts`.
      3. Đã i18n hóa các Guards & Services cốt lõi: `admin.guard.ts`, `state.service.ts`, `web3.service.ts`.
      4. Bổ sung hơn 80 cặp từ khóa dịch thuật (EN & VI) tương ứng vào [language.service.ts](file:///d:/git/isocalmarket/isocalmarket-web/src/app/core/services/language.service.ts).
      5. Đã giải quyết các lỗi biên dịch: Thiếu import `TranslatePipe`/`CurrencyPipe`/`LanguageService` ở header, gọi sai hàm `fetchProductsRaw` thay bằng `getProductsRaw`, và lỗi ép kiểu dữ liệu `[columns]` của Table trong `deposit-history.component.html` sang dùng Signal getter `columns()`.
    - **Xác minh:** Biên dịch frontend Angular thành công 100% không có lỗi (`npm run build`).

  - **[I18N HÓA TOÀN BỘ MODAL CONFIRMATIONS & CÁC NHÃN GIAO DIỆN CHÍNH]**:
    - **Yêu cầu:** Tiếp tục rà soát hệ thống, loại bỏ triệt để các tiêu đề (title), mô tả (description), nút xác nhận (confirmText/cancelText) viết bằng Tiếng Việt cứng trong file `.ts` của các Modal confirm và các nút/nhãn giao diện (cart delete, profile menu, order details).
    - **Thực hiện & Kết quả:**
      1. Đã i18n hóa Modal Confirmations của: bật/tắt 2FA (`profile-security.component.ts`, `user-form-modal.component.ts`), nạp tiền thành công (`deposit-invoice.component.ts`), kiểm tra lệnh nạp cũ (`deposit.component.ts`), từ chối nạp tiền (`admin-deposits.component.ts`), điều chỉnh số dư (`admin-users.component.ts`, `user-form-modal.component.ts`), và mở import/edit tài nguyên (`inventory.component.ts`).
      2. Đã i18n hóa các file HTML giao diện chính: [profile.component.html](file:///d:/git/isocalmarket/isocalmarket-web/src/app/features/profile/profile.component.html), [profile-information.component.html](file:///d:/git/isocalmarket/isocalmarket-web/src/app/features/profile/information/profile-information.component.html), [order-detail.component.html](file:///d:/git/isocalmarket/isocalmarket-web/src/app/features/orders/order-detail/order-detail.component.html), [cart-modal.component.html](file:///d:/git/isocalmarket/isocalmarket-web/src/app/features/storefront/cart-modal/cart-modal.component.html).
      3. Bổ sung đầy đủ import `TranslatePipe` và `TooltipDirective` vào các file `.ts` tương ứng để tránh lỗi template compiler.
      4. Sửa lỗi cú pháp click action expression (sử dụng pipe trực tiếp trong click handler) ở nút Sao chép trong [inventory.component.html](file:///d:/git/isocalmarket/isocalmarket-web/src/app/features/inventory/inventory.component.html).
      5. I18n hóa các nút bấm, tooltip và trạng thái cứng trong trang Quản lý kho hàng [inventory.component.html](file:///d:/git/isocalmarket/isocalmarket-web/src/app/features/inventory/inventory.component.html) và [inventory.component.ts](file:///d:/git/isocalmarket/isocalmarket-web/src/app/features/inventory/inventory.component.ts).
    - **Xác minh:** Biên dịch frontend Angular thành công 100% không có lỗi (`npm run build`).

  - **[LOẠI BỎ TOÀN BỘ CLASS FONT-MONO KHỎI FRONTEND]**:
    - **Yêu cầu:** Loại bỏ 100% lớp `font-mono` khỏi toàn bộ mã nguồn Frontend để giao diện sử dụng font mặc định đồng nhất (`Outfit`/`Inter`).
    - **Thực hiện & Kết quả:**
      1. Đã dọn dẹp thủ công tại các component lõi như `kbd.component.ts`, các modal (`enable-2fa-modal`, `cart-modal`), các view danh sách/chi tiết (`orders`, `products`, `order-detail`, `deposit-detail-modal`, `deposit-history`, `inventory`, `deposit-invoice`, `import-inventory-modal`).
      2. Đã viết và chạy script Node.js tự động dọn dẹp chuẩn xác trên 13 tệp HTML còn lại trong các thư mục quản lý (users, orders, settings, coupons, categories, deposits, api).
      3. Đã chạy tìm kiếm tĩnh `grep_search` kiểm tra đảm bảo **0 kết quả** `font-mono` còn sót lại.
    - **Xác minh:** Biên dịch frontend Angular thành công 100% không có lỗi (`npm run build`).

  - **[KIỂM TRA LỖI API B2B]**:
    - **Yêu cầu**: Kiểm tra lỗi API tra cứu chi tiết đơn hàng B2B (`GET /api/v1/b2b/orders/{orderCode}`) với API Key `7urYI76SIhPlmn7Y` của người dùng.
    - **Thực hiện & Kết quả**:
      1. Đã kiểm tra database: API Key `7urYI76SIhPlmn7Y` là hợp lệ, thuộc về tài khoản `admin` (trạng thái `active`).
      2. Đã giả lập gửi HTTP GET request với API Key trên để tra cứu đơn hàng `ORD-20260720-JQGB5P` thuộc về tài khoản `admin`.
      3. Kết quả phản hồi thành công (`200 OK`) trả về đầy đủ chi tiết đơn hàng và tài nguyên đã mua mà không gặp bất kỳ lỗi runtime nào.
      4. Chỉ ra các lỗi phản hồi dự kiến:
         - `401 Unauthorized` nếu API Key sai hoặc thiếu.
         - `404 Order not found` nếu mã đơn hàng không tồn tại hoặc đơn hàng đó thuộc sở hữu của tài khoản đại lý khác (cơ chế bảo mật dữ liệu của B2B API).

  - **[NÂNG CẤP BẢO MẬT & TÍCH HỢP ĐA DỤNG API KEY B2B]**:
    - **Yêu cầu**: Khắc phục lỗ hổng lưu trữ plaintext của API Key B2B trong database và nâng cấp để người dùng có thể xem lại khóa, đồng thời cho phép sử dụng API Key làm phương thức xác thực thay thế cho JWT token trên toàn bộ các route API (bao gồm cả user API và admin API).
    - **Giải pháp & Triển khai**:
      1. **Database Migration**: Tạo và chạy thành công migration [2026_07_21_200002_add_api_key_hash_to_users_table.php](file:///d:/git/isocalmarket/isocalmarket-api/database/migrations/2026_07_21_200002_add_api_key_hash_to_users_table.php) thêm cột `api_key_hash` (`string(64)`, unique, index) để tìm kiếm nhanh và xóa unique constraint trên cột `api_key` cũ.
      2. **Eloquent Model**: Cập nhật [UserModel.php](file:///d:/git/isocalmarket/isocalmarket-api/app/Infrastructure/Persistence/Eloquent/UserModel.php) thêm cast `'api_key' => 'encrypted'` để tự động mã hóa đối xứng AES-256 (hỗ trợ giải mã xem lại). Sinh khóa ngẫu nhiên dài **32 ký tự** (`Str::random(32)`) và thêm sự kiện `saving` để tự động băm SHA-256 vào `api_key_hash`.
      3. **Repositories & Middleware**: Cập nhật [UserRepository.php](file:///d:/git/isocalmarket/isocalmarket-api/app/Infrastructure/Persistence/Repositories/UserRepository.php) và [B2bAuthMiddleware.php](file:///d:/git/isocalmarket/isocalmarket-api/app/Http/Middleware/B2bAuthMiddleware.php) để đối khớp API Key qua `api_key_hash`.
      4. **JWT Authentication Fallback**: Cập nhật [JwtAuthMiddleware.php](file:///d:/git/isocalmarket/isocalmarket-api/app/Http/Middleware/JwtAuthMiddleware.php) hỗ trợ kiểm tra API Key (`X-API-KEY` header, `api_key` query param, hoặc `Authorization` header thô) làm fallback khi không có JWT token, cho phép gọi bất kỳ API nào (bao gồm user và admin) bằng API Key.
    - **Xác minh**:
      * Chạy test suite `B2bAdminApiTest` thành công 100% (8 tests passed).
      * Chạy test suite `SecurityHardeningTest` thành công 100% (5 tests passed), xác nhận API Key được mã hóa thành công trong DB và xác thực fallback qua `JwtAuthMiddleware` hoạt động hoàn hảo.
  - **[SỬA LỖI DECRYPTEXCEPTION KHI REFRESH TOKEN]**:
    - **Vấn đề**: Khi gọi API `/api/v1/auth/refresh`, backend gặp lỗi `500 Internal Server Error` với exception `Illuminate\Contracts\Encryption\DecryptException: The payload is invalid` ném ra từ `UserMapper::toDomain` tại `AuthService.php:107` khi cố gắng đọc thuộc tính `api_key` của model `UserModel`.
    - **Nguyên nhân**: Cột `api_key` của tài khoản `admin` hiện tại trong database đang lưu giá trị dạng plaintext (`7urYI76SIhPlmn7Y`) từ trước khi thêm cast `'api_key' => 'encrypted'`. Hơn thế nữa, kiểu dữ liệu cột `api_key` đang là `varchar(255)`, trong khi chuỗi mã hóa AES-256 của API key dài 32 ký tự của Laravel sau khi mã hóa có độ dài lên tới 256 ký tự (lớn hơn 255), có nguy cơ bị cắt cụt dữ liệu gây lỗi giải mã.
    - **Giải pháp & Triển khai**:
      1. **Tạo Migration Mới**: Tạo file migration `2026_07_21_062251_encrypt_existing_api_keys.php` để xử lý dữ liệu cũ.
      2. **Thay Đổi Kiểu Cột**: Đổi kiểu cột `api_key` trong bảng `users` từ `varchar(255)` sang `text` nhằm đảm bảo đủ độ dài chứa toàn bộ chuỗi mã hóa mà không lo bị cắt cụt.
      3. **Mã Hóa Dữ Liệu Cũ**: Quét toàn bộ bảng `users`, kiểm tra xem `api_key` nào chưa được mã hóa (bằng cách thử decrypt và bắt exception `DecryptException`). Nếu là plaintext, mã hóa nó bằng `Crypt::encryptString` và điền băm SHA-256 vào `api_key_hash`. Nếu đã được mã hóa nhưng thiếu hash, tự động tính và điền hash SHA-256 tương ứng.
      4. **Chạy Migration**: Đã chạy thành công lệnh `php artisan migrate` để cập nhật cơ sở dữ liệu.
    - **Xác minh**:
      * Dữ liệu trong database của user `admin` đã được mã hóa an toàn và tự động điền `api_key_hash` khớp với giá trị ban đầu.
      * Chạy thành công toàn bộ suite test gồm 35 bài test (`php artisan test`) với kết quả 100% Passed.

  - **[THAY ĐỔI LOGO, FAVICON & THIẾT LẬP META SEO]**:
    - **Yêu cầu**: Sử dụng 2 file logo ở thư mục `logoweb` để thay đổi logo website, favicon (favicon lấy logo trắng) và meta SEO đầy đủ, hợp lý. Yêu cầu giữ nguyên logo gốc (không crop, không tách nền trắng/đen) và loại bỏ hoàn toàn hiệu ứng xoay logo, đồng thời loại bỏ phần background xanh ngọc, viền tròn và hiệu ứng glow xung quanh logo trên giao diện và splash screen.
    - **Thực hiện & Kết quả**:
      1. Đã tạo script PHP chuyển đổi 2 file JPG gốc sang định dạng PNG/ICO trực tiếp (giữ nguyên gốc không crop, không tách nền):
         - `logo-dark.png` (bản logo đen nền trắng từ `photo_2026-07-21_15-05-36.jpg`).
         - `logo-light.png` (bản logo trắng nền đen từ `photo_2026-07-21_15-05-43.jpg`).
         - `favicon.ico` (đa độ phân giải 16x16, 32x32) và các kích thước favicon khác từ logo trắng nền đen.
         - `og-image.png` (1200x630) có logo light đặt ở trung tâm.
      2. Cập nhật [logo.component.ts](file:///d:/git/isocalmarket/isocalmarket-web/src/app/shared/components/logo/logo.component.ts):
         - Hiển thị ảnh logo responsive tĩnh theo chế độ sáng/tối (Light/Dark Mode) và loại bỏ hiệu ứng xoay logo (`animate-[spin_...]`).
         - Loại bỏ toàn bộ phần background màu xanh ngọc nhạt (`bg-secondary/10`), viền (`border-secondary/20`), padding và nền glow tròn xung quanh logo.
      3. Cập nhật [index.html](file:///d:/git/isocalmarket/isocalmarket-web/src/index.html):
         - Liên kết favicon mới, tắt hiệu ứng xoay ở splash screen và thêm đầy đủ các thẻ Meta SEO (description, keywords, og:*, twitter:*).
         - Loại bỏ container viền tròn màu xanh, padding và thẻ glow gradient (`app-splash-logo-glow`) trên màn hình splash screen khởi chạy.
      4. Cập nhật [web3.service.ts](file:///d:/git/isocalmarket/isocalmarket-web/src/app/core/services/web3.service.ts) trỏ icon AppKit sang `assets/logo-light.png` và dọn dẹp file `logo.svg` cũ.
    - **Xác minh**: Chạy biên dịch frontend Angular (`npm run build`) thành công 100% không phát sinh lỗi.

  - **[PHÂN TRANG CHO INVENTORY FILTER TẠI ADMIN DASHBOARD]**:
    - **Yêu cầu**: Phân trang danh sách "Inventory Filter (< 5 items)" tại trang Admin Dashboard.
    - **Thực hiện & Kết quả**:
      1. **Backend API**:
         - Cập nhật endpoint `v1/admin/dashboard-stats` trong `DashboardController.php`.
         - Thay thế truy vấn MySQL thô phức tạp trước đó bằng Eloquent query sạch sẽ, sử dụng mệnh đề `having('available_count', ...)` và phương thức `paginate()` tích hợp sẵn của Laravel để phân trang trực tiếp ở tầng database.
         - Thêm `low_stock_page` và `low_stock_limit` vào cache key để cache riêng lẻ từng trang dữ liệu low stock.
         - Điều chỉnh logic parse tham số `low_stock_threshold` để nhận diện tiền tố `gte_` (như `gte_10`, `gte_50`, `gte_100`) nhằm lọc các sản phẩm có tồn kho lớn hơn hoặc bằng (`>=`) ngưỡng tương ứng, thay vì chỉ lọc nhỏ hơn (`<`) như trước.
      2. **Frontend Angular**:
         - Cập nhật `AdminDashboardComponent` (`admin-dashboard.component.ts`): Import component `PaginationComponent`, bổ sung tín hiệu `lowStockCurrentPage` (mặc định là 1) và `lowStockLimit` (mặc định là 10) cùng phương thức `fetchLowStockPage(page)`.
         - Thay đổi bộ lọc tồn kho (`thresholdOptions`) sang các giá trị: `< 5` (`'5'`), `>= 10` (`'gte_10'`), `>= 50` (`'gte_50'`), và `>= 100` (`'gte_100'`).
         - Cập nhật [language.service.ts](file:///d:/git/isocalmarket/isocalmarket-web/src/app/core/services/language.service.ts) bổ sung key dịch thuật mới `dash.at_least_items_simple` cho cả bản dịch tiếng Anh và tiếng Việt.
         - Cập nhật `admin-dashboard.component.html` để gán dữ liệu nguồn bảng từ `stats().low_stock_products.data`, cập nhật các điều kiện kiểm tra độ dài mảng và chèn thẻ `<app-pagination>` với nhãn sản phẩm `admin.product_item` đồng bộ. Điều chỉnh logic kiểm tra trạng thái hiển thị "Cần nhập hàng gấp" (Restock Urgent) chỉ kích hoạt khi bộ lọc được chọn là `'5'`.
    - **Xác minh**: Biên dịch Angular thành công không lỗi và kiểm tra các bộ lọc hoạt động chính xác 100% trên database.
1797: 
1798:   - **[THÊM LIÊN KẾT THÔNG TIN CÁ NHÂN VÀO SIDEBAR PHÍA USER]**:
1799:     - **Yêu cầu**: Thêm mục "Thông tin cá nhân" nằm ở dưới lịch sử nạp tiền, đường dẫn là `/profile/information`.
1800:     - **Thực hiện & Kết quả**:
1801:       1. Cập nhật [sidebar.component.html](file:///d:/git/isocalmarket/isocalmarket-web/src/app/shared/layout/sidebar/sidebar.component.html) (Desktop) và [app.html](file:///d:/git/isocalmarket/isocalmarket-web/src/app/app.html) (Mobile sidebar drawer) để bổ sung thẻ `a` liên kết đến `/profile/information` ngay phía dưới liên kết Lịch sử nạp tiền `/deposit/history` và trên mục User API `/user-api`.
1802:       2. Sử dụng icon `'user'` và khóa dịch thuật `'nav.profile'` đã có sẵn trong `LanguageService` (đã dịch chính xác sang tiếng Việt là "Thông tin cá nhân" và tiếng Anh là "Profile Settings").
1803:     - **Xác minh**: Chạy biên dịch frontend Angular (`npm run build`) thành công 100% không phát sinh lỗi.




  - **[THÊM MÀU SẮC ĐẶC SẮC CHO CARD TỔNG ĐƠN HÀNG TẠI DASHBOARD]**:
    - **Yêu cầu**: Thêm màu sắc cho nhãn 'Toàn hệ thống' và icon tại ô thống kê Tổng đơn hàng trên Admin Dashboard cho sinh động, bớt xám.
    - **Thực hiện & Kết quả**:
      1. Sửa tệp admin-dashboard.component.html.
      2. Đổi màu nền và text cho icon Tổng đơn hàng từ xám (bg-slate-100 / text-slate-500) sang màu chàm (bg-indigo-50 / text-indigo-500).
      3. Đổi variant của <app-badge> tại nhãn 'Toàn hệ thống' từ neutral (màu xám) sang ultra (gradient đa sắc chuyển màu từ primary sang secondary rất độc đáo).
    - **Xác minh**: Biên dịch npm run build thành công 100%.


  - **[TỐI ƯU HÓA MÀU SẮC ĐỒNG BỘ CHO 5 CARD THỐNG KÊ DASHBOARD]**:
    - **Yêu cầu**: Thay đổi màu sắc các nhãn (badge) và icon trên các card thống kê của Dashboard cho sinh động, rực rỡ và đặc sắc.
    - **Thực hiện & Kết quả**:
      1. Sửa tệp admin-dashboard.component.html.
      2. Chuyển đổi card **Tổng đơn hàng** sang tông màu chàm (**Indigo**): Icon dùng màu indigo (	ext-indigo-500 / g-indigo-50), nhãn 'Toàn hệ thống' đổi từ variant 'ultra' sang class custom màu Indigo (!bg-indigo-500/10 !text-indigo-600 !border-indigo-500/20).
      3. Chuyển đổi card **Tổng người dùng** sang tông màu tím (**Purple**) đồng bộ: Giữ nguyên icon màu tím, đổi nhãn 'Đăng ký hệ thống' từ variant 'info' (màu xanh lục lệch tông) sang class custom màu Purple (!bg-purple-500/10 !text-purple-600 !border-purple-500/20).
    - **Xác minh**: Biên dịch npm run build thành công 100%.


  - **[SỬA MÀU NỀN CARD TỔNG ĐƠN HÀNG TOÀN HỆ THỐNG]**:
    - **Yêu cầu**: Sửa màu background của card Tổng đơn hàng (Toàn hệ thống) trên Dashboard cho đặc sắc.
    - **Thực hiện & Kết quả**:
      1. Sửa tệp admin-dashboard.component.html.
      2. Đổi class của app-card Tổng đơn hàng, thêm màu nền chàm nhạt (!bg-indigo-50/50 / dark:!bg-indigo-950/15) và viền chàm nhạt (!border-indigo-100 / dark:!border-indigo-900/30) giúp card nổi bật hơn hẳn so với nền trắng mặc định.
    - **Xác minh**: Biên dịch npm run build thành công 100%.


  - **[XÓA MÀU NỀN CARD TỔNG ĐƠN HÀNG TOÀN HỆ THỐNG]**:
    - **Yêu cầu**: Xóa màu background của card Tổng đơn hàng (Toàn hệ thống) trên Dashboard để trở lại mặc định.
    - **Thực hiện & Kết quả**:
      1. Sửa tệp admin-dashboard.component.html.
      2. Khôi phục class của app-card Tổng đơn hàng về mặc định (xóa bỏ !bg-indigo-50/50 và viền indigo), đưa card về dạng nền trắng/kính giống các card thống kê khác.
    - **Xác minh**: Biên dịch npm run build thành công 100%.


  - **[THIẾT KẾ LẠI GIAO DIỆN TRANG ĐĂNG NHẬP]**:
    - **Yêu cầu**: Thiết kế lại trang Đăng nhập dựa trên tham khảo vlclone.com/login, giữ nguyên hệ màu dynamic của web, hỗ trợ Light/Dark mode và Responsive.
    - **Thực hiện & Kết quả**:
      1. **Dịch thuật (i18n)**: Cập nhật [language.service.ts](file:///d:/git/isocalmarket/isocalmarket-web/src/app/core/services/language.service.ts) để thêm các key dịch tiếng Anh và tiếng Việt cho tiêu đề, slogan, 3 tính năng nổi bật, cam kết sử dụng và an toàn chân trang.
      2. **Logic UI (LoginComponent)**: Cập nhật [login.component.ts](file:///d:/git/isocalmarket/isocalmarket-web/src/app/features/auth/login.component.ts) thêm signal `showPassword` và hàm `toggleShowPassword()`. Import `LogoComponent` và dọn dẹp các component dư thừa khỏi mảng `imports`.
      3. **Giao diện HTML (LoginComponent)**: Thay thế hoàn toàn [login.component.html](file:///d:/git/isocalmarket/isocalmarket-web/src/app/features/auth/login.component.html) sang bố cục Split Layout 2 cột. Cột bên trái hiển thị nội dung marketing và các tính năng nổi bật của Isocalmarket với phong cách tối sang trọng, viền gradient và hiệu ứng phát sáng mờ. Cột bên phải chứa form đăng nhập đa ngôn ngữ, các trường nhập liệu có icon dẫn đầu (Email/Lock) và nút ẩn/hiện mật khẩu. Tích hợp dải an toàn (SSL, 24/7, 2FA) ở dưới cùng. Ẩn cột Marketing trên thiết bị di động.
      4. **Xác minh**: Khắc phục lỗi compiler liên quan đến Action expression pipe (`| translate`) và loại toast không đúng bằng cách dùng `lang.translate()` trực tiếp và Toast type `'warning'`. Chạy lệnh `npm run build` kiểm tra biên dịch thành công 100%.


  - **[ẨN SIDEBAR & HEADER CHUNG TẠI TRANG ĐĂNG NHẬP / ĐĂNG KÝ]**:
    - **Yêu cầu**: Sửa lỗi trang đăng nhập và đăng ký vẫn hiện sidebar menu và header chung của ứng dụng.
    - **Thực hiện & Kết quả**:
      1. **Logic (App Component)**: Cập nhật [app.ts](file:///d:/git/isocalmarket/isocalmarket-web/src/app/app.ts) bổ sung signal `isAuthPage` và thiết lập kiểm tra URL ban đầu cũng như lắng nghe sự kiện `NavigationEnd` của `router` để xác định xem route hiện tại có phải là `/login` hoặc `/register`.
      2. **Giao diện (App HTML)**: Cập nhật [app.html](file:///d:/git/isocalmarket/isocalmarket-web/src/app/app.html), bọc `<app-sidebar>`, `<app-header>` và menu mobile drawer trong khối điều kiện `@if (!isAuthPage())`. Đồng thời, động hoá class thụt lề bằng cách dùng `[ngClass]="isAuthPage() ? '' : 'lg:pl-72 pt-4'"` trên container chính chứa `<router-outlet>`.
      3. **Xác minh**: Chạy build `npm run build` thành công 100% không có lỗi. Sidebar và Header đã ẩn hoàn toàn ở trang Đăng nhập và Đăng ký.


  - **[THIẾT KẾ LẠI TRANG ĐĂNG KÝ & THÊM BỘ CHUYỂN THEME/NGÔN NGỮ]**:
    - **Yêu cầu**: Thêm nút chuyển đổi Light/Dark/Auto, bộ chuyển ngôn ngữ và thiết kế lại trang Đăng ký cho đồng bộ với trang Đăng nhập.
    - **Thực hiện & Kết quả**:
      1. **Giao diện (LoginComponent)**: Cập nhật [login.component.html](file:///d:/git/isocalmarket/isocalmarket-web/src/app/features/auth/login.component.html) bổ sung cụm đổi theme và ngôn ngữ góc trên bên phải dạng Pill capsule tinh tế.
      2. **Logic (RegisterComponent)**: Cập nhật [register.component.ts](file:///d:/git/isocalmarket/isocalmarket-web/src/app/features/auth/register.component.ts) thêm signal `showPassword` và `showConfirmPassword` cùng các hàm toggle tương ứng. Import `LogoComponent` và `ThemeSwitcherComponent`, dọn dẹp các component dư thừa khỏi mảng `imports`.
      3. **Giao diện (RegisterComponent)**: Thay thế hoàn toàn [register.component.html](file:///d:/git/isocalmarket/isocalmarket-web/src/app/features/auth/register.component.html) thành bố cục Split Layout 2 cột giống hệt trang đăng nhập, tích hợp cụm đổi theme/ngôn ngữ góc trên bên phải và nút ẩn/hiện mật khẩu ở 2 trường password.
      4. **Xác minh**: Chạy build `npm run build` thành công 100% không phát sinh lỗi. Hệ thống hỗ trợ thay đổi theme và ngôn ngữ hoàn hảo trên trang auth.


  - **[ĐỒNG BỘ MÀU CỘT TRÁI MARKETING THEO LIGHT/DARK MODE]**:
    - **Yêu cầu**: Sửa đổi cột Marketing bên trái ở cả trang Đăng nhập và Đăng ký để màu sắc thay đổi linh hoạt theo chế độ Sáng/Tối của hệ thống thay vì cố định một màu tối.
    - **Thực hiện & Kết quả**:
      1. **Giao diện (LoginComponent & RegisterComponent)**: Cập nhật [login.component.html](file:///d:/git/isocalmarket/isocalmarket-web/src/app/features/auth/login.component.html) và [register.component.html](file:///d:/git/isocalmarket/isocalmarket-web/src/app/features/auth/register.component.html), thay đổi các class màu sắc tĩnh (`bg-slate-950`, `text-white`, `text-slate-300`, v.v.) của cột trái thành các class thích ứng (`bg-white dark:bg-slate-950`, `text-slate-900 dark:text-slate-100`, `text-slate-600 dark:text-slate-300`).
      2. **Hiệu ứng**: Cập nhật màu của logo, slogan, mô tả, nhãn tính năng và disclaimer chân trang, đồng thời tinh chỉnh opacity của gradient mờ góc nền để hiển thị tinh tế và sang trọng trong cả 2 chế độ sáng và tối.
      3. **Xác minh**: Chạy build `npm run build` thành công 100% không phát sinh lỗi.


  - **[SỬ DỤNG LẠI COMPONENT INPUT, XÓA QUÊN MẬT KHẨU & SỬA LỖI GIẬT LÙI KHI RELOAD]**:
    - **Yêu cầu**: Sử dụng lại component input của hệ thống (`app-custom-input`), xóa bỏ nút Quên mật khẩu trên trang đăng nhập, và sửa lỗi giao diện bị giật lùi (layout shift) chớp nhoáng khi reload trang tại `/login` hoặc `/register`.
    - **Thực hiện & Kết quả**:
      1. **Giao diện (LoginComponent & RegisterComponent)**: Cập nhật [login.component.html](file:///d:/git/isocalmarket/isocalmarket-web/src/app/features/auth/login.component.html) và [register.component.html](file:///d:/git/isocalmarket/isocalmarket-web/src/app/features/auth/register.component.html) thay thế hoàn toàn các trường input tự chế bằng component `<app-custom-input>` bọc trong `.form-field` theo quy chuẩn của dự án. Loại bỏ nút bấm "Quên mật khẩu?" trên trang Đăng nhập.
      2. **Logic (app.ts)**: Cập nhật [app.ts](file:///d:/git/isocalmarket/isocalmarket-web/src/app/app.ts) thay đổi khai báo khởi tạo signal `isAuthPage` kiểm tra trực tiếp qua `window.location.pathname` ngay từ lúc load trang thay vì khởi tạo bằng `false` rồi đợi router event. Xóa bỏ logic gán URL ban đầu không chính xác trong `ngOnInit()`.
      3. **Xác minh**: Chạy build `npm run build` thành công 100% không phát sinh lỗi. Lỗi layout shift biến mất hoàn toàn khi người dùng reload trang trực tiếp tại các trang auth.


  - **[CHIA ĐỀU BỐ CỤC 2 CỘT 50-50 CHO TRANG AUTH TRÊN DESKTOP]**:
    - **Yêu cầu**: Căn đối chia 2 phần bằng nhau (50% - 50%) trên màn hình Desktop cho cả trang Đăng nhập và Đăng ký.
    - **Thực hiện & Kết quả**:
      1. **Giao diện (LoginComponent & RegisterComponent)**: Cập nhật [login.component.html](file:///d:/git/isocalmarket/isocalmarket-web/src/app/features/auth/login.component.html) và [register.component.html](file:///d:/git/isocalmarket/isocalmarket-web/src/app/features/auth/register.component.html) thay thế cấu trúc lưới cũ `grid-cols-12` bằng `grid-cols-1 lg:grid-cols-2`. Hai cột trái và phải đều thiết lập chiếm tỷ lệ bằng nhau (`lg:col-span-1`).
      2. **Xác minh**: Chạy build `npm run build` thành công 100% không phát sinh lỗi. Bố cục 2 phần được phân bố đối xứng và cân đối hoàn hảo trên màn hình lớn.


  - **[THÊM ĐƯỜNG DẪN LIÊN KẾT CHUYỂN ĐỔI TÀI KHOẢN TRANG AUTH]**:
    - **Yêu cầu**: Thêm các liên kết "Chưa có tài khoản? Đăng ký ngay" trên trang Đăng nhập và "Đã có tài khoản? Đăng nhập ngay" trên trang Đăng ký ở vị trí cân đối (giữa nút submit và dải bảo mật).
    - **Thực hiện & Kết quả**:
      1. **Giao diện (LoginComponent)**: Cập nhật [login.component.html](file:///d:/git/isocalmarket/isocalmarket-web/src/app/features/auth/login.component.html) bổ sung khối text và link routerLink="/register" ngay dưới nút submit "Đăng nhập".
      2. **Giao diện (RegisterComponent)**: Cập nhật [register.component.html](file:///d:/git/isocalmarket/isocalmarket-web/src/app/features/auth/register.component.html) bổ sung khối text và link routerLink="/login" ngay dưới nút submit "Đăng ký".
      3. **Xác minh**: Chạy build `npm run build` thành công 100% không phát sinh lỗi. Các liên kết hoạt động tốt, hỗ trợ đa ngôn ngữ đầy đủ.

  - **[SỬA LỖI CRASH UI TẠI TRANG ADMIN DASHBOARD]**:
    - **Yêu cầu**: Sửa lỗi crash UI trên production khi truy cập trang /admin/dashboard do lỗi TypeError: Cannot read properties of undefined (reading 'length').
    - **Thực hiện & Kết quả**:
      1. **Phân tích**: Xác định lỗi xảy ra do việc truy cập trực tiếp .length và .data của stats().low_stock_products.data trong template admin-dashboard.component.html mà không dùng safe navigation operator ?.. Khi API trả về lỗi hoặc thiếu dữ liệu, hoặc trong lúc loading, các thuộc tính này bị undefined làm crash giao diện.
      2. **Khắc phục**: Thay thế các truy cập trực tiếp thành dạng an toàn có safe navigation stats()?.low_stock_products?.data và sử dụng mảng rỗng làm fallback: (stats()?.low_stock_products?.data || []).length. Áp dụng safe navigation cho toàn bộ các thuộc tính hiển thị khác của stats().
      3. **Xác minh**: Chạy kiểm tra biên dịch npm run build thành công 100%.

  - **[TẮT INLINE CRITICAL CSS TRÁNH LỖI CSP LÀM MẤT CSS]**:
    - **Yêu cầu**: Khắc phục lỗi CSP chặn inline event handler onload trên thẻ stylesheet làm trang web bị mất sạch CSS (style Tailwind/CSS không được áp dụng).
    - **Thực hiện & Kết quả**:
      1. **Phân tích**: Angular CLI mặc định tối ưu CSS bằng cách tải bất đồng bộ qua onload="this.media='all'". Tuy nhiên CSP của Netlify cấu hình script-src 'self' chặn toàn bộ inline event handlers, làm stylesheet kẹt ở media="print" và không áp dụng lên màn hình.
      2. **Khắc phục**: Sửa cấu hình angular.json, thêm "optimization": { "styles": { "inlineCritical": false } } vào cấu hình build production. Điều này giúp Angular sinh ra link style đồng bộ truyền thống không chứa inline script.
      3. **Xác minh**: Chạy build frontend thành công. File index.html đầu ra hiển thị link style sạch sẽ <link rel="stylesheet" href="styles-HFGYCSND.css">.

  - **[THÊM LÁ CỜ NGÔN NGỮ & SỬA LỖI Ô MẬT KHẨU]**:
    - **Yêu cầu**: Thêm hình ảnh logo lá cờ (Việt Nam và Anh) cho đổi ngôn ngữ, sửa lỗi width/focus ô mật khẩu.
    - **Thực hiện & Kết quả**:
      1. **Styles**: Định nghĩa class .form-input-password trong styles.scss để tăng padding-right.
      2. **Auth Layout**: Đổi wrapper ô mật khẩu thành elative w-full giúp input tự động rộng 100%; căn giữa lại nút con mắt; tích hợp cờ SVG chất lượng cao cho VI/EN switcher.
      3. **Header**: Hiển thị cờ SVG tương ứng ở nút chọn và các tùy chọn dropdown ngôn ngữ của Header.
      4. **Xác minh**: Build dự án Angular thành công.

  - **[CHUYỂN SWITHCHER NGÔN NGỮ TRANG AUTH THÀNH DROPDOWN]**:
    - **Yêu cầu**: Chuyển đổi cụm đổi ngôn ngữ tĩnh VI | EN trên trang Auth thành dropdown có cờ tương tự Header.
    - **Thực hiện & Kết quả**:
      1. **Logic (auth-layout.component.ts)**: Import HostListener, thêm signal showLangDropdown và các phương thức toggle/select/close dropdown.
      2. **Giao diện (auth-layout.component.html)**: Thay thế cụm đổi ngôn ngữ tĩnh thành dropdown cao 32px (h-8) có cờ, chữ viết tắt, chevron và menu thả xuống.
      3. **Xác minh**: Build dự án Angular thành công.

  - **[CHO PHÊP TRUY CẬP STOREFRONT KHI CHƯA ĐĂNG NHẬP]**:\n    - **Yêu cầu**: Cho phép khách vãng lai chưa đăng nhập vẫn truy cập được /storefront để xem sản phẩm. Nếu click biểu tượng giỏ hàng hoặc nút thêm vào giỏ, hiển thị Toast cảnh báo yêu cáu đăng nhập. Nếu truy cập các trang bảo mật, hiển thị Toast yêu cáu đăng nhập và chuyển hướng sang /login. Khi phiên hết hạn tại /storefront, giỿ họ lại dưới vai trò khách vãng lai.\n    - **Thực hiện & Kết quả**:\n      1. **Dịch thuật (language.service.ts)**: Thêm các key cảnh báo yêu cáu đăng nhập cho cả EN và VI.\n      2. **Interceptor (auth.interceptor.ts)**: Cập nhật handle401Error để không chuyển hướng về /login nếu đang truy cập trang công khai (/storefront, /, /login, /register).\n      3. **Route Guard (auth.guard.ts)**: Thêm Toast cảnh báo khi chặn các route yêu cáu bảo mật.\n      4. **Component (storefront.component.ts & header.component.ts)**: Thêm check isAuthenticated() để chặn hành động giỏ hàng và hiển thị Toast cảnh báo tương ứng.\n      5. **Xác minh**: Chạy build Angular thành công.\n

## [2026-07-21] Sửa lỗi i18n thiếu key
- Đã bổ sung key uth.marketing_title_normal_2 vào từ điển tiếng Anh (en) trong LanguageService.
- Bổ sung các key dịch cho UI tab (User Info, Permissions, Security & API, Deposit History) đồng bộ cả 2 ngôn ngữ EN và VI.
- Cập nhật pipe translate cho tiêu đề mô tả Deposit History.

## [2026-07-21T11:25:57.947Z] Audit & Synchronize i18n Translations
- **Yêu cầu:** Rà soát toàn bộ source code HTML, các component TS và file `language.service.ts` để kiểm tra việc gắn key i18n và bổ sung các bản dịch còn thiếu.
- **Thực hiện:**
  1. Đã phân tích 65 file HTML template và 105 file TypeScript trong dự án Angular frontend.
  2. Xác nhận 100% các thành phần HTML và Toast/Alert/Modal messages đều đã được gắn key i18n đầy đủ.
  3. Rà soát file `language.service.ts` với 1080 key dịch ở cả hai ngôn ngữ (`en` và `vi`), kiểm tra tính đồng nhất của các tham số biến (`{{count}}`, `{{code}}`, ...).
  4. Cập nhật bổ sung key `auth.marketing_title_normal_2` trong cấu hình tiếng Anh để chuẩn hóa kết thúc câu giao diện Login.

## [2026-07-21] Sửa lỗi đổi mật khẩu bị out về trang Login
- **Yêu cầu:** Sửa lỗi khi người dùng đổi mật khẩu thành công tại trang Profile thì hệ thống tự động đăng xuất và đẩy người dùng về trang Login.
- **Phân tích:**
  1. Ở Backend (`ProfileController.php`): Trước đây khi đổi mật khẩu thành công, controller đã xóa cookie `access_token` và `refresh_token` dẫn đến phiên làm việc bị hủy.
  2. Ở Frontend (`auth.store.ts`): Trong toán tử `tap` của `changePassword`, store tự động gọi `clearFrontendLoggedInCookie()`, đặt `currentUser(null)` và chuyển hướng người dùng sang `/login`.
- **Thực hiện:**
  1. **Backend (`ProfileController.php`)**: Inject `AuthService`, sau khi đổi mật khẩu thành công thì tăng `token_version` (vô hiệu hóa token cũ ở thiết bị khác) và cấp lại ngay cặp cookie `access_token` (30 phút) và `refresh_token` (7 ngày) mới cho phiên làm việc hiện tại của người dùng.
  2. **Frontend (`auth.store.ts`)**: Loại bỏ việc xóa cookie, hủy user state và chuyển hướng trang `/login` trong method `changePassword`. Giữ lại Toast thông báo thành công.
  3. **Xác minh**: Chạy `npm run build` biên dịch thành công 100%. Người dùng giữ nguyên đăng nhập mượt mà sau khi đổi mật khẩu.

## [2026-07-21] Đồng bộ Icon mắt ẩn/hiện mật khẩu & Ô nhập 2FA OTP Code
- **Yêu cầu:** Tích hợp icon mắt ẩn/hiện mật khẩu cho tất cả các ô nhập mật khẩu (Profile, Admin Users, Login, Register...) và đồng bộ giao diện ô nhập 2FA OTP code về chuẩn `app-custom-input`.
- **Thực hiện:**
  1. **CustomInputComponent (`custom-input.component.ts` & `.html`)**: Tích hợp sẵn nút toggle icon con mắt cho tất cả các trường `type="password"`. Tự động gán style padding-right và icon ẩn/hiện mật khẩu trực tiếp trong component dùng chung.
  2. **Giao diện 2FA OTP Code (`auth-layout.component.html` & `login.component.html`)**: Chuyển đổi ô nhập mã OTP dạng đường gạch dưới (`border-b-2`) cũ sang component `<app-custom-input>` với góc bo tròn `rounded-xl` chuẩn thiết kế hệ thống.
## [2026-07-21] Sửa lỗi trang nạp tiền bị treo không tự động kiểm tra giao dịch
- **Yêu cầu:** Trang nạp tiền `/deposit/invoice/{id}` sau khi chuyển khoản bị treo, không tự động kiểm tra giao dịch.
- **Phân tích nguyên nhân:**
  1. Polling ở frontend trước đây chỉ gọi `fetchInvoice()` (chỉ đọc lại status từ Database) chứ không trigger kiểm tra blockchain.
  2. Backend chỉ quét Moralis/Binance API khi người dùng bấm nút "Tôi đã chuyển khoản" thủ công hoặc khi cronjob backend `deposit:check-pending` chạy.
  3. Cả Moralis API lẫn Binance Pay API ở backend chưa được lưu Cache ngắn hạn, gây rủi ro rate-limit nếu polling dồn dập.
- **Thực hiện:**
  1. **Backend (`DepositService.php`)**: Áp dụng `Cache::remember()` cho kết quả gọi Moralis API transfers và Binance Pay API với thời gian lưu cache 10 giây.
  2. **Frontend (`deposit-invoice.component.ts`)**: Rút ngắn thời gian polling từ 30s xuống 10s, tự động kích hoạt `checkTransaction(invoiceId)` ngầm khi polling. Nếu tiền về -> Tự động bật Modal thông báo thành công.
  3. **Giao diện (`deposit-invoice.component.html`)**: Thêm banner Live Status *"🤖 BOT đang tự động quét và đối soát giao dịch mỗi 10 giây..."* với radar nhấp nháy xanh lá.
  4. **Xác minh**: Chạy `npm run build` biên dịch thành công 100%, `php artisan test` vượt qua 47/47 unit & feature tests.

## [2026-07-22] Sửa lỗi bấm "Nhập kho" tại Lọc Tồn Kho Dashboard không pre-fill danh mục sản phẩm
- **Yêu cầu:** Tại Dashboard (`/admin/dashboard`), trong danh sách "Lọc Tồn Kho (< 5 items)", bấm nút "Nhập kho" ở một sản phẩm thì redirect sang `/admin/inventory?product_id=xxx` nhưng Modal Import mở ra không load/pre-select danh mục sản phẩm đó.
- **Phân tích nguyên nhân:**
  1. Ở `inventory.component.ts`: Khi mở modal `ImportInventoryModalComponent`, dữ liệu truyền vào là `data: { selectedProductId: defaultProductId, ... }`.
  2. Ở `import-inventory-modal.component.ts`: Trong `ngOnInit()`, code lại đọc `this.modalData.productId` dẫn tới bị `undefined`, khiến `this.importProductId` không được cập nhật giá trị ID sản phẩm.
  3. `ImportInventoryModalComponent` gọi API `getProductsRaw` riêng đè lên danh sách `productOptions` đã truyền qua `modalData`, và không gắn option mặc định `{ label: 'Chọn...', value: '' }` làm vỡ tính nhất quán.
- **Thực hiện khắc phục:**
  1. **`import-inventory-modal.component.ts`**: Đọc linh hoạt cả `modalData.productId` lẫn `modalData.selectedProductId`. Sử dụng trực tiếp `productOptions` được truyền từ component cha hoặc state store, loại bỏ API call `getProductsRaw` dư thừa.
  2. **`inventory.component.ts`**: Đọc param `product_id` / `productId` từ URL, tự động set `selectedProductFilter = productId` để lọc sẵn sản phẩm trên bảng kho hàng, và truyền đồng thời `productId` & `selectedProductId` vào `modalData`.
  3. **Xác minh**: Chạy `npm run build` thành công 100% không có lỗi.

## [2026-07-22] Rà soát và thay thế toàn bộ Emoji bằng Icon SVG trong Frontend
- **Yêu cầu:** Kiểm tra toàn bộ mã nguồn Frontend (Angular), xóa tất cả các ký tự Emoji và thay thế bằng Icon SVG tương ứng.
- **Thực hiện rà soát & kết quả:**
  1. **Rà soát toàn bộ Frontend (`isocalmarket-web`)**: Chạy script quét unicode surrogate pairs (emojis), dingbats và symbol ranges trên toàn bộ mã nguồn (`.html`, `.ts`, `.css`, `.scss`, `.json`).
  2. **Kết quả rà soát**: Phát hiện duy nhất 1 ký tự emoji `💡` (Lightbulb) tại dòng 49 của file `import-inventory-modal.component.html`.
  3. **Thay thế bằng Icon SVG**: Thay thế emoji `💡` bằng thẻ SVG inline `text-amber-500` mượt mà, responsive và đúng chuẩn thiết kế hệ thống.
  4. **Xác minh lại**: Chạy lại script rà soát xác nhận 0 ký tự emoji còn sót lại trong toàn bộ repository Frontend. Chạy `npm run build` biên dịch thành công 100%.

## [2026-07-22] Sửa hiển thị trạng thái đã lưu cho Moralis API Key, Binance API Key & Binance API Secret
- **Yêu cầu:** Tại trang Cấu hình hệ thống (`/admin/settings/general`), Admin đã lưu Moralis API Key, Binance API Key, Binance API Secret thành công nhưng khi tải lại trang thì các ô nhập liệu bị trống trơn, không hiển thị bất kỳ dấu hiệu nào cho biết API keys đã được lưu hay chưa.
- **Phân tích nguyên nhân:**
  1. **Bảo mật Backend**: Backend `SystemSettingController@index` không bao giờ trả về chuỗi API Key/Secret công khai (security best practice), mà chỉ trả về các cờ boolean (`has_scanner_api_key`, `has_binance_api_key`, `has_binance_api_secret`).
  2. **Frontend UI**: Frontend khi load xong gán giá trị các key thành chuỗi rỗng `''`. Tuy nhiên template HTML `admin-settings.component.html` lại dùng placeholder mặc định "Nhập API Key..." mà không hề kiểm tra các cờ `has_X_key`, và không có nhãn/badge hiển thị trạng thái cho Admin biết là API Key đã được mã hóa & lưu an toàn.
- **Thực hiện khắc phục:**
  1. **`language.service.ts`**: Bổ sung các translation keys `settings.key_configured`, `settings.configured`, `settings.key_saved_hint` cho cả tiếng Việt và tiếng Anh.
  2. **`admin-settings.component.html`**:
     - Thêm Badge xanh nổi bật `[✔ Đã cấu hình]` cạnh nhãn của Moralis API Key, Binance API Key và Binance API Secret khi key đã có trong hệ thống.
     - Cập nhật placeholder hiển thị `•••••••••••••••• (Đã lưu & bảo mật — để trống nếu giữ nguyên)` khi key đã được thiết lập.
     - Bổ sung dòng chú thích màu xanh lá ngọc `✔ Key/Secret đã được lưu & mã hóa an toàn trong hệ thống. Để trống nếu giữ nguyên.` ngay dưới ô nhập liệu.
  ## [2026-07-22] Chuẩn hóa logic kho hàng: Sản phẩm mới import từ Nhà cung cấp mặc định Khả dụng = 0
- **Yêu cầu & Quy tắc nghiệp vụ:**
  1. Tất cả sản phẩm (kể cả sản phẩm đồng bộ từ Nhà cung cấp ngoài) mặc định khi mới import về chưa được Admin nhập kho local thì **luôn có số lượng khả dụng là 0 (Hết hàng)**.
  2. Không được tự động lấy số lượng ảo từ Nhà cung cấp (`supplier_stock`) làm số khả dụng trên Storefront để tránh lỗi người dùng mua sản phẩm chưa có dữ liệu tài nguyên số.
  3. Chỉ khi Admin vào **Quản lý kho hàng -> Nhập kho** tạo lô hàng số cho sản phẩm đó thì số lượng khả dụng mới tăng lên đúng bằng số lượng key thực tế trong kho local.
- **Thực hiện khắc phục:**
  1. **`ProductRepository.php`**: Cập nhật `getAvailableCount` tính số lượng khả dụng dựa trên 100% các lô hàng số đang hoạt động (`activeBatches`) trong kho local cho tất cả sản phẩm.
  2. **`OrderService.php`**: Yêu cầu rút key thực tế từ kho local khi tạo đơn hàng. Nếu kho local hết key sẽ báo lỗi không đủ tài nguyên số rõ ràng.
  3. **`storefront.component.ts`**: Bổ sung `this.state.productStore.clearCache()` khi chuyển tới Storefront để luôn fetch dữ liệu kho tươi mới nhất từ server.
  4. **Xóa dữ liệu test**: Đã dọn dẹp toàn bộ các lô hàng test tạm thời trong CSDL.
  5. **Xác minh**: Chạy `php artisan test` vượt qua 47/47 unit & feature tests. Chạy `npm run build` biên dịch Angular thành công 100%.

## [2026-07-22] Chuyển đổi Quy trình Mua ngay tại chỗ (Buy Now) & Bỏ Nút Giỏ hàng Header
- **Yêu cầu & Quy tắc nghiệp vụ:**
  1. Gỡ bỏ nút Giỏ hàng trên thanh Header Menu (không cản trở thao tác khác).
  2. Thay thế toàn bộ các nút "Thêm vào giỏ" thành nút "Mua ngay" trên Storefront (Grid view, Table view, Featured list view).
  3. Bấm "Mua ngay" trên sản phẩm sẽ tự động gán duy nhất sản phẩm được chọn vào đơn và mở ngay Modal "Xác nhận đơn hàng của bạn" tại chỗ.
  4. Loại bỏ lưu trữ giỏ hàng trong `localStorage`, không lưu thông tin đơn cũ khi reload/F5 trang.
  5. Gỡ bỏ nút Xóa sản phẩm (thùng rác) và màn hình Giỏ hàng trống khỏi Modal Xác nhận đơn hàng vì đơn luôn mua 1 sản phẩm trực tiếp.
- **Thực hiện khắc phục & Cập nhật:**
  1. **`header.component.html` & `header.component.ts`**: Gỡ bỏ nút biểu tượng giỏ hàng và các hàm `openCart()` thừa.
  2. **`cart.store.ts`**: Bỏ `effect` lưu `localStorage` và `loadFromStorage()`, bổ sung hàm `setSingleProduct()` hỗ trợ mua tại chỗ.
  3. **`storefront.component.html` & `storefront.component.ts`**: Cập nhật 3 vị trí nút sang "Mua ngay" và gán hàm `buyNow()` mở trực tiếp `CartModalComponent`.
  4. **`cart-modal.component.html` & `cart-modal.component.ts`**: Xóa nút icon thùng rác, xóa khối HTML `Giỏ hàng trống`, giới hạn nút `-` không giảm xuống dưới 1 và nút `+` không vượt quá tồn kho. Dọn dẹp import `TooltipDirective`.
  5. **`language.service.ts`**: Bổ sung các từ khóa dịch thuật `'action.buy_now'`, `'cart.confirm_order_title'`, `'storefront.please_login_to_buy'` cho cả tiếng Việt và tiếng Anh.
  6. **Xác minh**: Chạy `npm run build` thành công 100% không phát sinh lỗi biên dịch.

## [2026-07-22] Sửa Bug Cache Lịch sử đơn hàng & Chuẩn hóa hiển thị Dữ liệu thô (Raw Data)
- **Yêu cầu & Quy tắc nghiệp vụ:**
  1. Khi thanh toán thành công redirect sang trang Lịch sử đơn hàng, phải làm sạch cache để tải ngay đơn hàng vừa tạo.
  2. Bỏ hoàn toàn việc parse bóc tách tài khoản/mật khẩu/2FA secret và nút Lấy OTP ở chi tiết đơn hàng. Hiển thị 100% dữ liệu thô (Raw data) của từng tài nguyên kèm nút Sao chép.
- **Thực hiện khắc phục & Cập nhật:**
  1. **`cart-modal.component.ts` & `orders.component.ts`**: Gọi `this.state.orderStore.clearCache()` khi thanh toán thành công và khi khởi tạo `OrdersComponent` để làm mới danh sách đơn hàng lập tức.
  2. **`order-detail.component.html` & `order-detail.component.ts`**: Gỡ bỏ hàm `parseAccountData()`, `get2FAOtp()`, TOTP utils, và 2 tab switcher. Thiết kế lại phần hiển thị tài nguyên phân phối hiển thị trực tiếp dữ liệu thô (`acc.data`) dạng font monospace, bôi đen `select-all` kèm nút Sao chép cho từng tài nguyên.

## [2026-07-22] Khắc phục Lỗi tính Tồn kho Khả dụng & Bổ sung Key i18n Status
- **Yêu cầu & Quy tắc nghiệp vụ:**
  1. Khắc phục lỗi khi admin cập nhật kho hàng nhưng Storefront báo "Khả dụng: 0".
  2. Bổ sung key i18n cho trạng thái badge tránh hiển thị `status.inactive`.
- **Thực hiện khắc phục & Cập nhật:**
  1. **`ProductRepository.php` & `InventoryBatchRepository.php`**: Sửa `getAvailableCount` và `findActiveBatchesByProductId` để tính tất cả các lô hàng thực tế còn key (`initial_count > sold_count` và `status != 'inactive'`), không còn bị bỏ qua do cờ `'completed'` tự động.
  2. **`InventoryService.php`**: Khi admin thêm/sửa lô hàng có key khả dụng, tự động đưa cờ status về `'active'` và làm sạch backend Cache.
  3. **`InventoryStore.ts`**: Tự động làm sạch `productStore` cache khi có thao tác nhập/sửa kho hàng.
  4. **`language.service.ts`**: Bổ sung key dịch `status.inactive` ('Tắt / Vô hiệu' / 'Inactive') và `status.pending` ('Chờ xử lý' / 'Pending') cho cả tiếng Việt và tiếng Anh, loại bỏ khai báo duplicate.
  5. **Xác minh**: `php artisan test` vượt qua 47/47 tests, `npm run build` biên dịch thành công 100%.

## [2026-07-22] Sửa nhãn hiển thị "SẢN PHẨM" thay vì "DANH MỤC SẢN PHẨM" tại trang Quản lý kho hàng
- **Yêu cầu & Quy tắc nghiệp vụ:**
  1. Tại trang Quản lý kho hàng (`/admin/inventory`) và các Modal Import / Edit kho hàng, danh sách lựa chọn trong dropdown là các **Sản phẩm cụ thể** (ví dụ: `BM50$ Ngâm`, `Full Via Limit 200$`), không phải là **Danh mục sản phẩm** (như `Tài khoản FB`, `Mã giảm giá`).
  2. Do đó nhãn tiêu đề của ô filter và ô chọn trong Modal phải hiển thị là **"SẢN PHẨM"** (hoặc option "Chọn sản phẩm"), tránh gây hiểu nhầm với "DANH MỤC SẢN PHẨM".
- **Thực hiện khắc phục & Cập nhật:**
  1. **`language.service.ts`**: Bổ sung key dịch `'common.product': 'Sản phẩm'` (EN: `'Product'`) và `'modal.select_product': 'Chọn sản phẩm'` (EN: `'Select Product'`).
  2. **`inventory.component.html` & `inventory.component.ts`**: Đổi nhãn bộ lọc thành `'common.product'` ("SẢN PHẨM") và option mặc định thành `'modal.select_product'` ("Chọn sản phẩm").
  3. **`import-inventory-modal.component.html` & `import-inventory-modal.component.ts`**: Đổi nhãn trường thành `'common.product'` và option mặc định thành `'modal.select_product'`.
  4. **`edit-inventory-modal.component.html`**: Đổi nhãn trường thành `'common.product'`.
  5. **Xác minh**: Chạy `npm run build` biên dịch Angular thành công 100%.

## [2026-07-22] Bổ sung tính năng Lazy Load / Infinite Scroll khi cuộn danh sách cho CustomSelectComponent
- **Yêu cầu & Ý tưởng:**
  - Tối ưu hóa trải nghiệm Select UI khi số lượng bản ghi lớn (trên 50, 100+ items).
  - Ban đầu hiển thị 50 bản ghi. Khi người dùng cuộn danh sách xuống 50% độ dài, tự động tải thêm 50 bản ghi tiếp theo (50 -> 100 -> 150... cho đến khi hiển thị hết).
- **Thực hiện triển khai:**
  1. **`custom-select.component.ts`**:
     - Thêm `displayLimit` dạng signal (mặc định = `maxDisplay` = 50).
     - Bổ sung hàm `onDropdownScroll(event: Event)` phát hiện ngưỡng cuộn `scrollTop + clientHeight >= scrollHeight * 0.5`. Tự động tăng `displayLimit` thêm `maxDisplay` nếu còn bản ghi chưa render.
     - Reset `displayLimit` về 50 khi người dùng đóng/mở lại dropdown hoặc gõ từ khóa tìm kiếm mới (`onSearchInput`).
     - Cập nhật `displayedOptions` computed signal để cắt mảng theo `displayLimit()` thay vì `maxDisplay` tĩnh.
  2. **`custom-select.component.html`**:
     - Gắn sự kiện `(scroll)="onDropdownScroll($event)"` vào container cuộn của dropdown.
     - Cập nhật thông số `visible` truyền cho `common.results_summary` thành `displayedOptions().length`.
  3. **`language.service.ts`**:
     - Cập nhật thông điệp `common.results_summary`: `"Hiển thị {{visible}} trên {{total}} kết quả. Cuộn xuống hoặc gõ từ khóa để tìm thêm..."`.
  4. **Xác minh**: Chạy `npm run build` biên dịch Angular thành công 100%.


## [2026-07-23] Bổ sung trường UID (8 ký tự duy nhất) cho bảng users
- **Phân tích & Yêu cầu:**
  - Người dùng muốn bổ sung mã User (`uid`) 8 ký tự dạng chữ thường + số (ví dụ: `abcd1234`), duy nhất và tự động tăng tiến dựa trên mốc thời gian timestamp để thay thế cho chuỗi UUID7 quá dài và rắc rối trên giao diện.
- **Thực hiện triển khai:**
  1. **Backend Laravel (`isocalmarket-api`)**:
     - `UserModel.php`: Thêm `uid` vào `#[Fillable]`, xây dựng hàm `generateUniqueUid()` sinh 5 ký tự Base36 từ timestamp milisecond (`microtime(true)*1000`) + 3 ký tự ngẫu nhiên chữ thường trong vòng lặp `do-while` kiểm tra DB. Thêm sự kiện `static::creating` tự động sinh `uid` cho mọi user mới.
     - `2026_07_23_000000_add_uid_to_users_table.php`: Migration tạo cột `uid` (`string('uid', 8)->unique()`) và backfill `uid` duy nhất cho toàn bộ user hiện có trong CSDL.
     - `User.php` Domain Entity & `UserMapper.php`: Bổ sung thuộc tính `uid`, getter `getUid()`, và chuyển đổi thuộc tính giữa Eloquent & Domain.
     - `UserRepositoryInterface.php` & `UserRepository.php`: Bổ sung `findByUid()` và cập nhật tìm kiếm theo `uid` trong `findAllPaginated`.
     - `AuthController.php`, `AdminUserController.php`: Bổ sung trường `uid` vào JSON response của tất cả các API liên quan đến user (`register`, `login`, `me`, `admin users list/store/update/adjust-balance`).
  2. **Frontend Angular (`isocalmarket-web`)**:
     - `admin-users.component.html`: Cập nhật bảng Quản lý User để ưu tiên hiển thị `#{{ row.uid }}` dạng badge monospace nổi bật thay cho đoạn UUID7 rắc rối.
  3. **Xác minh**:
     - Chạy `php artisan migrate` thành công 100%.
     - Chạy `php artisan test` vượt qua **47/47 unit & feature tests** (189 assertions).
     - Chạy `npm run build` biên dịch Angular thành công 100%.








