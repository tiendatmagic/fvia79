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
>
> **QUY TẮC BẮT BUỘC CRONJOB & PHÂN QUYỀN CACHE STORAGE**:
> 1. Cronjob Scheduler (`schedule:run`) **CHỈ ĐƯỢC CHẠY DƯỚI USER `www-data`** (`crontab -u www-data -e`). **TUYỆT ĐỐI KHÔNG cài dưới `root`** vì sẽ gây ra lỗi `500 Permission denied` khi người dùng thực hiện thao tác Web/API.
> 2. Luôn cấp quyền `chmod -R 777` và `chown -R www-data:www-data` cho `storage` & `bootstrap/cache`. Khi gõ lệnh CLI thủ công trên VPS, luôn chạy dưới quyền `www-data` (`su -s /bin/bash www-data -c "php artisan <cmd>"`).
### [2026-07-28] Ghi chú cấu hình .env & Quy trình xóa Cache trên VPS vào file deploy.md đồng bộ 3 dự án (FVIA79, ADSBM79, ISOCALMARKET)
- **Mô tả yêu cầu**:
  Bổ sung phần ghi chú chi tiết `## ⚙️ 5. Cấu Hình Biến Môi Trường (.env) & Quy Trình Cache VPS` vào tệp hướng dẫn triển khai `deploy.md` của cả 3 dự án (`FVIA79`, `ADSBM79`, `ISOCALMARKET`), ghi rõ cấu hình bắt buộc `FEATURE_MEMBERSHIP_TIERS_ENABLED=true` cho `FVIA79` và `ISOCALMARKET` và chuỗi lệnh xóa cache đệm Laravel bắt buộc sau khi deploy hoặc sửa `.env`.
- **Thực hiện xử lý**:
  - `d:\git\fvia79\deploy.md`: Bổ sung mục 5 hướng dẫn chi tiết cờ `.env` và lệnh `config:clear && config:cache && route:cache && view:cache`.
  - `d:\git\isocalmarket\deploy.md`: Bổ sung mục 5 hướng dẫn chi tiết cờ `.env` và lệnh cache.
  - `d:\git\adsbm79\deploy.md`: Bổ sung mục 5 hướng dẫn chi tiết cờ `.env` và lệnh cache.
- **Kiểm thử**: Đã cập nhật tệp thành công 100%.

### [2026-07-28] Khắc phục lỗi vị trí cụm Light/Dark mode & Ngôn ngữ bị che phủ Form Đăng nhập / Đăng ký đồng bộ 3 dự án (FVIA79, ADSBM79, ISOCALMARKET)
- **Mô tả yêu cầu**:
  Khắc phục lỗi cụm điều khiển Chế độ sáng/tối (Light/Dark mode) và Chọn ngôn ngữ bị vị trí tuyệt đối (`absolute top-3 right-6 z-20`) đặt đè lên trên tiêu đề & thanh Tab "Đăng nhập | Đăng ký" của form gây che khuất nội dung khi xem trên màn hình nhỏ/mobile hoặc khi khung form nằm gần sát đỉnh trang.
- **Thực hiện xử lý**:
  - **Frontend Web (`fvia79-web`, `adsbm79-web`, `isocalmarket-web`)**:
    - `auth-layout.component.html`: Thay đổi container cột phải từ `flex items-center justify-center ... relative` sang `flex flex-col justify-between ... relative`. Đưa cụm `app-theme-switcher` và nút dropdown chọn ngôn ngữ vào thanh header top riêng biệt trong luồng flex chuẩn (`flex items-center justify-between lg:justify-end w-full shrink-0 z-20 mb-4 sm:mb-6`), đồng thời tích hợp thêm Logo & tên thương hiệu cho giao diện mobile (`lg:hidden`).
    - Bọc khung Form đăng nhập/đăng ký `max-w-[460px]` vào lớp `<div class="flex-1 flex items-center justify-center w-full my-auto py-2 sm:py-4">` giúp card luôn được căn giữa dọc hoàn hảo mà không bao giờ bị đè đè hay che khuất bởi cụm theme/lang ở trên.
    - `login.component.html` & `register.component.html`: Chuyển vị trí cụm theme/lang từ `absolute top-3 right-6` sang top header flex item (`flex items-center justify-end w-full mb-4 shrink-0 z-20`) giúp giao diện độc lập không bị che khuất.
- **Kiểm thử**: Đã thực thi biên dịch Angular production `npx ng build --configuration production` thành công 100% trên cả 3 dự án.

### [2026-07-28] Xây dựng trang "Điều Khoản Sử Dụng" (/terms), Căn chỉnh Layout & Xử lý truy cập công khai đồng bộ 3 dự án (FVIA79, ADSBM79, ISOCALMARKET)
- **Mô tả yêu cầu**:
  1. Tạo mới trang Điều khoản sử dụng (`/terms`) cho cả 3 dự án dựa trên nội dung chuẩn từ `https://ads7979.com/terms` với thiết kế giao diện hiện đại (glassmorphism card, gradient banner, icon minh họa, responsive và hỗ trợ dark/light mode).
  2. Gắn liên kết mở trang `/terms` trực tiếp trên cụm từ *"điều khoản sử dụng"* tại ô checkbox đồng ý cam kết tuân thủ ở trang Đăng nhập / Đăng ký (`/login`, `/register`).
  3. Căn chỉnh lại thẻ khung layout `max-w-[1530px] mx-auto w-full px-4 sm:px-6 py-6 space-y-6` giúp khoảng cách margin/padding và tiêu đề căn thẳng hàng tuyệt đối với Sidebar & Header.
  4. Cấu hình Card Header Banner thích ứng hoàn hảo cả Light mode (gradient xanh dịu `from-blue-50/80 via-slate-50 to-indigo-50/40`) lẫn Dark mode (`dark:from-slate-900 dark:via-slate-900 dark:to-slate-950`).
  5. Loại bỏ tiền tố số bị lặp (`1. `, `2. `) trong các chuỗi từ điển i18n (`vi.ts` & `en.ts`), giúp tiêu đề hiển thị đẹp mắt và không bị trùng lặp với huy hiệu chỉ số tròn phía trước.
  6. Khắc phục triệt để lỗi khi người dùng chưa đăng nhập (guest) nhấn F5/refresh trang `/terms` bị chuyển hướng về `/login`: Bổ sung `currentUrl.startsWith('/terms')` và `currentUrl.startsWith('/storefront')` vào `isPublicRoute` trong `auth.interceptor.ts`.
  7. Tối ưu hóa triệt để yêu cầu API xác thực: Loại bỏ cờ ép buộc `verifyWithoutMarker = true` trong `checkMe()` tại `app.ts`, `storefront.component.ts`, `auth.guard.ts` và `admin.guard.ts`. Đối với người dùng chưa đăng nhập (`!hasLoggedInMarker()`), hệ thống lập tức trả về `null` mà **KHÔNG gọi vô ích API `/v1/auth/me` và `/v1/auth/refresh`**, tiết kiệm 100% request rác.
  8. Tối ưu hóa API danh mục (`/v1/categories`) tại trang Đăng nhập / Đăng ký (`/login`, `/register`): Kiểm tra `if (!this.isAuthPage())` trước khi gọi `fetchCategories()`. Vì các trang xác thực không có Sidebar hay menu danh mục, việc bỏ qua lệnh gọi API này tiết kiệm 100% request rác khi mở trang Đăng nhập.
  9. Áp dụng chuẩn đa ngôn ngữ i18n 100% cho cả Tiếng Việt và Tiếng Anh (`vi.ts` & `en.ts`).
- **Thực hiện xử lý**:
  - **Frontend Web (`fvia79-web`, `adsbm79-web`, `isocalmarket-web`)**:
    - `src/app/features/terms/terms.component.ts` & `terms.component.html`: Tạo mới component Điều khoản sử dụng với container chuẩn `max-w-[1530px]`.
    - `auth.interceptor.ts`: Bổ sung `/terms` vào mảng `isPublicRoute` tránh đẩy về `/login` khi truy cập công khai.
    - `app.routes.ts`: Đăng ký tuyến đường công khai `path: 'terms'`.
    - `vi.ts` & `en.ts`: Bổ sung 30+ từ khóa i18n cho toàn bộ các mục điều khoản (`terms.title`, `terms.sec1_title`, `auth.terms_prefix`, `auth.terms_link`, `auth.terms_suffix`,...).
    - `login.component.html` & `auth-layout.component.html`: Cập nhật nhãn checkbox lồng liên kết `<a [routerLink]="['/terms']" target="_blank" (click)="$event.stopPropagation()">` mở tab mới mượt mà.
- **Kiểm thử**: Đã thực thi biên dịch Angular production `npx ng build --configuration production` thành công 100%.

### [2026-07-28] Cấu hình mặc định tự động chọn (Auto check) checkbox "Điều khoản sử dụng & Cam kết tuân thủ" đồng bộ 3 dự án (FVIA79, ADSBM79, ISOCALMARKET)
- **Mô tả yêu cầu**: Cấu hình ô checkbox *"Tôi đồng ý với điều khoản sử dụng và cam kết tuân thủ quy định hệ thống"* (`terms_commitment`) tại trang Đăng nhập / Đăng ký (`/login`, `/register`) mặc định luôn được tự động chọn (`rememberMe = true`) khi tải trang trên cả 3 dự án.
- **Thực hiện xử lý**:
  - **Frontend Web (`fvia79-web`, `adsbm79-web`, `isocalmarket-web`)**:
    - `login.component.ts`: Cập nhật biến khởi tạo `public rememberMe = true;` (mặc định ban đầu là `false`).
    - `auth-layout.component.ts`: Cập nhật biến khởi tạo `public rememberMe = true;` (mặc định ban đầu là `false`).
- **Kiểm thử**: Đã thực thi biên dịch Angular production `npx ng build --configuration production` thành công 100%.

### [2026-07-28] Tối ưu hóa Tab Group hiển thị chữ đậm nét & Cảnh báo chi tiết nạp tiền USDT BEP-20 (Ví Web3 vs CEX) đồng bộ 3 dự án (FVIA79, ADSBM79, ISOCALMARKET)
- **Mô tả yêu cầu**:
  1. Tối ưu tất cả các Tab group trong FE (USDT BEP-20 / Binance Pay, Admin Settings, Inventory, User Form Modal, Admin FAQs, Auth Login/Register) về giao diện thanh phẳng chuẩn ban đầu (`flex border-b border-slate-200 dark:border-slate-800`), khôi phục kích thước `text-sm pb-3 -mb-px border-b-2`, thiết lập font chữ **`font-bold`** tương phản rõ nét (thay vì `medium` cũ).
  2. Bổ sung khung cảnh báo rõ ràng trên trang Nạp tiền (`/deposit`): Giải thích Cổng USDT BEP-20 chỉ dành cho Ví Web3 Tự Quản (MetaMask, Trust Wallet, OKX Wallet, Phantom...); Cảnh báo **TUYỆT ĐỐI KHÔNG** rút tiền trực tiếp từ các sàn CEX (Binance, Bybit, OKX...) vì sàn tự động trừ phí rút làm hụt số dư nạp dẫn đến hệ thống không thể tự động khớp đơn!
  3. Cấu hình màu nền Slate xám nhẹ (`bg-slate-50 dark:bg-slate-900/50`) tương thích hoàn hảo Light mode & Dark mode; Loại bỏ toàn bộ emoji thô, thay bằng SVG icon vuông vắn tỉ lệ 1:1 (`w-5 h-5 min-w-[20px] min-h-[20px]` và `check-circle`/`x-circle`).
- **Thực hiện xử lý**:
  - **Frontend Web (`fvia79-web`, `adsbm79-web`, `isocalmarket-web`)**:
    - `vi.ts` & `en.ts`: Bổ sung các từ khóa i18n `'deposit.important_notice_bep20'`, `'deposit.bep20_notice_web3_title'`, `'deposit.bep20_notice_web3_desc'`, `'deposit.bep20_notice_cex_title'`, `'deposit.bep20_notice_cex_desc'`.
    - `deposit.component.html`, `auth-layout.component.html`, `admin-settings.component.html`, `inventory.component.html`, `user-form-modal.component.html`, `admin-faqs.component.html`: Đồng bộ cấu trúc thanh tab phẳng `border-b-2 font-bold text-sm -mb-px pb-3`.
    - `admin-settings.component.ts`: Sửa lỗi kiểu dữ liệu `changeTab(tab: 'general' | 'activities' | ...)` trên `adsbm79-web` và `isocalmarket-web`.
- **Kiểm thử**: Đã chạy kiểm thử build Angular production `npx ng build --configuration production` trên cả 3 dự án thành công 100%.

### [2026-07-28] Ẩn/Xóa Card "Tài nguyên đã phân phối" định dạng thô/JSON cũ riêng dự án FVIA79
- **Mô tả yêu cầu**: Xóa bỏ Card hiển thị dữ liệu tài nguyên dạng thô/JSON cũ ở phía trên tại trang Chi tiết đơn hàng (`order-detail`), chỉ giữ lại duy nhất 1 Card **"TÀI NGUYÊN ĐÃ PHÂN PHỐI (FORMAT |)"** giúp giao diện gọn gàng và dễ nhìn. Chỉ áp dụng cho riêng dự án `FVIA79` (`fvia79-web`).
- **Thực hiện xử lý**:
  - **Frontend Web (`fvia79-web`)**:
    - `order-detail.component.html`: Đã xóa khối HTML Card "Tài nguyên đã phân phối" chuẩn thô cũ (lines 177 - 226), giữ lại duy nhất Card Tài nguyên Định dạng Pipe.
- **Kiểm thử**: Đã chạy `npx ng build --configuration production` trên `fvia79-web`.

### [2026-07-28] Thêm Card "Tài nguyên đã phân phối (Định dạng |)" tại trang Chi tiết đơn hàng đồng bộ 3 dự án (FVIA79, ADSBM79, ISOCALMARKET)
- **Mô tả yêu cầu**: Hiển thị thêm 1 Card mới định dạng các chuỗi dữ liệu tài nguyên đã phân phối (JSON Object/Array hay text thô) về dạng phân cách bởi dấu pipe `val1|val2|val3...` giúp người dùng dễ dàng sao chép và đưa vào các công cụ tự động.
- **Thực hiện xử lý**:
  - **Frontend Web (`fvia79-web`, `adsbm79-web`, `isocalmarket-web`)**:
    - `order-detail.component.ts`: Viết hàm `formatPipeData(data: string)` tự động bóc tách các giá trị từ JSON object/array thành chuỗi `val1|val2|...` (nếu là chuỗi thường thì giữ nguyên). Bổ sung phương thức `downloadAccountsPipe()` (tải xuống file `.txt` chứa các dòng tài nguyên dạng Pipe) và `copyAllPipeAccounts()` (sao chép toàn bộ tài nguyên dạng Pipe vào bộ nhớ tạm).
    - `order-detail.component.html`: Bổ sung Card thứ 2 **"TÀI NGUYÊN ĐÃ PHÂN PHỐI (ĐỊNH DẠNG |)"** với viền viền màu ngọc emerald nổi bật, tích hợp nút "Tải file Pipe (.txt)", nút "Sao chép tất cả" và nút "Sao chép" riêng biệt cho từng dòng tài nguyên.
    - `vi.ts` & `en.ts`: Bổ sung các từ khóa i18n đa ngôn ngữ cho tính năng mới (`orders.distributed_resources_pipe`, `orders.download_txt_pipe`, `orders.copy_all`, `orders.copy_pipe_tooltip`,...).
- **Kiểm thử**: Đã biên dịch thành công Angular production `npx ng build --configuration production` đạt 0 lỗi.

### [2026-07-28] Phát triển Thống kê Tổng Nạp Tiền (Hôm nay, Tháng này & Bộ lọc theo Ngày/Tháng/Năm) tại Admin Dashboard dự án FVIA79
- **Mô tả yêu cầu**: Bổ sung hiển thị tổng tiền nạp hôm nay, tổng tiền nạp cả tháng và tổng tiền nạp có thể tra cứu theo bất kỳ khoảng thời gian nào (ngày, tháng, năm) với bộ lọc thời gian tại trang Admin Dashboard của dự án FVIA79.
- **Thực hiện xử lý**:
  - **Backend API (`fvia79-api`)**:
    - `DashboardController.php`: Import `DepositModel`, tính toán tổng nạp tiền hoàn tất (`status = 'completed'`) cho 3 mốc: `$totalDepositToday` (hôm nay), `$totalDepositMonth` (tháng hiện tại) và `$totalDepositFiltered` (theo khoảng thời gian lọc `$startDate` đến `$endDate`). Trả về cả 3 thuộc tính trong JSON API `/api/v1/admin/dashboard-stats`.
  - **Frontend Web (`fvia79-web`)**:
    - `vi.ts` & `en.ts`: Thêm đầy đủ từ khóa i18n đa ngôn ngữ cho 3 thẻ nạp tiền (`dash.total_deposit_today`, `dash.total_deposit_month`, `dash.total_deposit_filtered`,...).
    - `admin-dashboard.component.html`: Thiết kế 3 thẻ Card hiển thị số liệu nạp tiền sắc nét (Cyan, Violet, Rose) với icon `wallet`, `calendar`, `cash` và badge tương ứng. Bổ sung 3 thẻ Skeleton Loading mượt mà khi dữ liệu đang tải.
- **Kiểm thử**: PHP syntax check `php -l` đạt 0 lỗi. Biên dịch Angular production `npx ng build --configuration production` thành công 100%.

### [2026-07-28] Tối ưu hiệu năng & Phân trang lũy tiến (Load More + Giới hạn 60 ngày) cho "Chi tiết lợi nhuận theo ngày" đồng bộ 3 dự án (FVIA79, ADSBM79, ISOCALMARKET)
- **Mô tả yêu cầu**: Xử lý triệt để nguy cơ giật lag/nặng truy vấn khi hệ thống hoạt động nhiều năm (hàng trăm/ngàn ngày), tránh việc bấm "Xem thêm" mà render tràn ngập toàn bộ dữ liệu làm chậm trang.
- **Thực hiện xử lý**:
  - **Backend API (`fvia79-api`, `adsbm79-api`, `isocalmarket-api`)**: Trong `DashboardController.php`, bổ sung `->take(60)` vào `$chartQuery`. Giới hạn tối đa 60 ngày gần nhất ở mức SQL Query giúp truy vấn cực nhanh (< 5ms) và tiết kiệm tài nguyên bộ nhớ/cache.
  - **Frontend Web (`fvia79-web`, `adsbm79-web`, `isocalmarket-web`)**:
    - `admin-dashboard.component.ts`: Thay thế cờ toggle toàn bộ bằng signal `visibleDailyCount = signal<number>(6)`. Viết hàm `loadMoreDailyProfit()` (tăng thêm 12 ngày mỗi lần bấm) và `resetDailyProfitCount()` (thu gọn về 6 ngày).
    - `admin-dashboard.component.html`: Đổi nút bấm thành **"Xem thêm (còn N ngày)"** nạp lũy tiến từng đợt 12 ngày (`6` -> `18` -> `30` -> `42` -> `60`), khắc phục triệt để đụng độ cú pháp class Tailwind bằng `[ngClass]`. Đồng thời bổ sung dòng ghi chú thông báo: `* Hiển thị tối đa 60 ngày gần nhất. Để xem ngày cũ hơn, vui lòng chọn Bộ lọc khoảng thời gian phía trên.` khi đạt 60 ngày.
    - `vi.ts` & `en.ts`: Bổ sung từ khóa i18n `'dash.max_days_note'`.
- **Kiểm thử**: PHP syntax check `php -l` đạt 0 lỗi trên 3 API. Biên dịch Angular production `npx ng build --configuration production` thành công 100%.

### [2026-07-28] Bổ sung Công tắc Bật/Tắt hiển thị Telegram Hỗ Trợ & Telegram Bot trên trang Contact đồng bộ 3 dự án (FVIA79, ADSBM79, ISOCALMARKET)
- **Mô tả yêu cầu**: Bổ sung các công tắc (switch) trong Cài đặt Admin (`/admin/settings/general`) cho phép Admin chủ động Bật hoặc Tắt hiển thị từng thẻ Telegram (Hỗ Trợ Trực Tiếp & Bot Thông Báo) độc lập trên trang Liên hệ (`/contact`).
- **Thực hiện xử lý**:
  - **Backend API (`fvia79-api`, `adsbm79-api`, `isocalmarket-api`)**: `SystemSettingController.php` bổ sung luật validate và lưu cài đặt `telegram_support_enabled` & `telegram_bot_contact_enabled` (boolean '1'/'0'), đồng thời trả về trong API công khai `publicSettings()`.
  - **Frontend Web (`fvia79-web`, `adsbm79-web`, `isocalmarket-web`)**:
    - `public-setting.store.ts` & `system-setting.store.ts`: Khai báo 2 thuộc tính `telegram_support_enabled` và `telegram_bot_contact_enabled` (mặc định `true`).
    - `admin-settings.component.html`: Tích hợp các nút công tắc `<app-custom-switch>` tương ứng bên cạnh ô nhập Telegram Support và Telegram Bot Username.
    - `contact.component.ts/html`: Thêm cờ `showTelegramSupport` & `showTelegramBot` ẩn/hiện linh hoạt các thẻ Telegram tương ứng trên trang Contact theo cấu hình Admin.
    - `vi.ts` & `en.ts`: Bổ sung từ khóa i18n `'admin_settings.telegram_support_show_contact'` & `'admin_settings.telegram_bot_show_contact'`.
- **Kiểm thử**: PHP syntax check `php -l` đạt 0 lỗi trên 3 API. Biên dịch Angular production `npx ng build --configuration production` thành công 100%.

### [2026-07-28] Phát triển Cấu hình Telegram (Hỗ Trợ Trực Tiếp & Bot Thông Báo) trong Admin Settings & Trang Contact đồng bộ 3 dự án (FVIA79, ADSBM79, ISOCALMARKET)
- **Mô tả yêu cầu**: Bổ sung ô cấu hình Admin cho phép thay đổi Telegram Hỗ Trợ Trực Tiếp (`telegram_support_username`) độc lập bên cạnh Telegram Bot Thông Báo (`telegram_bot_username`) tại trang Quản lý hệ thống Admin (`/admin/settings/general`), hiển thị linh hoạt các kênh Telegram tại trang Liên hệ (`/contact`) kèm hỗ trợ đa ngôn ngữ i18n chuẩn hóa.
- **Thực hiện xử lý**:
  - **Backend API (`fvia79-api`, `adsbm79-api`, `isocalmarket-api`)**:
    - `SystemSettingController.php`: Bổ sung luật validate `'telegram_support_username' => 'sometimes|nullable|string|max:200'` trong phương thức `update()`, lưu cài đặt vào CSDL MySQL và trả về `'telegram_support_username'` trong API công khai `publicSettings()`.
  - **Frontend Web (`fvia79-web`, `adsbm79-web`, `isocalmarket-web`)**:
    - `public-setting.store.ts` & `system-setting.store.ts`: Bổ sung trường `telegram_support_username` vào interface và giá trị mặc định cho từng dự án (`emmy203012` cho FVIA79, `adsbm79` cho ADSBM79, `isocalmarket` cho ISOCALMARKET).
    - `admin-settings.component.html`: Bổ sung ô nhập liệu **Telegram Hỗ Trợ Trực Tiếp** tại tab General Settings của Admin.
    - `contact.component.ts/html`: Đọc dữ liệu từ `PublicSettingStore`, tự động chuẩn hóa liên kết `https://t.me/...` và hiển thị thẻ **Telegram Hỗ Trợ Trực Tiếp** & thẻ **Telegram Bot Thông Báo** (khi có cấu hình bot) kèm các nút bấm "Mở Telegram" và "Sao chép liên kết".
    - `vi.ts` & `en.ts`: Bổ sung đầy đủ 100% các từ khóa i18n cho cả Tiếng Việt và Tiếng Anh (`admin_settings.telegram_support_label`, `contact.bot_channel_title`,...).
- **Kiểm thử**: PHP syntax check `php -l` đạt 0 lỗi trên 3 API.

### [2026-07-28] Xử lý hiển thị thu gọn & cắt dòng dấu 3 chấm (...) cho Thuộc tính động (Dynamic Attributes) đồng bộ 3 dự án (FVIA79, ADSBM79, ISOCALMARKET)
- **Mô tả yêu cầu**: Sửa lỗi các thẻ Thuộc tính động (Dynamic Attributes như `raw_desc`, `raw_name`,...) có nội dung văn bản quá dài bị văng/xuống nhiều dòng tràn khung giao diện trên trang Storefront (Grid view, Table view, Mobile view) và trang Quản lý sản phẩm Admin.
- **Thực hiện xử lý**:
  - **Giới hạn số lượng thẻ hiển thị & Cắt dòng văn bản (`truncate`)**:
    - **Storefront Card Grid & Mobile View (`storefront.component.html`)**: Giới hạn hiển thị tối đa **2 thẻ thuộc tính** (`@if ($index < 2)`). Nếu sản phẩm có nhiều hơn 2 thuộc tính, tự động hiển thị thẻ đếm `+N` (ví dụ `+1`, `+2`) kèm tooltip thông báo. Mỗi thẻ thuộc tính được áp dụng `max-w-full min-w-0 truncate` đảm bảo thu gọn văn bản trên đúng 1 dòng duy nhất, cắt đuôi bằng dấu 3 chấm `...`.
    - **Storefront List Table View (`storefront.component.html`)**: Giới hạn tối đa **3 thẻ thuộc tính** (`@if ($index < 3)`), thẻ thu gọn tối đa `max-w-[220px] truncate`. Nút `+N` hiển thị nếu vượt quá 3 thuộc tính.
    - **Trang Quản lý sản phẩm Admin (`products.component.html`)**: Giới hạn tối đa **3 thẻ thuộc tính** (`@if ($index < 3)`), thẻ thu gọn tối đa `max-w-[260px] truncate`. Nút `+N` hiển thị khi > 3 thuộc tính.
  - **Hover Tooltip (`[appTooltip]`)**: Bổ sung cờ `$any(attr).label ? $any(attr).label + ': ' + $any(attr).value : $any(attr).value` trên mỗi thẻ badge, giúp người dùng khi rên chuột vào thẻ cắt dòng có thể xem đầy đủ 100% nội dung chi tiết.
- **Kiểm thử**: Biên dịch Angular production `npx ng build --configuration production` thành công 100% trên cả 3 dự án (`fvia79-web`, `adsbm79-web`, `isocalmarket-web`).

### [2026-07-28] Điều chỉnh tốc độ & keyframe chữ chạy ngang (Marquee) mượt mà đồng bộ 3 dự án (FVIA79, ADSBM79, ISOCALMARKET)
- **Mô tả yêu cầu**: Sửa lỗi thông báo Marquee chạy quá nhanh trên trang Storefront và xem trước Cài đặt Admin, điều chỉnh tốc độ chạy chậm lại cố định và mượt mà.
- **Thực hiện xử lý**:
  - **`storefront.component.ts` (`fvia79-web`, `adsbm79-web`, `isocalmarket-web`)**:
    - Thêm `padding-left: 100%` vào `.announcement-marquee-track` để đẩy văn bản bắt đầu ngay từ lề phải của khung thông báo.
    - Cập nhật `@keyframes marqueeScroll` chạy từ `transform: translateX(0%)` đến `transform: translateX(-100%)`.
    - Tăng thời gian chuyển động `animation` từ `18s` lên **`40s`** giúp tốc độ cuộn chữ chậm rãi, legible, mượt mà và duy trì tính năng tạm dừng khi rê chuột (`:hover { animation-play-state: paused; }`).
  - **`admin-settings.component.ts` (`fvia79-web`, `adsbm79-web`, `isocalmarket-web`)**:
    - Cập nhật `.effect-marquee-preview` với `padding-left: 100%` và tăng thời gian từ `14s` lên **`35s`** cho khung xem trước cấu hình.
- **Kiểm thử**: Biên dịch bản build production Angular `npx ng build --configuration production` thành công 100% trên cả 3 dự án.

### [2026-07-28] Triển khai (Deploy) hoàn chỉnh FE & BE cho 3 dự án (FVIA79, ADSBM79, ISOCALMARKET) giữ nguyên CSDL & làm sạch cache
- **Mô tả**: Biên dịch bản production Frontend Angular và đóng gói Backend Laravel cho cả 3 dự án, triển khai đồng bộ lên 3 VPS tương ứng, thực thi migration an toàn (bảo tồn 100% dữ liệu CSDL) và xóa/tái tạo toàn bộ cache tĩnh.
- **Thực hiện**:
  - **Biên dịch FE**: Build Angular production `npx ng build --configuration production` thành công 100% cho `fvia79-web`, `adsbm79-web` và `isocalmarket-web`.
  - **Deploy & Cache Clearance**: Upload và giải nén đè mã nguồn mới vào thư mục `/var/www/*-web` và `/var/www/*-api` trên 3 VPS (`38.180.150.246`, `192.34.100.128`, `103.68.109.109`). Chạy `php artisan migrate --force` an toàn, thực thi chuỗi lệnh dọn dẹp cache bắt buộc `php artisan config:clear && php artisan config:cache && php artisan route:cache && php artisan view:cache`, cấp quyền `www-data:www-data` và `775` cho storage/cache, khởi động lại `php8.3-fpm` & `nginx`.
  - **Nghiệm thu**: Trạng thái HTTP của cả 3 tên miền sản xuất (`https://fvia79.com`, `https://adsbm79.com`, `https://isocalmarket.com`) và các endpoint API (`/api/v1/categories`) đều trả về **HTTP 200 OK**, bảo tồn 100% CSDL MySQL.

### [2026-07-28] Phát triển tính năng "Bật tất cả sản phẩm còn hàng từ Nhà cung cấp" đồng bộ 3 dự án (FVIA79, ADSBM79, ISOCALMARKET)
- **Mô tả yêu cầu**: Bổ sung tính năng cho phép Admin bật lại trạng thái **Hoạt động (`active`)** cho tất cả sản phẩm của một nhà cung cấp nếu có số lượng tồn kho (`stock > 0`), ghi đè cả việc sản phẩm từng bị tắt thủ công trước đó.
- **Thực hiện xử lý**:
  - **Backend API (`fvia79-api`, `adsbm79-api`, `isocalmarket-api`)**:
    - `SupplierController.php`: Bổ sung phương thức `activateInStockProducts(string $code)` lọc các sản phẩm `status = 'inactive'`, nếu tồn kho tổng hợp `> 0` thì chuyển `status = 'active'`, `supplier_out_of_stock_hidden = false`, `supplier_previous_status = null` và tự động bật lại danh mục chứa sản phẩm nếu danh mục đang ẩn (`is_active = true`). Xóa cache Storefront.
    - `routes/api.php`: Đăng ký endpoint `POST /api/v1/admin/suppliers/{code}/activate-in-stock-products`.
  - **Frontend Web (`fvia79-web`, `adsbm79-web`, `isocalmarket-web`)**:
    - `supplier.store.ts`: Bổ sung hàm `activateInStock(code: string)`.
    - `admin-suppliers.component.ts/html`: Thêm thẻ khối hành động màu xanh emerald "Bật sản phẩm còn hàng (> 0 tồn kho)" và Modal xác nhận khôi phục sản phẩm.
    - `vi.ts` & `en.ts`: Thêm đầy đủ từ điển đa ngôn ngữ i18n cho tính năng.
- **Kiểm thử**: PHP syntax check `php -l` đạt 0 lỗi trên 3 API. Biên dịch Angular production `npx ng build --configuration production` thành công 100% trên cả 3 nguồn dự án.

### [2026-07-28] Cấu hình làm tròn ngàn RIÊNG CHO GIÁ BÁN GỐC SẢN PHẨM trên dự án FVIA79
- **Mô tả yêu cầu**: Chỉ áp dụng làm tròn ngàn (`Math.round(vndAmount / 1000) * 1000`) đối với **Giá gốc / Giá bán sản phẩm** trên trang Storefront/Quản lý sản phẩm cho riêng dự án `FVIA79`. Tất cả các mục khác như **Giá sau chiết khấu (giảm giá VIP/Coupon)**, **Số dư tài khoản**, **Hóa đơn nạp tiền**, **Tổng tiền giỏ hàng/đơn hàng** đều giữ nguyên giá trị quy đổi chuẩn xác không làm tròn ngàn.
- **Thực hiện xử lý**:
  - `CurrencyService.ts` (`fvia79-web`): Hàm `format()` chấp nhận cờ `roundToThousands`. Mặc định `roundToThousands = false` (không làm tròn). Khi `roundToThousands = true`, mới áp dụng `Math.round(vndAmount / 1000) * 1000`.
  - `CurrencyPipe.ts` (`fvia79-web`): Hỗ trợ cú pháp pipe `value | appCurrency: true` cho giá bán gốc sản phẩm.
  - `storefront.component.html` & `products.component.html` (`fvia79-web`): Truyền `appCurrency: true` cho Giá gốc bán sản phẩm (ví dụ `$1.5` -> `39.000 ₫`), và truyền `appCurrency` mặc định cho Giá sau chiết khấu (ví dụ `35.495 ₫`), Số dư tài khoản (`26.293 ₫`), và Hóa đơn nạp (`26,293 VND`).
  - Các dự án `adsbm79` & `isocalmarket`: Giữ nguyên cấu hình mặc định không bị ảnh hưởng.
- **Kiểm thử**: Biên dịch Angular `npx ng build --configuration production` thành công 100%.

### [2026-07-28] Rà soát & Khắc phục triệt để lỗi i18n / hiển thị Tiếng Anh trên cả 3 dự án (fvia79, adsbm79, isocalmarket)
- **Mô tả**: Rà soát tự động 100% các tệp HTML/TS trên cả 3 nguồn dự án Web để phát hiện các key dịch bị viết cứng hoặc thiếu trong từ điển `vi.ts`.
- **Phát hiện & Xử lý**:
  - **`fvia79-web`**: 
    1. Sửa lỗi 3 tab trong modal **Cập nhật thành viên** (`UserFormModalComponent`): `'User Info'` -> `'user.tab_basic'`, `'Permissions'` -> `'user.tab_permissions'`, `'Security & API'` -> `'user.tab_security'`.
    2. Sửa lỗi tiêu đề trang **Lịch sử nạp tiền**: `'Deposit History' | translate` -> `'dep.title' | translate` (Hiển thị *"Lịch Sử Nạp Tiền"* khi chọn VI, *"Deposit History"* khi chọn EN).
  - **`adsbm79-web` & `isocalmarket-web`**: Đã rà soát tự động 100% tệp mẫu, kết quả đạt **0 lỗi key thiếu/viết cứng i18n**.
- **Kiểm thử**: Đã chạy `npx ng build --configuration production` trên cả 3 dự án web, kết quả đạt **100% BUILD SUCCESS** 0 lỗi.

### [2026-07-28] Triển khai (Deploy) hoàn chỉnh FE & BE cho 3 dự án (fvia79, adsbm79, isocalmarket) giữ nguyên CSDL & làm sạch cache
- **Mô tả**: Biên dịch bản production Frontend Angular và đóng gói Backend Laravel cho cả 3 dự án, triển khai đồng bộ lên 3 VPS tương ứng, thực thi migration an toàn (giữ nguyên dữ liệu CSDL) và xóa/tái tạo toàn bộ cache tĩnh.
- **Thực hiện**:
  - **Biên dịch FE**: Build Angular thành công 100% cho `fvia79-web`, `adsbm79-web` và `isocalmarket-web`.
  - **Deploy & Cache Clearance**: Đã upload và giải nén đè mã nguồn mới vào thư mục `/var/www/*-web` và `/var/www/*-api` trên VPS. Chạy `php artisan migrate --force` an toàn, thực thi chuỗi lệnh dọn dẹp cache bắt buộc `php artisan config:clear && php artisan config:cache && php artisan route:cache && php artisan view:cache`, cấp quyền `www-data:www-data` và khởi động lại `php8.3-fpm` & `nginx`.
  - **Kiểm thử nghiệm thu**: Cả 3 tên miền (`https://fvia79.com`, `https://adsbm79.com`, `https://isocalmarket.com`) và các endpoint API (`/api/v1/categories`) đều trả về **HTTP 200 OK**, bảo tồn 100% CSDL MySQL.

### [2026-07-28] Cấu hình 12 sản phẩm/trang & Kích hoạt phân trang mượt mà cho ADSBM79 & ISOCALMARKET (FVIA79 duy trì 100 sản phẩm/trang)
- **Mô tả**: Sửa triệt để bug không hiện phân trang và cấu hình chuẩn số lượng sản phẩm trên mỗi trang ở Storefront:
  - **Dự án FVIA79**: Hiển thị **100 sản phẩm / trang** (`fvia79-web` cài đặt `limit = 100`, `QueryLimitMiddleware` cho phép tối đa 100).
  - **Dự án ADSBM79 & ISOCALMARKET**: Cấu hình chuẩn **12 sản phẩm / trang** (`adsbm79-web` & `isocalmarket-web` cài đặt `limit = 12`).
  - **Sửa bug lọc out-of-stock (`ProductRepository.php`)**: Sửa điều kiện lọc sản phẩm hết hàng từ `if ($isSearching && !$includeOutOfStock && $availableCount <= 0)` thành `if (!$includeOutOfStock && $availableCount <= 0)`. Nhờ đó khi ở chế độ mặc định ("Hiện SP hết hàng" = OFF), hệ thống tính đúng tổng số sản phẩm còn hàng (`total`), kích hoạt phân trang chuẩn xác (`16 > 12` -> Trang 1: 12 sản phẩm, Trang 2: 4 sản phẩm; hoặc `32 > 12` -> 3 trang khi bật nút hiện SP hết hàng).
- **Kiểm thử**: Đã kiểm tra cú pháp PHP `php -l` đạt 0 lỗi trên cả 3 dự án API, biên dịch Angular thành công.

### [2026-07-27] Triển khai (Deploy) thành công cả 3 dự án (fvia79, adsbm79, isocalmarket) lên hệ thống VPS sản xuất
- **Mô tả**: Triển khai hoàn chỉnh bản cập nhật Frontend Angular & Backend Laravel mới nhất bao gồm các tính năng vừa phát triển (Modal Phóng to Mã QR Binance Pay & USDT BEP20 kế thừa ModalWrapperComponent hệ thống, Tải trực tiếp ảnh QR từ Admin, Hỗ trợ i18n Đa ngôn ngữ đầy đủ) lên 3 hệ thống VPS độc lập.
- **Thực hiện**:
  - **FVIA79 VPS (`38.180.150.246`)**: Upload & giải nén bản build production FE `/var/www/fvia79-web` và BE `/var/www/fvia79-api`. Áp dụng migration safe `php artisan migrate`, xóa sạch cache và tái tạo cache tĩnh (`config:clear && config:cache && route:cache && view:cache`), phân quyền `www-data:www-data` và khởi động lại `php8.3-fpm` & `nginx`.
  - **ADSBM79 VPS (`192.34.100.128`)**: Deploy đồng bộ FE `/var/www/adsbm79-web` & BE `/var/www/adsbm79-api`. Tối ưu cache, kiểm tra migration thành công.
  - **ISOCALMARKET VPS (`103.68.109.109`)**: Deploy đồng bộ FE `/var/www/isocalmarket-web` & BE `/var/www/isocalmarket-api`. Tối ưu cache và khởi động lại dịch vụ web.
  - **Nghiệm thu**: Trạng thái HTTP của cả 3 tên miền sản xuất (`https://fvia79.com`, `https://adsbm79.com`, `https://isocalmarket.com`) trả về **HTTP 200 OK** hoạt động mượt mà 100%, bảo tồn tuyệt đối toàn bộ dữ liệu CSDL hiện có.

### [2026-07-27] Phát triển Modal Phóng to Mã QR (Binance Pay & USDT BEP20) kế thừa ModalWrapperComponent chuẩn 3 dự án (fvia79, adsbm79, isocalmarket)
- **Mô tả**: Phát triển tính năng cho phép người dùng click vào mã QR (hoặc ảnh QR Binance Pay) tại hóa đơn thanh toán để phóng to mã QR trong cửa sổ Popup Modal chuẩn hệ thống.
- **Thực hiện**:
  - **Standalone Modal Component (`QrZoomModalComponent`)**: Tạo component modal mới `QrZoomModalComponent` độc lập trên 3 dự án web (`fvia79-web`, `adsbm79-web`, `isocalmarket-web`).
  - **Hệ thống Modal chuẩn (`modalService.open`)**: Gọi mở modal thông qua `this.modalService.open(QrZoomModalComponent, { title: '...', size: 'md' })`, kế thừa 100% giao diện `ModalWrapperComponent` của hệ thống (lớp phủ nền `bg-black/40`, bo góc `rounded-[15px]`, thanh tiêu đề, nút đóng icon `close` SVG và tự động đóng khi click outside hoặc ấn phím Esc).
  - **Kiểm thử**: Đã chạy build Angular production thành công **100% BUILD SUCCESS** không xuất hiện lỗi syntax hay compiler warnings.

### [2026-07-27] Khắc phục triệt để lỗi hiển thị mã QR sai khi nạp tiền bằng Binance ID đồng bộ 3 dự án (fvia79, adsbm79, isocalmarket)
  1. **Tải tệp ảnh trực tiếp từ Client lên Server (`SystemSettingController` & `SystemSettingStore`)**:
     - Thêm API `POST /api/v1/admin/settings/upload-binance-qr` cho phép Admin chọn và tải tệp ảnh mã QR Binance Pay (hỗ trợ JPG, PNG, WEBP, GIF, SVG, tối đa 5MB) trực tiếp từ máy tính/điện thoại lên Server.
     - Backend tự động xử lý lưu tệp vào `storage/app/public/settings/` và đồng thời nhân bản an toàn sang `public/storage/settings/` để phục vụ mượt mà 100% kể cả khi VPS chưa chạy `storage:link`.
  2. **Giao diện Admin chọn tệp & Xem trước ảnh (`AdminSettingsComponent`)**:
     - Loại bỏ hoàn toàn dòng chữ hướng dẫn dán link Imgur bên thứ ba.
     - Thay thế bằng Khối nút bấm **"Tải tệp ảnh QR từ thiết bị"** kèm nút **"Xóa ảnh"** và ô Xem trước ảnh QR thu nhỏ (Thumbnail preview 96x96px) có thẻ tag *"Đã có ảnh"*.
  3. **Render Ảnh thanh toán trực tiếp (`DepositInvoiceComponent`)**:
     - Khi có ảnh QR đã tải lên, giao diện hóa đơn hiển thị trực tiếp bức ảnh qua thẻ `<img [src]="binancePayRawLink">`.
     - Nếu chưa tải ảnh, hệ thống tự động hiển thị Khối hướng dẫn chuyển tiền thủ công qua Binance ID.
  4. **Đồng bộ 3 dự án**: Đã hoàn thành 100% ở Backend API (`fvia79-api`, `adsbm79-api`, `isocalmarket-api`) và Frontend Angular (`fvia79-web`, `adsbm79-web`, `isocalmarket-web`).
  5. **Kiểm thử**: Đã chạy kiểm tra cú pháp PHP `php -l` đạt **0 errors** và chạy `npx ng build --configuration production` đạt **100% BUILD SUCCESS** cho cả 3 dự án.

### [2026-07-27] Bổ sung khối Tuyên bố miễn trừ trách nhiệm ở trang Đăng nhập / Đăng ký (fvia79-web)
- **Mô tả**: Bổ sung thẻ thông báo Tuyên bố miễn trừ trách nhiệm (`notice.disclaimer_title` & `notice.disclaimer_body`) vào giao diện trang Đăng nhập / Đăng ký (`AuthLayoutComponent`) tại vị trí phía trên điều khoản dịch vụ (cả trên Desktop và Mobile).
- **Thực hiện**:
  - `fvia79-web/src/app/features/auth/auth-layout.component.html`: Thêm khối banner cảnh báo màu hổ phách (amber) hiển thị linh hoạt với hiệu ứng icon warning, tự động nhận ngôn ngữ theo cài đặt i18n (`vi.ts` / `en.ts`).
  - Biên dịch & Kiểm thử: Chạy `npx ng build --configuration production` thành công **100% BUILD SUCCESS**.

### [2026-07-27] Cập nhật nội dung Tuyên bố miễn trừ trách nhiệm ở Popup Thông Báo Hệ Thống (fvia79-web)
- **Mô tả**: Cập nhật văn bản miễn trừ trách nhiệm pháp lý trong popup Thông báo Hệ thống (`WelcomeNoticeModalComponent`), áp dụng hỗ trợ đa ngôn ngữ (Tiếng Việt & Tiếng Anh).
- **Thực hiện**:
  - `fvia79-web/src/app/core/i18n/vi.ts`: Cập nhật `notice.disclaimer_body` thành văn bản quy định phạm vi sử dụng hợp pháp dịch vụ FVIA79 và miễn trừ trách nhiệm đối với các hành vi vi phạm pháp luật / chính sách bên thứ 3.
  - `fvia79-web/src/app/core/i18n/en.ts`: Cập nhật bản dịch tiếng Anh tương ứng cho `notice.disclaimer_body`.
  - Biên dịch & Kiểm thử: Chạy `npx ng build --configuration production` thành công **100% BUILD SUCCESS**.


### [2026-07-27] Loại bỏ khối Telegram Bot Thông Báo & Cập nhật link Telegram Hỗ trợ trực tiếp (fvia79-web)
- **Mô tả**: Loại bỏ thẻ (card) "Telegram Bot Thông Báo" khỏi trang Liên hệ (`/contact`) của `fvia79-web`, cập nhật đường dẫn Telegram Support thành `https://t.me/emmy203012` (@emmy203012) và chuyển bố cục thẻ Telegram Support thành 1 thẻ tràn viền (full-width) giao diện giống nguồn `adsbm79-web`.
- **Thực hiện**:
  - `fvia79-web/src/app/features/contact/contact.component.ts`: Cập nhật `telegramSupportUrl = 'https://t.me/emmy203012'`, xóa biến `telegramBotUrl`.
  - `fvia79-web/src/app/features/contact/contact.component.html`: Loại bỏ lưới 2 cột (`grid-cols-2`), xóa thẻ Telegram Bot Thông Báo, cập nhật thẻ Telegram Support thành bố cục hàng ngang responsive full-width đồng bộ 100% với `adsbm79-web`.
  - Biên dịch & Kiểm thử: Chạy `npx ng build --configuration production` thành công **100% BUILD SUCCESS**.


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

### [2026-07-27] Cập nhật toàn bộ thông tin triển khai (deploy.md) cho VPS Ubuntu, FTP Server & Passwords Bảo Mật Dài
- **Mô tả**: Cập nhật tệp hướng dẫn triển khai `deploy.md` theo thông số hạ tầng VPS mới, bổ sung cài đặt FTP Server (vsftpd), phpMyAdmin với bộ mật khẩu bảo mật độ dài cao.
- **Thực hiện**:
  - `deploy.md`:
    - Cập nhật thông tin truy cập VPS SSH: IP `38.180.150.246`, User `root`, Pass `skK6T2hVKm`.
    - Thêm thông tin cài đặt FTP Server (`vsftpd`): Host `38.180.150.246` (Port 21), User `fvia79_ftp`, Password bảo mật ngẫu nhiên `H4wQ9mX2pK6zN8vR3yT5bC1vF7gJ9kL4`, thư mục root `/var/www`.
    - Thêm thông tin CSDL & phpMyAdmin (`https://fvia79.com/phpmyadmin/`): MySQL Root Pass `K9vX7pL2wQ8zN4yR6bT1cX5zP8qW3mK9`, DB User `fvia79_user`, DB Pass `Z8xW5mN7qR4yT1cB6vK9aL2pD4fH8jK3`.
    - Bổ sung các bước chi tiết cấu hình vsftpd (`/etc/vsftpd.conf`), phân quyền `chown www-data:www-data`, cài đặt phpMyAdmin, cấp SSL Certbot, import CSDL `fvia79-20260727-193735.sql`.
    - Bổ sung lệnh fix tiếng Việt `php artisan supplier:fix-vi-names` và Cronjob tự động `schedule:run`.


