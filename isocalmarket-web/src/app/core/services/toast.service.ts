import { Injectable, signal } from '@angular/core';

export interface ToastItem {
  id: string;
  message: string;
  type: 'success' | 'error' | 'warning';
  duration: number;
}

@Injectable({
  providedIn: 'root'
})
export class ToastService {
  public readonly toasts = signal<ToastItem[]>([]);

  private translateMessage(msg: string): string {
    if (!msg) return '';

    // Từ điển dịch thông điệp từ tiếng Anh sang tiếng Việt
    const dictionary: Record<string, string> = {
      'Invalid email/username or password.': 'Tài khoản hoặc mật khẩu không chính xác!',
      'Unauthorized.': 'Phiên đăng nhập không hợp lệ hoặc đã hết hạn!',
      'Too Many Attempts.': 'Bạn đã thử quá nhiều lần. Vui lòng chờ vài phút rồi thử lại!',
      'The email has already been taken.': 'Email này đã được sử dụng. Vui lòng chọn email khác!',
      'The name has already been taken.': 'Tên tài khoản này đã tồn tại. Vui lòng chọn tên khác!',
      'The email field must be a valid email address.': 'Địa chỉ email không đúng định dạng!',
      'The password field must be at least 6 characters.': 'Mật khẩu phải có ít nhất 6 ký tự!',
      'Invalid or expired verification token.': 'Mã xác thực đã hết hạn. Vui lòng đăng nhập lại!',
      'Invalid OTP code. Please try again.': 'Mã OTP không chính xác. Vui lòng thử lại!',
      'Invalid 2FA verification code.': 'Mã xác thực 2FA không chính xác!',
      'User not found.': 'Tài khoản không tồn tại trong hệ thống!',
      'Change password successfully.': 'Đổi mật khẩu thành công!',
      'Password changed successfully.': 'Đổi mật khẩu thành công!',
      'Profile updated successfully.': 'Cập nhật thông tin thành công!',
      '2FA enabled successfully.': 'Khởi tạo 2FA thành công!',
      '2FA confirmed successfully.': 'Đã kích hoạt bảo mật 2FA thành công!',
      '2FA disabled successfully.': 'Đã hủy kích hoạt bảo mật 2FA!',
      'Successfully enabled 2FA.': 'Khởi tạo 2FA thành công!',
      'Successfully confirmed 2FA.': 'Đã kích hoạt bảo mật 2FA thành công!',
      'Successfully disabled 2FA.': 'Đã hủy kích hoạt bảo mật 2FA!',

      'Product created successfully.': 'Tạo sản phẩm thành công!',
      'Product updated successfully.': 'Cập nhật sản phẩm thành công!',
      'Product deleted successfully.': 'Xóa sản phẩm thành công!',
      'Product not found.': 'Không tìm thấy sản phẩm trong hệ thống!',
      'Category not found.': 'Không tìm thấy danh mục!',

      'Inventory batch created successfully.': 'Tạo lô hàng thành công!',
      'Inventory batch updated successfully.': 'Cập nhật lô hàng thành công!',
      'Inventory batch deleted successfully.': 'Xóa lô hàng thành công!',
      'Inventory batch not found.': 'Không tìm thấy lô hàng trong kho!',
      'No resource keys found in raw data.': 'Không tìm thấy tài nguyên nào trong dữ liệu nhập vào!',

      'Order created successfully.': 'Đặt hàng thành công!',
      'Order not found.': 'Không tìm thấy đơn hàng!',
      'Insufficient stock for product.': 'Không đủ hàng trong kho cho sản phẩm này!',
    };

    const trimmed = msg.trim();
    if (dictionary[trimmed]) {
      return dictionary[trimmed];
    }

    // Dịch động với pattern chứa tham số
    if (trimmed.startsWith("Successfully imported batch")) {
      const match = trimmed.match(/with (\d+) resources/i);
      const count = match ? match[1] : '';
      return `Import lô hàng thành công${count ? ' với ' + count + ' tài nguyên' : ''}!`;
    }

    return msg;
  }

  public showToast(message: string, type: 'success' | 'error' | 'warning' = 'success', duration?: number) {
    const finalMessage = this.translateMessage(message);
    const defaultDuration = type === 'success' ? 3000 : type === 'warning' ? 3500 : 4000;
    const finalDuration = duration || defaultDuration;
    const id = Date.now().toString() + Math.random().toString(36).substr(2, 9);

    const newToast: ToastItem = {
      id,
      message: finalMessage,
      type,
      duration: finalDuration
    };

    this.toasts.update(current => [...current, newToast]);

    setTimeout(() => {
      this.removeToast(id);
    }, finalDuration);
  }

  public removeToast(id: string) {
    this.toasts.update(current => current.filter(t => t.id !== id));
  }
}
