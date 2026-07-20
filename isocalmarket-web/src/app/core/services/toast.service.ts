import { Injectable, inject, signal } from '@angular/core';
import { LanguageService } from './language.service';

export interface ToastItem {
  id: string;
  message: string;
  type: 'success' | 'error' | 'warning';
  duration: number;
}

const API_MESSAGE_KEYS: Record<string, string> = {
  'Invalid email/username or password.': 'error.invalid_credentials',
  'Unauthorized.': 'error.unauthorized',
  'Too Many Attempts.': 'error.too_many_attempts',
  'Email already registered.': 'error.email_taken',
  'Username already taken.': 'error.username_taken',
  'The email has already been taken.': 'error.email_taken',
  'The name has already been taken.': 'error.username_taken',
  'The username has already been taken.': 'error.username_taken',
  'Username must only contain letters, numbers, and underscores.': 'error.username_format',
  'Username must be at least 3 characters.': 'error.username_length',
  'Email must be a valid email address.': 'toast.email_invalid_format',
  'The email field must be a valid email address.': 'toast.email_invalid_format',
  'Password must be at least 6 characters.': 'toast.password_too_short',
  'The password field must be at least 6 characters.': 'toast.password_too_short',
  'Password confirmation does not match.': 'error.password_mismatch',
  'Invalid or expired verification token.': 'error.verification_expired',
  'Invalid OTP code. Please try again.': 'toast.otp_invalid',
  'Invalid 2FA verification code.': 'toast.otp_invalid',
  'User not found.': 'error.user_not_found',
  'Change password successfully.': 'toast.password_changed',
  'Password changed successfully.': 'toast.password_changed',
  'Password updated successfully.': 'toast.password_changed',
  'Profile updated successfully.': 'toast.profile_updated',
  '2FA enabled successfully.': 'toast.2fa_enabled',
  '2FA confirmed successfully.': 'toast.2fa_enabled',
  '2FA disabled successfully.': 'toast.2fa_disabled',
  'Successfully enabled 2FA.': 'toast.2fa_enabled',
  'Successfully confirmed 2FA.': 'toast.2fa_enabled',
  'Successfully disabled 2FA.': 'toast.2fa_disabled',
  'Product created successfully.': 'toast.product_created',
  'Product updated successfully.': 'toast.product_updated',
  'Product deleted successfully.': 'toast.product_deleted',
  'Product restored successfully.': 'toast.product_restored',
  'Product not found.': 'error.product_not_found',
  'Category not found.': 'error.category_not_found',
  'Cannot update a soft-deleted product.': 'toast.edit_deleted_product',
  'Product not found or delete failed.': 'toast.product_delete_failed',
  'Product not found or restore failed.': 'toast.product_restore_failed',
  'Inventory batch created successfully.': 'toast.stock_imported',
  'Inventory batch updated successfully.': 'toast.stock_updated',
  'Inventory batch deleted successfully.': 'toast.stock_deleted',
  'Inventory batch not found.': 'error.inventory_not_found',
  'No resource keys found in raw data.': 'error.inventory_empty',
  'Order created successfully.': 'cart.order_success',
  'Order not found.': 'error.order_not_found',
  'Insufficient stock for product.': 'error.insufficient_stock',
  'Compiled views cleared successfully.': 'maintenance.views_cleared',
  'Config cache cleared successfully.': 'maintenance.config_cleared',
  'Route cache cleared successfully.': 'maintenance.routes_cleared',
  'System optimization completed successfully.': 'maintenance.optimized',
  'Optimization cache cleared successfully.': 'maintenance.optimization_cleared',
  'System settings updated successfully.': 'toast.settings_updated',
  'Successfully cleared all active tokens and regenerated application key. All users logged out.': 'maintenance.tokens_cleared',
  'The admin wallet address must be a valid BEP20/EVM address starting with 0x followed by 40 hex characters.': 'toast.wallet_invalid',
  'Current password is incorrect.': 'error.current_password',
  '2FA is already enabled.': 'error.2fa_already_enabled',
  '2FA has not been initialized. Please enable first.': 'error.2fa_not_initialized',
  'API Key generated successfully.': 'toast.apikey_created',
  'Created deposit invoice successfully.': 'toast.deposit_created_success',
  'Deposit invoice not found.': 'error.deposit_not_found',
  'Failed to cancel invoice. Invalid invoice state or unauthorized.': 'toast.deposit_cancel_failed',
  'Cancelled deposit invoice successfully.': 'toast.deposit_cancelled_success'
};

@Injectable({ providedIn: 'root' })
export class ToastService {
  private readonly languageService = inject(LanguageService);
  public readonly toasts = signal<ToastItem[]>([]);

  public resolveApiMessage(message: string, status = 0, fallback = ''): string {
    const normalized = message?.trim() || '';
    if (/^[a-z0-9_.]+$/i.test(normalized)) {
      return this.languageService.translate(normalized);
    }
    const key = API_MESSAGE_KEYS[normalized];
    if (key) return this.languageService.translate(key);

    const imported = normalized.match(/^Successfully imported batch.*with (\d+) resources/i);
    if (imported) {
      return this.languageService.translate('toast.stock_imported_count', { count: imported[1] });
    }

    const cleaned = normalized.match(/^Successfully cleaned up (\d+) expired refresh tokens from database\.$/i);
    if (cleaned) {
      return this.languageService.translate('maintenance.expired_tokens_cleaned', { count: cleaned[1] });
    }

    if (status === 401) return this.languageService.translate('error.invalid_credentials');
    if (status === 422) return this.languageService.translate('error.invalid_input');
    if (status === 429) return this.languageService.translate('error.too_many_attempts');
    if (status >= 500) return this.languageService.translate('error.server');
    if (status === 0 && !normalized) return this.languageService.translate('error.network');

    return normalized || fallback;
  }

  public showToast(message: string, type: 'success' | 'error' | 'warning' = 'success', duration?: number): void {
    const finalMessage = this.resolveApiMessage(message);
    const defaultDuration = type === 'success' ? 3000 : type === 'warning' ? 3500 : 4000;
    const id = Date.now().toString() + Math.random().toString(36).substring(2, 11);

    this.toasts.update(current => [...current, {
      id,
      message: finalMessage,
      type,
      duration: duration || defaultDuration
    }]);

    setTimeout(() => this.removeToast(id), duration || defaultDuration);
  }

  public removeToast(id: string): void {
    this.toasts.update(current => current.filter(toast => toast.id !== id));
  }
}
