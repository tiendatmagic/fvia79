import { Injectable, inject, signal } from '@angular/core';
import { LanguageService, TRANSLATIONS } from './language.service';

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
  'Cancelled deposit invoice successfully.': 'toast.deposit_cancelled_success',
  '2FA has been disabled for this user.': 'toast.2fa_disabled',
  '2FA has been enabled for this user successfully.': 'toast.2fa_enabled',
  '2FA is not enabled.': 'error.2fa_not_enabled',
  'A transaction ID/hash is required for manual approval.': 'error.transaction_id_required',
  'Transaction ID/hash is too long.': 'error.transaction_id_too_long',
  'This transaction hash has already been used for another deposit.': 'error.transaction_hash_used',
  'All system caches were cleared and optimized successfully.': 'maintenance.task_success',
  'Bulk user deletion successful.': 'toast.bulk_users_deleted',
  'Bulk user status updated successfully.': 'toast.bulk_user_status_updated',
  'CSRF validation failed.': 'error.unauthorized',
  'Cannot delete category containing products.': 'toast.category_has_products',
  'Cannot reject a completed deposit invoice.': 'error.deposit_completed_reject',
  'Category created successfully.': 'toast.category_created',
  'Category deleted successfully.': 'toast.category_deleted',
  'Category updated successfully.': 'toast.category_updated',
  'Coupon code is invalid or does not exist.': 'toast.coupon_invalid',
  'Coupon created successfully.': 'toast.coupon_created',
  'Coupon deleted successfully.': 'toast.coupon_deleted',
  'Coupon not found.': 'error.coupon_not_found',
  'Coupon restored successfully.': 'toast.coupon_restored',
  'Coupon updated successfully.': 'toast.coupon_updated',
  'End date must be after start date.': 'toast.coupon_date_invalid',
  'This coupon has expired.': 'error.coupon_expired',
  'This coupon has reached its usage limit.': 'error.coupon_usage_limit',
  'This coupon is not active yet.': 'error.coupon_not_active',
  'Deposit invoice approved successfully. User balance has been credited.': 'toast.deposit_approved',
  'Deposit invoice rejected successfully.': 'toast.deposit_rejected',
  'Deposit invoice revoked successfully. User balance has been deducted.': 'toast.revoke_success',
  'Failed to approve deposit invoice.': 'toast.deposit_approve_failed',
  'Failed to reject deposit invoice.': 'toast.deposit_reject_failed',
  'Failed to revoke deposit invoice.': 'toast.revoke_failed',
  'Forbidden. Access denied.': 'error.forbidden',
  'Forbidden. Admin access required.': 'toast.admin_required',
  'Insufficient user balance. Cannot subtract more than current balance.': 'toast.user_balance_insufficient',
  'Login successful.': 'toast.login_success',
  'Logout successful.': 'toast.logout_success',
  'Registration successful.': 'toast.register_success',
  'Matched transaction on BSC block chain. Balance updated.': 'invoice.payment_confirmed',
  'Missing inventory content or file.': 'toast.import_validation',
  'No deposit invoice IDs selected for bulk revocation.': 'error.deposit_selection_required',
  'No valid target users selected for deletion.': 'error.user_selection_required',
  'No valid target users selected for status update.': 'error.user_selection_required',
  'Not logged in.': 'error.unauthorized',
  'Only completed deposit invoices can be revoked.': 'error.deposit_not_completed',
  'Only pending or expired deposit invoices can be approved.': 'error.deposit_not_approvable',
  'Percentage adjustment cannot be below -100%.': 'supplier.percentage_invalid',
  'Product is not available or has been discontinued.': 'error.product_unavailable',
  'Profit reset to 0 successfully.': 'maintenance.profit_reset',
  'Profit snapshots and dashboard stats recalculated successfully.': 'maintenance.profit_recalculated',
  'Quantity must be greater than 0.': 'error.quantity_positive',
  'Resource item deleted successfully.': 'toast.stock_deleted',
  'Resource item not found.': 'error.inventory_not_found',
  'Resource item updated successfully.': 'toast.stock_updated',
  'Successfully revoked all active tokens. All users logged out.': 'maintenance.tokens_cleared',
  'Supplier API key has not been configured.': 'supplier.api_key_missing',
  'ADS7979 API key has not been configured.': 'supplier.api_key_missing',
  'Supplier URL must be a plain HTTPS origin without credentials, query, fragment, or custom port.': 'supplier.url_invalid',
  'Supplier URL must resolve only to public internet addresses.': 'supplier.url_public_required',
  'Supplier hostname could not be resolved.': 'supplier.hostname_unresolved',
  'Supplier category is missing its id.': 'supplier.category_id_missing',
  'Supplier product is missing its id.': 'supplier.product_id_missing',
  'Supplier is disabled.': 'supplier.disabled_error',
  'Supplier not found.': 'supplier.not_found_error',
  'Supplier rejected the V1 API key.': 'supplier.api_key_rejected',
  'Supplier returned an incompatible V2 catalog schema.': 'supplier.schema_incompatible',
  'Supplier returned an invalid V1 catalog response.': 'supplier.catalog_invalid',
  'Supplier returned an invalid V2 catalog response.': 'supplier.catalog_invalid',
  'ADS7979 returned an invalid catalog response.': 'supplier.catalog_invalid',
  'The supplier does not support the compatible Clone Store API V1/V2, or its API key is invalid.': 'supplier.unsupported_api',
  'The administrator has not configured the Binance API Key/Secret yet.': 'invoice.reconcile_binance_error',
  'The administrator has not configured the receiving Binance ID yet.': 'invoice.binance_support',
  'The administrator has not configured the receiving USDT wallet address yet.': 'invoice.wallet_support',
  'The administrator has not configured the scanner API key yet.': 'invoice.scanner_admin',
  'The coupon code already exists.': 'toast.coupon_exists',
  'The deposit amount must be at least 0.1 USDT.': 'toast.deposit_min_limit',
  'The items array must contain at least one item.': 'error.invalid_input',
  'The product_id field is required (or provide an items array).': 'error.invalid_input',
  'This deposit invoice is already in failed status.': 'error.deposit_already_failed',
  'This inventory batch was updated by another transaction. Close the modal and reload the page.': 'error.inventory_conflict',
  'Token refreshed.': 'auth.token_refreshed',
  'Too many login attempts. Please try again in 1 minute.': 'error.too_many_attempts',
  'Too many pending transactions for this amount. Please try again in a few minutes.': 'error.deposit_pending_limit',
  'Unable to connect to Binance Pay API. Please try again later.': 'invoice.binance_api_error',
  'Unable to connect to blockchain scanner API. Please try again later.': 'invoice.blockchain_api_error',
  'Unable to verify payment status on blockchain right now. Please try again later.': 'invoice.blockchain_api_error',
  'Binance Pay API rejected the transaction history request.': 'invoice.binance_api_error',
  'Unauthorized. API Key is missing.': 'error.unauthorized',
  'Unauthorized. Invalid API Key.': 'error.unauthorized',
  'Unauthorized. Invalid or expired token.': 'error.unauthorized',
  'Unauthorized. JWT token missing.': 'error.unauthorized',
  'Unauthorized. Refresh token missing.': 'error.unauthorized',
  'Unauthorized. Token has been revoked.': 'error.unauthorized',
  'Unauthorized. User not found.': 'error.unauthorized',
  'User account not found.': 'error.user_not_found',
  'User balance adjusted successfully.': 'toast.user_balance_adjusted',
  'User created successfully.': 'toast.user_created',
  'User deleted successfully.': 'toast.user_deleted',
  'User not found for this deposit.': 'error.user_not_found',
  'User not found or delete failed.': 'toast.user_delete_failed',
  'User updated successfully.': 'toast.user_updated',
  'VND/USD exchange rate must be greater than zero.': 'supplier.exchange_rate_invalid',
  'We could not detect your USDT payment on BSC blockchain yet. Please make sure you transferred the exact amount (with the fractional suffix) to the correct wallet address within 15 minutes.': 'invoice.bsc_transaction_missing',
  'You already have an active deposit order. Please cancel the previous order before creating a new one.': 'error.active_deposit',
  'You cannot delete your own account.': 'error.cannot_delete_self',
  'Cannot delete user because they have existing transactions (deposits or orders). Please ban the user instead.': 'error.cannot_delete_has_transactions',
  'Cannot delete user(s) because they have existing transactions (deposits or orders). Please ban the user instead.': 'error.cannot_delete_has_transactions',
  'Your account has been banned.': 'error.account_banned',
  'Your account has been banned. Please contact administrator.': 'error.account_banned',
  'The current password is incorrect.': 'error.current_password',
  'The two-factor authentication code is invalid.': 'toast.otp_invalid',
  'Database restored successfully. A safety backup was retained on the server.': 'database_backup.restore_success',
  'Restore was cancelled because the safety backup could not be created. The current database was not changed.': 'database_backup.safety_backup_failed',
  'The SQL import failed. The database was automatically restored to its previous state.': 'database_backup.restore_failed_rolled_back',
  'The SQL import failed and automatic recovery was unsuccessful. Manual recovery is required.': 'database_backup.restore_failed_manual',
  'Another database backup or restore operation is already running.': 'database_backup.busy',
  'The database backup service is currently unavailable. Check the MySQL client configuration on the server.': 'database_backup.unavailable_title',
  'Too many database export attempts. Please wait one minute and try again.': 'database_backup.export_rate_limited',
  'Too many database restore attempts. Please wait ten minutes and try again.': 'database_backup.restore_rate_limited'
};

const LARAVEL_KEY_FALLBACK: Record<string, string> = {
  'messages.invalid_password': 'error.current_password',
  'messages.invalid_otp': 'toast.otp_invalid',
  'messages.database_restore_success': 'database_backup.restore_success',
  'messages.database_safety_backup_failed': 'database_backup.safety_backup_failed',
  'messages.database_restore_failed_rolled_back': 'database_backup.restore_failed_rolled_back',
  'messages.database_restore_failed_manual_recovery': 'database_backup.restore_failed_manual',
  'messages.database_backup_busy': 'database_backup.busy',
  'messages.database_backup_unavailable': 'database_backup.unavailable_title',
  'messages.database_backup_rate_limited': 'database_backup.export_rate_limited',
  'messages.database_restore_rate_limited': 'database_backup.restore_rate_limited'
};

const VI_TRANSLATION_VALUES = new Set(Object.values(TRANSLATIONS.vi));
const EN_TRANSLATION_KEYS = new Map(
  Object.entries(TRANSLATIONS.en).map(([key, value]) => [value, key])
);
const VIETNAMESE_CHARACTERS = /[À-ỹĐđ]/;

@Injectable({ providedIn: 'root' })
export class ToastService {
  private readonly languageService = inject(LanguageService);
  public readonly toasts = signal<ToastItem[]>([]);

  public resolveApiMessage(message: string, status = 0, fallback = ''): string {
    const normalized = message?.trim() || '';
    if (/^[a-z0-9_.]+$/i.test(normalized)) {
      const laravelFallback = LARAVEL_KEY_FALLBACK[normalized];
      if (laravelFallback) return this.languageService.translate(laravelFallback);
      return this.languageService.translate(normalized);
    }
    const key = API_MESSAGE_KEYS[normalized];
    if (key) return this.languageService.translate(key);
    const exactTranslationKey = EN_TRANSLATION_KEYS.get(normalized);
    if (exactTranslationKey) return this.languageService.translate(exactTranslationKey);

    const imported = normalized.match(/^Successfully imported batch.*with (\d+) resources/i);
    if (imported) {
      return this.languageService.translate('toast.stock_imported_count', { count: imported[1] });
    }

    const cleaned = normalized.match(/^Successfully cleaned up (\d+) expired refresh tokens from database\.$/i);
    if (cleaned) {
      return this.languageService.translate('maintenance.expired_tokens_cleaned', { count: cleaned[1] });
    }

    const insufficientStock = normalized.match(
      /^Product '(.+)' does not have enough stock available \(Requested: (\d+), Available: (\d+)\)\.$/i
    );
    if (insufficientStock) {
      return this.languageService.translate('error.insufficient_stock_detail', {
        product: insufficientStock[1],
        requested: insufficientStock[2],
        available: insufficientStock[3]
      });
    }

    const insufficientBalance = normalized.match(
      /^Insufficient balance to pay for the order \(Required: ([\d,.]+) USD, Current: ([\d,.]+) USD\)\.$/i
    );
    if (insufficientBalance) {
      return this.languageService.translate('error.insufficient_balance_detail', {
        required: insufficientBalance[1],
        current: insufficientBalance[2]
      });
    }

    const minimumCart = normalized.match(/^Cart total must be at least ([\d,.]+) USD\.$/i);
    if (minimumCart) {
      return this.languageService.translate('error.minimum_cart', { amount: minimumCart[1] });
    }

    const bulkRevoke = normalized.match(
      /^Successfully revoked (\d+) deposit\(s\)\.(?: (\d+) deposit\(s\) failed or skipped.*)?$/i
    );
    if (bulkRevoke) {
      return this.languageService.translate('toast.bulk_revoke_result', {
        success: bulkRevoke[1],
        failed: bulkRevoke[2] || '0'
      });
    }

    const revokeBalance = normalized.match(
      /^Cannot revoke deposit invoice #(.+)\. User current balance \(([\d,.]+) USDT\) is less than the deposit amount \(([\d,.]+) USDT\)\.$/i
    );
    if (revokeBalance) {
      return this.languageService.translate('error.deposit_revoke_balance', {
        code: revokeBalance[1],
        current: revokeBalance[2],
        amount: revokeBalance[3]
      });
    }

    const supplierDisabled = normalized.match(/^Supplier (.+) is disabled\.$/i);
    if (supplierDisabled) {
      return this.languageService.translate('supplier.disabled_named', { name: supplierDisabled[1] });
    }

    const supplierSynchronized = normalized.match(/^(.+) catalog synchronized successfully\.$/i);
    if (supplierSynchronized) {
      return this.languageService.translate('supplier.synchronized_named', { name: supplierSynchronized[1] });
    }

    if (/^Coupon '.+' already exists\.$/i.test(normalized)) {
      return this.languageService.translate('toast.coupon_exists');
    }
    if (/^Coupon code '.+' is (?:invalid|already used by another coupon)\.$/i.test(normalized)) {
      return this.languageService.translate('toast.coupon_invalid');
    }
    if (/^An error occurred: Could not retrieve enough digital resources for product '.+'\.$/i.test(normalized)) {
      return this.languageService.translate('error.insufficient_stock');
    }
    if (/^The items\.\d+\.(?:product_id field is required|quantity must be greater than 0)\.$/i.test(normalized)) {
      return this.languageService.translate('error.invalid_input');
    }
    if (/^Unsupported supplier (?:catalog )?driver: .+$/i.test(normalized)) {
      return this.languageService.translate('supplier.unsupported_driver');
    }

    if (status === 401) return this.languageService.translate('error.invalid_credentials');
    if (status === 422) return this.languageService.translate('error.invalid_input');
    if (status === 429) return this.languageService.translate('error.too_many_attempts');
    if (status >= 500) return this.languageService.translate('error.server');
    if (status === 0 && !normalized) return this.languageService.translate('error.network');

    if (
      normalized
      && this.languageService.currentLang() === 'vi'
      && !VI_TRANSLATION_VALUES.has(normalized)
      && !VIETNAMESE_CHARACTERS.test(normalized)
    ) {
      return fallback || this.languageService.translate('toast.operation_failed');
    }

    return normalized || fallback;
  }

  public showToast(message: string, type: 'success' | 'error' | 'warning' = 'success', duration?: number): void {
    const fallbackKey = type === 'success'
      ? 'toast.operation_success'
      : type === 'warning'
        ? 'toast.operation_warning'
        : 'toast.operation_failed';
    const finalMessage = this.resolveApiMessage(
      message,
      0,
      this.languageService.translate(fallbackKey)
    );
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

  public showSuccess(message: string, duration?: number): void {
    this.showToast(message, 'success', duration);
  }

  public showError(message: string, duration?: number): void {
    this.showToast(message, 'error', duration);
  }

  public removeToast(id: string): void {
    this.toasts.update(current => current.filter(toast => toast.id !== id));
  }
}
