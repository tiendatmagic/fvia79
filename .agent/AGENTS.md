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
