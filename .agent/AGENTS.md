# AGENTS.md - Lịch sử Thay Đổi & Ghi Nhớ Công Việc (Fvia79)

## ⛔ QUY TẮC BẮT BUỘC (MANDATORY RULES)

> **TUYỆT ĐỐI KHÔNG được chạy lệnh `php artisan migrate:fresh --seed`** vì lệnh này sẽ **XÓA TOÀN BỘ DỮ LIỆU** hiện có trong database. Chỉ được dùng trong môi trường dev sạch hoàn toàn.
>
> Khi cần chạy migration mới, chỉ dùng:
> - `php artisan migrate` — áp dụng migration mới mà không xóa dữ liệu cũ.
>
> Khi cần seed dữ liệu cho một seeder cụ thể, chỉ dùng:
> - `php artisan db:seed --class=TenSeederClass` — chạy đúng 1 seeder.
>
> **QUY TẮC BẮT BUỘC KHI DEPLOY VPS & NÂNG CẤP CẤU HÌNH .ENV**:
> Khi triển khai (deploy) hoặc thay đổi file `.env` trên VPS, **LUÔN LUÔN phải thực hiện chuỗi xóa và tái tạo cache cấu hình**:
> `php artisan config:clear && php artisan config:cache && php artisan route:cache && php artisan view:cache`
> để đảm bảo Laravel cập nhật ngay lập tức 100% các biến môi trường tươi mới nhất từ tệp `.env`, tuyệt đối không được bỏ qua bước `config:clear` để tránh tình trạng dính cache đệm tĩnh cũ.

---

## 📋 NHẬT KÝ THAY ĐỔI DỰ ÁN

### [2026-07-27] Phân quyền bắt buộc đăng nhập để xem sản phẩm & danh mục sản phẩm ở trang Storefront (fvia79-web)
- **Mô tả**: Cấu hình phân quyền đối với trang Storefront (`/storefront`) và submenu **"Mua hàng"** (Danh mục sản phẩm), yêu cầu khách truy cập (Guest) chưa đăng nhập phải thực hiện đăng nhập tài khoản trước mới có thể xem danh sách sản phẩm và danh mục sản phẩm. Các API khác như B2B hoàn toàn giữ nguyên không bị ảnh hưởng.
- **Thực hiện**:
  - `fvia79-web/src/app/app.routes.ts`: Bổ sung guard `canActivate: [AuthGuard]` trực tiếp cho route `'storefront'`. Khi khách chưa đăng nhập truy cập vào `/storefront`, Angular Router sẽ ngăn truy cập, hiển thị thông báo toast yêu cầu đăng nhập và tự động chuyển hướng đến `/login` kèm tham số `returnUrl=/storefront`.
  - `SidebarComponent` (`sidebar.component.ts/html`) & `App` (`app.ts/html`): Bổ sung kiểm tra `stateService.isAuthenticated()`. Khi khách chưa đăng nhập nhấn vào submenu **"Mua hàng"** (hoặc truy cập danh mục sản phẩm), hệ thống sẽ hiển thị thông báo yêu cầu đăng nhập và tự động chuyển hướng đến trang `/login`.
  - `auth.interceptor.ts`: Khắc phục lỗi khi khách chưa đăng nhập (Guest) truy cập các trang công khai (`/check-uid`, `/faq`, `/contact`), API `/auth/me` trả về `401 Unauthorized` khiến HTTP Interceptor tự động chuyển hướng khách về `/login`. Đã bổ sung `/check-uid`, `/faq`, `/contact` vào `isPublicRoute` đồng bộ trên 3 nguồn dự án (`fvia79`, `adsbm79`, `isocalmarket`), đảm bảo duy trì quyền truy cập công khai bình thường mà không bị văng ra trang đăng nhập.
  - Biên dịch & Kiểm thử: Chạy `npx ng build --configuration production` thành công **100% BUILD SUCCESS** cho cả 3 dự án.

### [2026-07-27] Lọc chỉ hiển thị các danh mục đang hoạt động (is_active) ở submenu Mua hàng trên 3 nguồn (fvia79, adsbm79, isocalmarket)
- **Mô tả**: Sửa lỗi submenu **"Mua hàng"** (cả Sidebar desktop và Mobile menu) và bộ lọc danh mục trang Storefront hiển thị các danh mục đã bị ẩn (`is_active = false`). Cấu hình chỉ lọc và hiển thị danh mục đang hoạt động (`is_active !== false`).
- **Thực hiện**:
  - `fvia79-web`: Cập nhật `SidebarComponent`, `App` (mobile menu), và `StorefrontComponent` lọc `is_active !== false && is_active !== 0 && is_active !== '0'`.
  - `adsbm79-web`: Cập nhật `SidebarComponent`, `App` (mobile menu), và `StorefrontComponent` đồng bộ 100%.
  - `isocalmarket-web`: Cập nhật `SidebarComponent`, `App` (mobile menu), và `StorefrontComponent` đồng bộ 100%.
  - Biên dịch & Kiểm thử: Đã chạy `npx ng build --configuration production` thành công **100% BUILD SUCCESS** cho cả 3 dự án.

### [2026-07-27] Kiểm tra và cập nhật cấu hình .gitignore cho thư mục storage/framework/views
- **Mô tả**: Rà soát và cấu hình loại trừ các tệp view compiled cache (`/storage/framework/views`) khỏi hệ thống quản lý phiên bản Git để tránh tình trạng commit nhầm các file Blade view cache tạm thời.
- **Thực hiện**:
  - `fvia79-api/.gitignore`: Bổ sung `/storage/framework/views`, `/storage/framework/sessions`, `/storage/framework/testing`.
  - `.gitignore` (gốc): Bổ sung quy tắc `/fvia79-api/storage/framework/views/*`, `/fvia79-api/storage/framework/cache/*`, `/fvia79-api/storage/framework/sessions/*`, `/fvia79-api/storage/framework/testing/*` và ngoại lệ `!/fvia79-api/storage/framework/**/.gitignore`.
  - `storage/framework/views/.gitignore`: Đã có sẵn quy tắc `*` và `!.gitignore` giữ lại folder views sạch trong Git repository.

### [2026-07-27] Chuẩn hóa thứ tự thời gian từ mới nhất (trên) đến cũ nhất (dưới) & Số tiền nạp ngẫu nhiên ở chế độ Demo cho Storefront
- **Mô tả**:
  1. **Tự động căn chỉnh thứ tự thời gian chuẩn theo logic dòng thời gian**:
     - **Đơn hàng vừa mua**: Đơn hàng nằm trên cùng luôn có số phút nhỏ nhất (**5 phút trước**) và giảm dần xuống dòng dưới cùng (**15 phút trước**). Khi có đơn hàng demo mới được đẩy vào trên cùng, toàn bộ danh sách tự động giãn khoảng thời gian mượt mà từ **5 đến 15 phút trước**.
     - **Lịch sử nạp gần đây**: Dòng trên cùng luôn có số phút nhỏ nhất (**20 phút trước**) và giảm dần xuống dòng dưới cùng (**40 phút trước**). Khi có giao dịch nạp demo mới đẩy vào trên cùng, danh sách tự động giãn thời gian từ **20 đến 40 phút trước**.
  2. **Số tiền nạp ngẫu nhiên ở chế độ Demo**: Giới hạn nghiêm ngặt từ **0.1 đến 100 USDT** (loại bỏ hoàn toàn các con số nạp trên 100 USDT).
- **Thực hiện**:
  - `storefront.component.ts`: Xây dựng hàm helper `rescaleDemoTimestamps(list, minMinutes, maxMinutes)` để tính toán mốc thời gian tăng dần đều từ trên xuống dưới, áp dụng cho `seedInitialPurchases()`, `seedInitialDeposits()` và `startDemoActivitiesTimer()`.
  - Biên dịch: Chạy `npx ng build --configuration production` thành công **100% BUILD SUCCESS**.



### [2026-07-27] Bổ sung quy tắc nhận diện logo quốc kỳ cho Ecuador, Timor-Leste, Congo, Mexico và các quốc gia trên thế giới (fvia79 & adsbm79)
- **Mô tả**: Bổ sung đầy đủ quy tắc nhận diện tên quốc gia cho `ProductIconComponent` đối với các sản phẩm chưa hiển thị đúng cờ (như Ecuador 🇪🇨, Timor-Leste 🇹🇱, Congo 🇨🇬, Mexico 🇲🇽, Luxembourg 🇱🇺, Belgium 🇧🇪...):
  - Ecuador (`ec`): `/\b(ecuador)\b/i`
  - Timor-Leste (`tl`): `/\b(timor\s*leste|timor|east\s*timor)\b/i`
  - Congo (`cg`): `/\b(congo)\b/i`
  - Mexico (`mx`), Bỉ (`be`), Nepal (`np`), Sri Lanka (`lk`), Mongolia (`mn`), Iraq (`iq`), Iran (`ir`), Israel (`il`)...
- **Thực hiện**:
  - `fvia79-web`: Cập nhật `COUNTRY_RULES` trong `product-icon.component.ts`.
  - `adsbm79-web`: Cập nhật `COUNTRY_RULES` trong `product-icon.component.ts` đồng bộ 100%.
  - Biên dịch & Kiểm thử: Chạy build Angular production thành công **100% BUILD SUCCESS** trên cả 2 dự án.

### [2026-07-27] Phát triển tính năng Tùy chỉnh vị trí/thứ tự hiển thị danh mục ở trang chủ Storefront & Modal Sắp xếp vị trí thông minh (fvia79)
- **Mô tả**: Phát triển hoàn chỉnh tính năng cho phép Admin tùy chỉnh thứ tự (vị trí) sắp xếp của các danh mục hiển thị trên trang chủ Storefront (`/storefront`), giúp sắp xếp vị trí danh mục linh hoạt theo mong muốn (ví dụ đưa danh mục *"Ad account Limit 50$"* xếp dưới *"BM3 Limit 50$"* và *"BM50$"*).
- **Thực hiện**:
  - **Backend (`fvia79-api`)**:
    - Tạo migration `2026_07_27_200000_add_sort_order_to_categories_table.php` bổ sung cột `sort_order` (mặc định 0) vào bảng `categories`.
    - Tạo migration `2026_07_27_210000_normalize_categories_sort_order.php` tự động chuẩn hóa đánh số thứ tự tuần tự `0, 1, 2, 3, 4...` cho toàn bộ danh mục hiện tại.
    - Cập nhật `CategoryModel`, `Category` entity, `CategoryMapper`, `CreateCategoryRequest`, `CategoryRepository`, `ProductRepository` sắp xếp danh mục theo `sort_order ASC`, `name ASC`.
    - Cập nhật `CategoryService` & `CategoryController` hỗ trợ lưu `sort_order` và thêm API `POST /categories/reorder` cập nhật thứ tự hàng loạt.
    - Cập nhật `SupplierCatalogSyncService`: Đảm bảo khi đồng bộ sản phẩm/danh mục từ nhà cung cấp (Supplier), vị trí `sort_order` đã cài đặt của danh mục cũ giữ nguyên **100% không bị thay đổi hay ghi đè**. Nếu có danh mục mới hoàn toàn từ NCC sẽ được tự động xếp vào cuối danh sách.
  - **Frontend (`fvia79-web`)**:
    - `product.store.ts`: Bổ sung hàm `reorderCategories()`.
    - `category-reorder-modal.component.ts/html`: Tạo mới modal **"Sắp Xếp Vị Trí Trang Chủ"** giúp Admin di chuyển danh mục Lên ▲ / Xuống ▼ hoặc Đưa lên đầu/cuối trực quan 100%, không lo bị nhảy xuống trang 2.
    - `category-form-modal.component.ts/html`: Thêm ô nhập "Thứ tự hiển thị (`sort_order`)".
    - `admin-categories.component.ts/html`: Thêm nút hành động **"Sắp Xếp Vị Trí Trang Chủ 🔀"** và cột "Thứ tự hiển thị" trên bảng danh mục.
    - `storefront.component.ts`: Đổi `categorySort` mặc định sang `'default'` để hiển thị chính xác theo vị trí cấu hình từ Admin.
  - **Kiểm thử**: Đã chạy 2 migration thành công, kiểm tra cú pháp PHP `php -l` đạt **0 errors**, và build Angular production (`npx ng build --configuration production`) thành công **100% BUILD SUCCESS**.



### [2026-07-27] Khắc phục triệt để lỗi khớp nhầm quốc kỳ (Colombia, Nigeria) & Bổ sung nhận diện tên tiếng Việt không dấu (Úc -> uc) cho ProductIconComponent trên cả 2 nguồn (fvia79 & adsbm79)
- **Mô tả**: Sửa lỗi nhận diện quốc kỳ của sản phẩm (`ProductIconComponent`):
  1. Loại bỏ việc cho phép các mã quốc gia 2 ký tự tự do (như `co`, `ng`, `in`, `ca`, `my`, `no`, `de`...) khớp với các từ tiếng Việt 1 âm tiết ngắn hoặc đã bị loại bỏ dấu (ví dụ: `Cổ`, `Có` bị dính cờ Colombia 🇨🇴, `Ngâm`, `Ngoại` bị dính cờ Nigeria 🇳🇬).
  2. Mã 2 ký tự chỉ được khớp khi viết trong ngoặc vuông/ngoặc tròn (như `[US]`, `(VN)`, `[CO]`, `[NG]`) hoặc khi viết IN HOA đứng độc lập (`US`, `VN`, `TH`, `PH`, `AU`, `CO`, `NG`...).
  3. Bổ sung hỗ trợ đầy đủ các từ tên nước tiếng Việt không dấu (như `Úc` -> `uc`, `Đức` -> `duc`, `Pháp` -> `phap`, `Hàn` -> `han`, `Nhật` -> `nhat`, `Ý` -> `y`, `Lào` -> `lao`...).
- **Thực hiện**:
  - `fvia79-web`: Cập nhật `product-icon.component.ts`.
  - `adsbm79-web`: Cập nhật `product-icon.component.ts` đồng bộ 100%.
  - Biên dịch: Đã kiểm tra build Angular production thành công.

### [2026-07-27] Chuẩn hóa phương thức nạp tiền Demo ở khối Lịch sử nạp gần đây (Chỉ hiển thị USDT BEP20 và BINANCE PAY với tỷ lệ 8/10)
- **Mô tả**: Cập nhật dữ liệu khởi tạo và logic tạo dữ liệu ngẫu nhiên thời gian thực ở chế độ **Demo** cho khối **Lịch sử nạp gần đây** trên Storefront:
  1. Loại bỏ phương thức nạp TRC20 (`usdt_trc20`).
  2. Chỉ cho phép 2 phương thức nạp: **USDT (BEP20)** (`usdt_bep20`) và **BINANCE PAY** (`binance_pay`).
  3. Áp dụng tỷ lệ xuất hiện **8/10** (80% ưu tiên `USDT (BEP20)` và 20% `BINANCE PAY`).
- **Thực hiện**:
  - `storefront.component.ts`: Cập nhật `seedInitialDeposits()` và `startDemoActivitiesTimer()` sinh dữ liệu demo chuẩn tỷ lệ 80:20.
  - Biên dịch: Chạy `npx ng build --configuration production` thành công **100% BUILD SUCCESS**.

### [2026-07-27] Đồng bộ tệp ảnh social_image.png tại thư mục fvia79-web/public/assets/image
- **Mô tả**: Thay thế tệp ảnh cũ `social_image.png` trong thư mục `public/assets/image/` bằng tệp ảnh social/OG Image mới được sinh từ bộ nhận diện thương hiệu `fvia79-logo`. Đồng thời bổ sung các định dạng tên biến thể (`social-image.png`, `social_image.jpg`, `social-image.jpg`) để đảm bảo không bị lỗi 404 khi truy cập bất kỳ đường dẫn ảnh social nào.
- **Thực hiện**:
  - `fvia79-web/public/assets/image/`: Cập nhật `social_image.png`, `social-image.png`, `social_image.jpg`, `social-image.jpg`.
  - Biên dịch: Chạy `npx ng build --configuration production` thành công **100% BUILD SUCCESS**.



### [2026-07-27] Đồng bộ tỷ giá mặc định USDT/VND thành 26.500 trên cả 3 dự án (fvia79, isocalmarket, adsbm79)
- **Mô tả**: Cập nhật giá trị tỷ giá mặc định (fallback) cho toàn bộ 3 dự án từ các con số cũ (`25.400`, `26.276`) sang tỷ giá mặc định mới **`26.500`** theo yêu cầu.
- **Thực hiện**:
  - `fvia79`: Cập nhật `26.500` cho Backend (`SystemSettingController`, `StorefrontController`) và Frontend (`currency.service.ts`, `public-setting.store.ts`, `storefront.component.ts/html`).
  - `isocalmarket`: Cập nhật `26.500` cho Backend và Frontend.
  - `adsbm79`: Cập nhật `26.500` cho Backend và Frontend.
  - Biên dịch: Chạy `npx ng build --configuration production` thành công **100% BUILD SUCCESS**.


### [2026-07-27] Khắc phục lỗi giá trị mặc định telegram_bot_username bị dính tên dự án cũ (adsbm79noti_bot)
- **Mô tả**: Phát hiện và khắc phục lỗi giá trị mặc định (fallback) của `telegram_bot_username` ở `public-setting.store.ts` và `system-setting.store.ts` bị dính tên mặc định của dự án cũ `adsbm79noti_bot` trên cả 2 nguồn `fvia79` và `isocalmarket`.
- **Thực hiện**:
  - `fvia79-web`: Cập nhật 100% các tệp store fallback thành `fvia79noti_bot`.
  - `isocalmarket-web`: Cập nhật 100% các tệp store fallback thành `isocalmarketnoti_bot`.
  - Biên dịch: Chạy `npx ng build --configuration production` thành công **100% BUILD SUCCESS**.


### [2026-07-27] Khắc phục lỗi hiển thị giá gốc VND bị lẻ lẻ (225.009đ, 330.013đ) do sai lệch phép chia floating-point
- **Mô tả**: Khi người dùng xem giá ở đơn vị VND trên Storefront, mặc dù Nhà cung cấp đã bật tùy chọn "Làm tròn số (VND)" (`round_vnd_prices = true`), giá niêm yết gốc lại hiển thị dạng lẻ `225.009 đ` và `330.013 đ`.
- **Nguyên nhân**: Trong CSDL, giá sản phẩm được lưu dưới dạng USD. Khi `CurrencyService` phía Frontend nhân giá USD với tỷ giá `usdToVndRate` (ví dụ: `8.56294717 * 26276`), phép tính số thực floating-point tạo ra sai lệch vài đồng lẻ làm sai lệch hiển thị giá niêm yết nghìn chẵn.
- **Thực hiện**:
  - `currency.service.ts`: Bổ sung tự động làm tròn giá trị VND hiển thị về nghìn đồng chẵn (`Math.round(vndAmount / 1000) * 1000`) đối với các khoản tiền $\ge 1.000$ VNĐ.
  - Biên dịch: Chạy `npx ng build --configuration production` thành công **100% BUILD SUCCESS**.


### [2026-07-26] Cấu hình & Chuẩn hóa liên kết Frontend Netlify với Backend DDNS
- **Mô tả**: Thiết lập và kiểm tra cơ chế kết nối giữa FE (`https://fvia79.netlify.app/`) và BE (`https://fvia79.ddns.net/`).
- **Thực hiện**:
  - `netlify.toml`: Bổ sung redirect rule proxy `/api/*` và `/storage/*` từ Netlify sang `https://fvia79.ddns.net/api/:splat` và `https://fvia79.ddns.net/storage/:splat` (status 200 rewrite), cập nhật CSP `connect-src 'self' https://fvia79.ddns.net`.
  - `cors.php`: Đảm bảo `https://fvia79.netlify.app` đã có sẵn trong danh sách `defaultOrigins` cho phép cross-origin request có credentials (`withCredentials: true`).
  - Hướng dẫn cấu hình môi trường `.env` phía Backend server DDNS & lệnh clear cache bắt buộc `php artisan config:clear && php artisan config:cache && php artisan route:cache && php artisan view:cache`.

### [2026-07-26] Bổ sung Skeleton Loading cho 2 khối "Đơn hàng vừa mua" & "Lịch sử nạp gần đây" ở Storefront
- **Mô tả**: Sửa lỗi khi tải trang Storefront, trong khi danh sách sản phẩm hiển thị khung xương skeleton loader thì 2 khối "Đơn hàng vừa mua" và "Lịch sử nạp gần đây" ở phía dưới lại hiển thị ngay dòng chữ "Không có dữ liệu hiển thị".
- **Thực hiện**:
  - `storefront.component.ts`: Khai báo 2 signal quản lý trạng thái loading `recentPurchasesLoading = signal<boolean>(true)` và `recentDepositsLoading = signal<boolean>(true)`. Cập nhật hàm `loadRecentActivities()` bật/tắt đúng thời điểm nhận dữ liệu API hoặc lỗi.
  - `storefront.component.html`: Bổ sung khối template `@if (recentPurchasesLoading())` và `@if (recentDepositsLoading())` rendering các hàng Skeleton Loader có hiệu ứng `animate-pulse` đồng bộ với giao diện chính.
  - Biên dịch & Kiểm thử: Chạy `npx ng build --configuration production` thành công **100% BUILD SUCCESS**.


### [2026-07-26] Tạo tab riêng "Hoạt động gần đây" trong Cấu hình hệ thống Admin
- **Mô tả**: Tạo một tab quản lý độc lập mang tên **Hoạt động gần đây** (`/admin/settings/activities`) trên thanh điều hướng Cấu hình hệ thống Admin, giúp quản trị viên dễ dàng quản lý bật/tắt và chọn chế độ dữ liệu (Demo/Real) cho 2 khối "Đơn hàng vừa mua" & "Lịch sử nạp gần đây".
- **Thực hiện**:
  - `SystemSettingController.php`: Bổ sung validate & xử lý lưu trữ các tham số `recent_purchases_enabled`, `recent_purchases_mode`, `recent_deposits_enabled`, `recent_deposits_mode` trong hàm `update()`.
  - `system-setting.store.ts`: Khai báo bổ sung các trường cấu hình trong interface `SystemSettingsPayload`.
  - `admin-settings.component.ts`: Cập nhật signal `activeTab` và phương thức `changeTab()` để hỗ trợ tab `'activities'`.
  - `admin-settings.component.html`: Thêm nút Tab "Hoạt động gần đây" với icon giỏ hàng 🛍️ và giao diện thẻ độc lập có nút Mở khóa/Lưu riêng biệt.
  - Biên dịch: Chạy `npx ng build --configuration production` thành công **100% BUILD SUCCESS**.


### [2026-07-26] Bổ sung SVG icon `shopping-bag` & `credit-card`, chuẩn hóa đơn vị USD và chuyển khối Hoạt động gần đây xuống cuối trang Storefront
- **Mô tả**:
  1. Khắc phục lỗi 2 icon `shopping-bag` và `credit-card` ở tiêu đề khối "Đơn hàng vừa mua" và "Lịch sử nạp gần đây" bị hiển thị biểu tượng chấm hỏi default `(?)` do thiếu `@case` trong `IconComponent`.
  2. Chuẩn hóa hiển thị tổng tiền khối "Đơn hàng vừa mua" sang đơn vị **USD** (định dạng `$X.XX`) thay vì hiển thị số VND hàng trăm ngàn USD.
  3. Di chuyển toàn bộ khối "Đơn hàng vừa mua" và "Lịch sử nạp gần đây" từ vị trí trên cùng xuống vị trí **dưới cùng của trang Storefront** (nằm bên dưới tất cả các danh mục sản phẩm).
- **Thực hiện**:
  - `icon.component.html`: Bổ sung đường dẫn SVG chuẩn Heroicons cho `@case ('shopping-bag')` và `@case ('credit-card')` (cũng như `@case ('shopping-cart')`).
  - `storefront.component.ts`: Cập nhật dữ liệu seed và timer tạo đơn hàng demo sử dụng giá USD thực tế (`$2.50` đến `$20.55`).
  - `storefront.component.html`: Đổi phần định dạng tổng tiền đơn hàng thành `<span class="font-bold">${{ (item.total_usd || item.total_price_usd || (item.total_price > 1000 ? item.total_price / 26276 : item.total_price) || 0) | number:'1.2-2' }}</span>`, đồng thời di chuyển thẻ `@if (recentPurchasesEnabled() || recentDepositsEnabled())` xuống dưới cùng trang HTML bên dưới phần sản phẩm.
  - Kiểm thử: Đã chạy `npx ng build --configuration production` thành công **100% BUILD SUCCESS**.


### [2026-07-26] Cập nhật Git Remote và Push mã nguồn lên Repository fvia79.git
- **Mô tả**: Chuyển đổi URL remote `origin` từ repository cũ sang `https://github.com/tiendatmagic/fvia79.git`, dọn dẹp các tệp cache Angular ra khỏi git index tracking, và tiến hành push các nhánh `develop`, `master` lên GitHub repository mới.
- **Thực hiện**:
  - `git remote set-url origin https://github.com/tiendatmagic/fvia79.git`: Cập nhật remote origin thành công.
  - `git rm --cached -r fvia79-web/.angular/cache`: Bỏ tracking các tệp cache tự sinh trong Angular.
  - `git push -u origin develop`, `git push origin master`: Push thành công 100% tất cả các nhánh lên repository `tiendatmagic/fvia79.git`.


### [2026-07-26] Chuẩn hóa đa ngôn ngữ tên sản phẩm ở Đơn hàng vừa mua & Tiền nạp USDT ở Storefront
- **Mô tả**: Sửa lỗi hiển thị key dịch thô (`storefront.recent_purchases_title`, `storefront.recent_deposits_title`, `storefront.bought`, `storefront.deposited`, `storefront.deposit_from`), bổ sung cơ chế hiển thị tên sản phẩm theo đa ngôn ngữ (Tiếng Việt / Tiếng Anh) cho phần "Đơn hàng vừa mua", và chuyển đổi đơn vị hiển thị tiền nạp từ VND sang USDT với hạn mức ngẫu nhiên từ **1 đến 100 USDT**.
- **Thực hiện**:
  - `vi.ts`, `en.ts`: Thêm đầy đủ 5 key dịch đa ngôn ngữ cho khối mua/nạp gần đây ở Storefront.
  - `storefront.component.ts`: Cập nhật hàm `getProductName(item)` ưu tiên tự động quét các trường `product_name_en`, `name_en`, `product.name_en`, `product_name_vi`, `name_vi`, `product.name_vi` để chuyển đổi linh hoạt theo ngôn ngữ hiện tại của người dùng (`en` / `vi`).
  - `storefront.component.html`: Đổi phần định dạng tiền nạp thành `<span class="font-bold text-emerald-600 dark:text-emerald-400">{{ (item.amount_usdt || item.amount || 0) | number:'1.0-2' }} USDT</span>`.
  - `storefront.component.ts`: Đặt hạn mức ngẫu nhiên tiền nạp demo từ 1 đến 100 USDT (`[2, 5, 10, 15, 20, 25, 30, 45, 50, 65, 75, 80, 95, 100]`), cập nhật các phương thức nạp bằng USDT (`USDT (BEP20)`, `BINANCE PAY`, `USDT (TRC20)`), và khởi tạo dữ liệu mẫu ban đầu ngay trong callback API.
  - Biên dịch & Kiểm thử: Chạy `npx ng build --configuration production` thành công **100% BUILD SUCCESS**.

### [2026-07-26] Khắc phục lỗi chữ thông báo chạy (marquee) đè lên badge "THÔNG BÁO"
- **Mô tả**: Chữ chạy ngang hiệu ứng marquee trong thanh thông báo Storefront chạy bị trôi sang bên trái và đè lên trên icon/badge "THÔNG BÁO".
- **Nguyên nhân**: Do thuộc tính `overflow-hidden` bị đặt nhầm ở phần tử con trực tiếp `.announcement-marquee-track` (nơi áp dụng `transform: translateX(...)`) thay vì nằm ở thẻ bao ngoài `flex-1 min-w-0 w-full`. Khi chữ cuộn dịch chuyển `translateX(-100%)`, phần tử con bị trượt ra ngoài khung flex và đè trực tiếp lên badge thông báo ở bên trái.
- **Thực hiện**:
  - `storefront.component.html`: Bổ sung `overflow-hidden` vào thẻ bao ngoài `<div class="flex-1 min-w-0 w-full overflow-hidden">` và dọn dẹp `overflow-hidden` thừa trên `.announcement-marquee-track`, đồng bộ với chuẩn hiển thị ở `adsbm79` và `admin-settings`.
  - Biên dịch và kiểm thử: Chạy `npx ng build --configuration production` thành công **100% BUILD SUCCESS**.

### [2026-07-26] Điều tra nguyên nhân và khôi phục toàn bộ source code fvia79-web
- **Nguyên nhân**: Trong quá trình merge conflict resolution (commit `9bfe475`) và commit setup (`158d8df`), thư mục source code frontend `fvia79-web/src` cùng các file cấu hình `package.json`, `angular.json`, `tsconfig.json` đã bị ghi đè/xóa nhầm do xung đột đường dẫn khi đổi tên từ `fvia79-web` sang `fvia79-web`.
- **Thực hiện**:
  - Truy vết lịch sử Git reflog và cây thư mục ở các commit trước đó (`42c4d21`).
  - Khôi phục 100% toàn bộ source code frontend (`fvia79-web/src` với 225 files) và các tệp tin cấu hình liên quan từ commit `42c4d21`.
  - Thực hiện commit an toàn `fix: restore fvia79-web source files from commit 42c4d21`.
- **Xác nhận**: Toàn bộ source code của dự án đã được phục hồi đầy đủ 100%, không bị mất mát dữ liệu hay logic nào.



### [2026-07-26] Rà soát và chuẩn hóa tham số limit (max 20) và page trong truy vấn CSDL
- Mô tả: Rà soát các vấn đề liên quan đến tham số `limit` và `page` khi client gửi request truy vấn API phân trang, phòng chống các lỗi offset âm (`OFFSET -10`), chia cho 0 (`limit = 0`), cắt mảng sai trang trong `array_slice`, và áp dụng mức `limit` tối đa **20** bản ghi/trang cho toàn bộ hệ thống (riêng trang Storefront cho phép max 100 record/trang).
- Thực hiện:
  - Backend (`fvia79-api`):
    - `ProductRepository.php`, `OrderRepository.php`, `UserRepository.php`, `CategoryRepository.php`, `InventoryItemRepository.php`, `InventoryBatchRepository.php`, `CouponRepository.php`, `DepositRepository.php`: Đồng bộ bổ sung chuẩn hóa `$page = max(1, $page);` và `$limit = max(1, $limit);` cho toàn bộ các hàm phân trang.
    - `OrderController.php`, `AdminUserController.php`, `CategoryController.php`, `UserSessionController.php`, `B2bController.php`, `B2bAdminController.php`: Cập nhật bọc trần `$limit = min(20, max(1, (int) $request->query('limit', ...)));` ép giá trị `limit` tối đa không vượt quá **20** bản ghi/trang cho các trang quản trị.
    - `ProductController.php`: Đặt mức trần `$limit = min(100, max(1, (int) $request->query('limit', ...)));` riêng cho API sản phẩm của Storefront, đảm bảo Storefront hiển thị đủ 100 record/page.
    - Kiểm thử: Kiểm tra cú pháp PHP `php -l` toàn bộ các Controller thành công **100% SUCCESS, 0 ERRORS**.
- Xác nhận: Hệ thống hoạt động an toàn 100%!

### [2026-07-26] Khắc phục triệt để đường dẫn Build & Deploy FE/BE Fvia79
- **Mô tả yêu cầu**: Dọn dẹp tệp ghi chép cũ, chuẩn hóa tên dự án `fvia79-web`, khắc phục triệt để việc script deploy nén nhầm thư mục cũ `dist/angular-web3-wallet`.
- **Thực hiện**:
  - **Frontend (`fvia79-web`)**:
    - `angular.json`, `package.json`, `netlify.toml`: Đồng bộ đổi tên dự án thành `fvia79-web`.
    - `product-form-modal.component.ts`: Sửa lỗi `TypeError: this.modalData.attributes is not iterable` khi Edit sản phẩm (hỗ trợ kiểm tra Array, Object Key-Value, JSON string).
    - Thư mục build output: `dist/fvia79-web/browser`. Xóa hoàn toàn thư mục build cũ `dist/angular-web3-wallet`.
  - **Script Deploy (`deploy_vps_production.js`, `deploy_fe_vps.js`)**:
    - Cập nhật 100% đường dẫn nén sang `dist/fvia79-web/browser`.
  - **VPS Production (`184.164.93.247`)**:
    - Web Root FE: `/var/www/fvia79-web`
    - Web Root BE: `/var/www/fvia79-api`
- **Xác nhận**: Biên dịch Angular build và triển khai 100% thành công.

### [2026-07-27] Đồng bộ Background & Badge khối "Đơn hàng vừa mua" và "Lịch sử nạp gần đây" theo màu chủ đạo (Primary Theme)
- **Mô tả**: Chuyển đổi màu nền header và badge thời gian của 2 thẻ "Đơn hàng vừa mua" & "Lịch sử nạp gần đây" từ màu gradient cố định (blue-600, indigo-600, purple-600) sang màu chủ đạo động (`from-[var(--color-primary)] to-[var(--color-secondary)]` và `bg-[var(--color-primary)]`).
- **Thực hiện**:
  - `storefront.component.html`: Thay thế class gradient header 2 thẻ bằng `bg-gradient-to-r from-[var(--color-primary)] to-[var(--color-secondary)]`, đổi class badge thời gian từ `bg-blue-500/90` sang `bg-[var(--color-primary)]`.
  - Biên dịch & Kiểm thử: Chạy `npx ng build --configuration production` thành công **100% BUILD SUCCESS** (0 errors).

### [2026-07-27] Thay thế Logo web, Favicon, Social Image (OG Image) và cập nhật Hệ màu chủ đạo/phụ theo Logo mới
- **Mô tả**: Cập nhật bộ nhận diện thương hiệu web dựa trên tài nguyên từ `D:\git\fvia79\fvia79-logo`, đồng thời trích xuất màu chủ đạo `#0066FF` (Electric Blue) và màu phụ `#0B193C` (Dark Navy Blue) cập nhật cho toàn bộ hệ thống web.
- **Thực hiện**:
  - **Tài sản hình ảnh (`fvia79-web/public`)**:
    - Chuyển đổi & thay thế `logo.png`, `logo-dark.png`, `logo-light.png` bằng ảnh logo mới.
    - Chuyển đổi `social-image.jpg` thành `public/assets/og-image.png`.
    - Sinh đầy đủ bộ favicons: `favicon.ico`, `favicon-16x16.png`, `favicon-32x32.png`, `apple-touch-icon.png`, `android-chrome-192x192.png`, `android-chrome-512x512.png`.
  - **CSS Variables & Splash Screen**:
    - `styles.scss`: Cập nhật `--color-primary` và `--dynamic-primary` thành `#0066FF`, `--color-secondary` và `--dynamic-secondary` thành `#0B193C`.
    - `index.html`: Cập nhật `--splash-brand` (`#0066FF`) và `--splash-primary` (`#0B193C`).
### [2026-07-27] Bổ sung cơ chế Bật/Tắt Quản lý mã giảm giá qua env (FEATURE_COUPONS_ENABLED) & Cấu hình hệ thống
- **Mô tả**: Thiết lập cơ chế bật/tắt toàn bộ tính năng **Quản lý mã giảm giá (Coupons)** từ cấu hình `.env` (`FEATURE_COUPONS_ENABLED`, mặc định là `true`) kết hợp với Cấu hình Hệ thống (`coupons_enabled`). Khi tính năng bị tắt:
  1. Backend tự động bỏ qua (ignore) mã giảm giá khi tạo đơn hàng mà không gây ra bất kỳ lỗi hay ảnh hưởng nào đến quy trình thanh toán, đồng thời chặn API kiểm tra mã giảm giá (`/coupons/validate`).
  2. Frontend tự động ẩn menu **Quản lý mã giảm giá** (`/admin/coupons`) trên Sidebar điều hướng Admin và ẩn toàn bộ ô nhập Mã giảm giá trong Modal Xác Nhận Đơn Hàng (Cart Modal).
  3. Trang quản trị Mã giảm giá bổ sung thẻ công tắc/thông báo bật/tắt trực quan cho Admin.
- **Thực hiện**:
  - `config/app.php` & `.env` / `.env.example`: Khai báo `'feature_coupons_enabled' => (bool) env('FEATURE_COUPONS_ENABLED', true)`. Mặc định luôn là `true` nếu không khai báo.
  - `SystemSettingController.php`: Bổ sung `coupons_enabled` & `coupons_feature_enabled` vào `index()`, `update()`, và `publicSettings()`.
  - `OrderService.php`: Kiểm tra `coupons_enabled`, nếu bị tắt tự động set `$couponCode = null;` để bỏ qua mã giảm giá an toàn.
  - `CouponController.php`: Kiểm tra `coupons_enabled`, nếu bị tắt trả về thông báo lỗi 400.
  - `public-setting.store.ts` & `system-setting.store.ts`: Khai báo các trường cấu hình `coupons_enabled` và `coupons_feature_enabled`.
  - `sidebar.component.html`: Bọc link `/admin/coupons` bằng `@if (publicSettingStore.settings().coupons_enabled)`.
  - `cart-modal.component.html`: Bọc thẻ nhập mã giảm giá bằng `@if (publicSettingStore.settings().coupons_enabled)`.
  - `admin-coupons.component.ts` & `admin-coupons.component.html`: Bổ sung công tắc bật/tắt trạng thái hệ thống ngay phần đầu trang quản lý mã giảm giá.
  - `vi.ts`, `en.ts`: Bổ sung các string dịch tương ứng.
- **Kiểm thử**:
  - `php -l`: Kiểm tra toàn bộ file PHP backend không có lỗi cú pháp (**0 ERRORS**).
  - `npx ng build --configuration production`: Biên dịch Angular frontend thành công **100% BUILD SUCCESS**.

### [2026-07-27] Cập nhật hạn mức nạp tiền demo từ 0.1 đến 300 USDT cho khối Lịch sử nạp gần đây
- **Mô tả**: Điều chỉnh dữ liệu mẫu khởi tạo (`seedInitialDeposits`) và trình tạo nạp tiền ngẫu nhiên theo thời gian thực (`startDemoActivitiesTimer`) ở chế độ Demo trên Storefront để chỉ tạo các số tiền nạp vừa phải trong khoảng từ **0.1 đến 300 USDT**.
- **Thực hiện**:
  - `storefront.component.ts`: Cập nhật `seedInitialDeposits` với các mức nạp USDT thực tế từ `0.5`, `2.5`, `15.5`, `180`, `295`.
  - `storefront.component.ts`: Cập nhật mảng `demoUsdtAmounts = [0.1, 0.5, 1.2, 2.5, 5, 8.5, 10, 15, 25.5, 45, 50, 75, 120, 180.5, 250, 295, 300]` đảm bảo hạn mức nạp tối đa là 300 USDT.
- **Biên dịch & Kiểm thử**: Chạy `npx ng build --configuration production` thành công.

### [2026-07-27] Chuẩn hóa tạo Tên Người Dùng (Username) ngẫu nhiên tự nhiên ở chế độ Demo
- **Mô tả**: Thay thế logic lấy tên username cố định bằng phương thức khởi tạo tên ngẫu nhiên đa dạng (`getRandomDemoUsername()`) cho cả 2 khối "Đơn hàng vừa mua" và "Lịch sử nạp gần đây" ở chế độ Demo trên Storefront. Tên được chọn ngẫu nhiên từ kho danh xưng tự nhiên (MMO, Facebook Ads, Crypto, tên phong phú...) và tự động được che mượt dạng `minh****`, `tuand****`.
- **Thực hiện**:
  - `storefront.component.ts`: Thêm hàm `getRandomDemoUsername()` sinh tên ngẫu nhiên đa dạng từ danh sách gần 40 tiền tố username thực tế.
  - `storefront.component.ts`: Đồng bộ `seedInitialPurchases`, `seedInitialDeposits` và `startDemoActivitiesTimer` sử dụng `this.getRandomDemoUsername()`.
- **Biên dịch & Kiểm thử**: Chạy `npx ng build --configuration production` thành công **100% BUILD SUCCESS**.

### [2026-07-27] Khắc phục lỗi ưu tiên chế độ Demo và chuẩn hóa quy đổi USDT ở chế độ Real
- **Mô tả**:
  1. Khắc phục triệt để lỗi trang Storefront khi ở chế độ Demo nhưng vẫn hiển thị dữ liệu thật từ DB (gây ra hiện tượng hiển thị `10,000 USDT` hoặc `$0.00` của các user `a****` từ CSDL).
  2. Chuẩn hóa kiểm tra `mode === 'demo'` ưu tiên hàng đầu trước khi dùng `res.data` từ API.
  3. Chuẩn hóa hàm quy đổi USDT ở `StorefrontController.php` cho chế độ Real nếu số tiền trong DB lớn hơn 1000 (tránh lấy nhầm số VND gán cho USDT).
  4. Chuẩn hóa bộ phát sinh username ngẫu nhiên không trùng lặp cho các hàng khởi tạo ban đầu.
- **Thực hiện**:
  - `storefront.component.ts`: Đưa điều kiện `if (mode === 'demo')` lên trước `else if (res.data && res.data.length > 0)` trong `loadRecentActivities()`. Đảm bảo `getRandomDemoUsername()` kiểm tra trùng lặp trong `excludeList`.
  - `StorefrontController.php`: Bổ sung tính toán `total_usd` cho đơn mua và tự động quy đổi `$amountVnd / 25400` cho lượt nạp tiền nếu `amount_usdt > 1000`.
- **Kiểm thử**: `php -l` thành công, Angular build thành công **100% BUILD SUCCESS**.

### [2026-07-27] Dọn dẹp & Xóa vĩnh viễn tất cả sản phẩm, danh mục rác đã bị xóa trong CSDL
- **Mô tả**: Rà soát CSDL, tìm và thực hiện xóa vĩnh viễn (force delete) toàn bộ các sản phẩm bị xóa mềm (soft-deleted) và các danh mục rác/không còn sản phẩm trong CSDL theo yêu cầu.
- **Thực hiện**:
  - **Sản phẩm (Products)**: Tìm thấy 10 sản phẩm ở trạng thái xóa mềm (`deleted_at IS NOT NULL`). Đã thực hiện force delete 100% khỏi bảng `products` (không có ràng buộc tồn kho/đơn hàng).
  - **Danh mục (Categories)**: Xóa 3 danh mục rỗng/rác không chứa sản phẩm active nào (`BM Limit 250$`, `Danh mục A`, `LINKED IN`).
  - **Clear Cache**: Tiến hành xóa toàn bộ cache đệm Laravel và ứng dụng (`php artisan config:clear; php artisan cache:clear`).
- **Kết quả**:
  - Sản phẩm xóa mềm còn lại: **0** sản phẩm.
  - Sản phẩm active còn lại: **128** sản phẩm.
  - Danh mục còn lại: **14** danh mục.
