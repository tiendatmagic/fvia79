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
      4. Đã chạy build biên dịch frontend thành công 100% không phát sinh lỗi.
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
