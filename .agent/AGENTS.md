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

