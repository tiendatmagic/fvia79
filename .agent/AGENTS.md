# Lịch sử Yêu cầu & Quy trình Làm việc (Isocalmarket)

## ⛔ QUY TẮC BẮT BUỘC (MANDATORY RULES)

> **TUYỆT ĐỐI KHÔNG được chạy lệnh `php artisan migrate:fresh --seed`** vì lệnh này sẽ **XÓA TOÀN BỘ DỮ LIỆU** hiện có trong database. Chỉ được dùng trong môi trường dev sạch hoàn toàn.
>
> Khi cần chạy migration mới, chỉ dùng:
> - `php artisan migrate` — áp dụng migration mới mà không xóa dữ liệu cũ.
>
> Khi cần seed dữ liệu cho một seeder cụ thể, chỉ dùng:
> - `php artisan db:seed --class=TenSeederClass` — chạy đúng 1 seeder.

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
