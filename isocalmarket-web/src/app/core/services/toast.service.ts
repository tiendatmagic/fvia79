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
      'Product restored successfully.': 'Khôi phục sản phẩm thành công!',
      'Product not found.': 'Không tìm thấy sản phẩm trong hệ thống!',
      'Category not found.': 'Không tìm thấy danh mục!',
      'Cannot update a soft-deleted product.': 'Không thể cập nhật sản phẩm đã bị xóa tạm thời!',
      'Product not found or delete failed.': 'Không tìm thấy sản phẩm hoặc xóa thất bại!',
      'Product not found or restore failed.': 'Không tìm thấy sản phẩm hoặc khôi phục thất bại!',

      'Inventory batch created successfully.': 'Tạo lô hàng thành công!',
      'Inventory batch updated successfully.': 'Cập nhật lô hàng thành công!',
      'Inventory batch deleted successfully.': 'Xóa lô hàng thành công!',
      'Inventory batch not found.': 'Không tìm thấy lô hàng trong kho!',
      'No resource keys found in raw data.': 'Không tìm thấy tài nguyên nào trong dữ liệu nhập vào!',

      'Order created successfully.': 'Đặt hàng thành công!',
      'Order not found.': 'Không tìm thấy đơn hàng!',
      'Insufficient stock for product.': 'Không đủ hàng trong kho cho sản phẩm này!',

      // Cấu hình hệ thống & Bảo trì
      'Compiled views cleared successfully.': 'Xóa views đã biên dịch thành công!',
      'Config cache cleared successfully.': 'Xóa bộ nhớ đệm cấu hình thành công!',
      'Route cache cleared successfully.': 'Xóa bộ nhớ đệm định tuyến thành công!',
      'System optimization completed successfully.': 'Tối ưu hóa hiệu năng hệ thống thành công!',
      'Optimization cache cleared successfully.': 'Xóa bộ nhớ đệm tối ưu hóa thành công!',
      'System settings updated successfully.': 'Cập nhật cấu hình hệ thống thành công!',
      'Successfully cleared all active tokens and regenerated application key. All users logged out.': 'Đã xóa toàn bộ token đang hoạt động và làm mới khóa ứng dụng. Tất cả người dùng đã bị đăng xuất!',
      'The admin wallet address must be a valid BEP20/EVM address starting with 0x followed by 40 hex characters.': 'Địa chỉ ví Admin phải là địa chỉ ví BEP20/EVM hợp lệ (bắt đầu bằng 0x và có đúng 40 ký tự hex theo sau).',

      // Profile & Bảo mật
      'Password updated successfully.': 'Cập nhật mật khẩu thành công!',
      'Current password is incorrect.': 'Mật khẩu hiện tại không chính xác!',
      '2FA is already enabled.': 'Bảo mật 2FA đã được kích hoạt từ trước!',
      '2FA has not been initialized. Please enable first.': 'Bảo mật 2FA chưa được khởi tạo. Vui lòng kích hoạt trước!',
      'API Key generated successfully.': 'Tạo API Key thành công!',

      // Hóa đơn nạp tiền
      'Created deposit invoice successfully.': 'Tạo hóa đơn nạp tiền thành công!',
      'Deposit invoice not found.': 'Không tìm thấy hóa đơn nạp tiền!',
      'Failed to cancel invoice. Invalid invoice state or unauthorized.': 'Không thể hủy hóa đơn. Trạng thái hóa đơn không hợp lệ hoặc không có quyền!',
      'Cancelled deposit invoice successfully.': 'Hủy hóa đơn nạp tiền thành công!',
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

    if (trimmed.startsWith("Successfully cleaned up") && trimmed.endsWith("expired refresh tokens from database.")) {
      const match = trimmed.match(/cleaned up (\d+) expired/i);
      const count = match ? match[1] : '0';
      return `Đã dọn dẹp thành công ${count} token hết hạn từ cơ sở dữ liệu!`;
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
