# AGENTS.md - Lịch sử Thay Đổi & Ghi Nhớ Công Việc (Isocalmarket)

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

## 📋 NHẬT KÝ THAY ĐỔI DỰ ÁN (ISOCALMARKET)

### [2026-07-26] Khắc phục triệt để đường dẫn Build & Deploy FE/BE Isocalmarket
- **Mô tả yêu cầu**: Dọn dẹp tệp ghi chép cũ, chuẩn hóa tên dự án `isocalmarket-web`, khắc phục triệt để việc script deploy nén nhầm thư mục cũ `dist/angular-web3-wallet`.
- **Thực hiện**:
  - **Frontend (`isocalmarket-web`)**:
    - `angular.json`, `package.json`, `netlify.toml`: Đồng bộ đổi tên dự án thành `isocalmarket-web`.
    - `product-form-modal.component.ts`: Sửa lỗi `TypeError: this.modalData.attributes is not iterable` khi Edit sản phẩm (hỗ trợ kiểm tra Array, Object Key-Value, JSON string).
    - Thư mục build output: `dist/isocalmarket-web/browser`. Xóa hoàn toàn thư mục build cũ `dist/angular-web3-wallet`.
  - **Script Deploy (`deploy_vps_production.js`, `deploy_fe_vps.js`)**:
    - Cập nhật 100% đường dẫn nén sang `dist/isocalmarket-web/browser`.
  - **VPS Production (`184.164.93.247`)**:
    - Web Root FE: `/var/www/isocalmarket-web`
    - Web Root BE: `/var/www/isocalmarket-api`
- **Xác nhận**: Biên dịch Angular build và triển khai 100% thành công.

---
