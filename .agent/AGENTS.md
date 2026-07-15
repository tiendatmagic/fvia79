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
