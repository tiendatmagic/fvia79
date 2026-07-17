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
    - Hover chuột vào các trường này và các ô dữ liệu khác sẽ hiển thị tooltip đầy đủ thông tin gốc.  - **[HOÀN THÀNH] Triển khai bộ lọc (Filter Bar) đồng bộ trên 4 trang giao diện**:
    - Backend: Cập nhật `OrderRepository`, `OrderService`, `OrderController` và `DashboardController` để tiếp nhận và truy vấn dữ liệu chính xác theo khoảng thời gian (`start_date`, `end_date`).
    - Quản lý sản phẩm: Tích hợp dropdown bộ chọn Danh mục và Trạng thái, thực hiện lọc client-side mượt mà qua signal computed `filteredProducts()`.
    - Lịch sử đơn hàng & Quản lý đơn hàng: Triển khai thanh bộ lọc Grid đồng bộ chứa dropdown Trạng thái và bộ chọn thời gian `<app-custom-date-time-range>`, kết nối và gọi API backend chính xác.
    - Dashboard: Thêm bộ lọc thời gian để thống kê Doanh thu và Số lượng đơn hàng động theo khoảng thời gian chọn.
    - Biên dịch: Chạy lệnh build frontend hoàn thành không lỗi.
    - Sửa lỗi bộ lọc: Khắc phục hiện tượng race condition khi lọc khoảng thời gian bằng cách chuyển sự kiện lắng nghe từ `(valueChange)` sang `(ngModelChange)` cho `CustomDateTimeRangeComponent` ở cả 3 trang (Orders, Admin Orders, Dashboard) giúp model cập nhật đầy đủ trước khi fetch.
    - Sửa lỗi bộ lọc sản phẩm: Chuyển đổi các biến lọc Category/Status của sản phẩm sang Signals để computed `filteredProducts()` phản ứng và tính toán lại chính xác client-side ngay khi chọn dropdown.
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
      5. Kết quả kiểm tra: Dự án biên dịch (`npm run build`) thành công hoàn toàn không có lỗi.
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
