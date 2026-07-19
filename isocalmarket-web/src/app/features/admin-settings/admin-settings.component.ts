import { Component, OnInit, inject, signal } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { SystemSettingStore, SystemSettingsPayload } from './system-setting.store';
import { PageHeaderComponent } from '@shared/components/page-header/page-header.component';
import { CardComponent } from '@shared/components/card/card.component';
import { ButtonComponent } from '@shared/components/button/button.component';
import { SkeletonLoaderComponent } from '@shared/components/skeleton-loader/skeleton-loader.component';
import { IconComponent } from '@shared/components/icon/icon.component';
import { ToastService } from '@core/services/toast.service';
import { ModalService } from '@core/services/modal.service';

@Component({
  selector: 'app-admin-settings',
  standalone: true,
  imports: [
    CommonModule,
    FormsModule,
    PageHeaderComponent,
    CardComponent,
    ButtonComponent,
    SkeletonLoaderComponent,
    IconComponent
  ],
  templateUrl: './admin-settings.component.html',
  styles: [`
    :host {
      display: block;
    }
    .mask-security {
      -webkit-text-security: disc !important;
      text-security: disc !important;
    }
  `]
})
export class AdminSettingsComponent implements OnInit {
  public readonly store = inject(SystemSettingStore);
  private readonly toast = inject(ToastService);
  private readonly modalService = inject(ModalService);

  public readonly loading = this.store.loading;
  public readonly submitting = this.store.submitting;
  public readonly isLocked = signal<boolean>(true);
  public readonly showApiKey = signal<boolean>(false);
  public readonly showBinanceKey = signal<boolean>(false);
  public readonly showBinanceSecret = signal<boolean>(false);

  // Tab management
  public readonly activeTab = signal<'general' | 'maintenance'>('general');
  public readonly tokenCleanupDays = signal<number>(7);

  public form: SystemSettingsPayload = {
    admin_wallet_address: '',
    scanner_api_key: '',
    token_cleanup_days: 7,
    usdt_exchange_rate: 0,
    admin_binance_id: '',
    admin_binance_qr: '',
    binance_api_key: '',
    binance_api_secret: ''
  };

  ngOnInit(): void {
    this.store.fetchSettings().subscribe({
      next: (settings) => {
        if (settings) {
          this.form = { ...settings };
          if (settings.token_cleanup_days) {
            this.tokenCleanupDays.set(Number(settings.token_cleanup_days));
          }
        }
      },
      error: () => {
        this.toast.showToast('Không thể tải cấu hình hệ thống!', 'error');
      }
    });
  }

  public toggleLock(): void {
    this.isLocked.set(!this.isLocked());
  }

  public toggleApiKeyVisibility(): void {
    this.showApiKey.set(!this.showApiKey());
  }

  public toggleBinanceKeyVisibility(): void {
    this.showBinanceKey.set(!this.showBinanceKey());
  }

  public toggleBinanceSecretVisibility(): void {
    this.showBinanceSecret.set(!this.showBinanceSecret());
  }

  public changeTab(tab: 'general' | 'maintenance'): void {
    this.activeTab.set(tab);
  }

  public onSubmit(): void {
    if (this.submitting() || this.isLocked()) return;

    const walletAddress = this.form.admin_wallet_address.trim();
    if (!walletAddress) {
      this.toast.showToast('Địa chỉ ví Admin không được để trống!', 'warning');
      return;
    }

    // Kiểm tra định dạng ví BEP20/EVM (0x + 40 ký tự hex)
    const walletRegex = /^0x[a-fA-F0-9]{40}$/;
    if (!walletRegex.test(walletAddress)) {
      this.toast.showToast('Địa chỉ ví nhận USDT BEP20 không hợp lệ! Ví phải bắt đầu bằng "0x" và có đúng 42 ký tự (a-f, 0-9).', 'error');
      return;
    }

    if (!this.form.scanner_api_key.trim()) {
      this.toast.showToast('API Key Scanner không được để trống!', 'warning');
      return;
    }

    const binanceId = this.form.admin_binance_id?.trim();
    const binanceKey = this.form.binance_api_key?.trim();
    const binanceSecret = this.form.binance_api_secret?.trim();

    if (binanceId || binanceKey || binanceSecret) {
      if (!binanceId) {
        this.toast.showToast('Binance ID nhận tiền không được để trống!', 'warning');
        return;
      }
      if (!binanceKey) {
        this.toast.showToast('Binance API Key không được để trống!', 'warning');
        return;
      }
      if (!binanceSecret) {
        this.toast.showToast('Binance API Secret không được để trống!', 'warning');
        return;
      }
    }

    this.store.updateSettings(this.form).subscribe({
      next: () => {
        this.toast.showToast('Cập nhật cấu hình hệ thống thành công!', 'success');
        this.isLocked.set(true);
      },
      error: (err) => {
        const errorMsg = err?.error?.errors?.admin_wallet_address?.[0] || err?.error?.message || 'Có lỗi xảy ra khi cập nhật cấu hình!';
        this.toast.showToast(errorMsg, 'error');
      }
    });
  }

  // Tác vụ Bảo trì Cache & Tối ưu hóa
  public runMaintenance(action: string): void {
    if (this.store.runningMaintenance()) return;

    const run = () => {
      this.store.runMaintenance(action).subscribe({
        next: (res) => {
          this.toast.showToast(res.message || 'Thực hiện tác vụ bảo trì thành công!', 'success');
        },
        error: (err) => {
          this.toast.showToast(err.error?.message || 'Có lỗi xảy ra khi thực hiện bảo trì!', 'error');
        }
      });
    };

    if (action === 'clear-all') {
      this.modalService.confirm({
        title: 'Dọn dẹp nhanh hệ thống',
        description: 'Bạn có chắc chắn muốn xóa toàn bộ các tệp cache và tối ưu hóa lại hệ thống? Quá trình này có thể làm giảm tốc độ phản hồi ban đầu của server trong vài giây.',
        confirmText: 'Xóa tất cả cache',
        cancelText: 'Hủy',
        confirmButtonClass: '!bg-rose-600 hover:!bg-rose-700'
      }).afterClosed$.subscribe((confirmed?: boolean) => {
        if (confirmed) run();
      });
    } else {
      run();
    }
  }

  // Lưu cài đặt Token Cleanup
  public saveTokenSettings(): void {
    const days = this.tokenCleanupDays();
    if (days < 1 || days > 365) {
      this.toast.showToast('Số ngày giữ lại token phải từ 1 đến 365 ngày!', 'warning');
      return;
    }

    this.store.updateTokenSettings(days).subscribe({
      next: () => {
        this.toast.showToast('Cập nhật cấu hình Sanctum Token thành công!', 'success');
      },
      error: () => {
        this.toast.showToast('Không thể cập nhật cấu hình Token!', 'error');
      }
    });
  }

  // Thực hiện dọn dẹp Token hết hạn thủ công
  public triggerCleanTokens(): void {
    if (this.store.isCleaningTokens()) return;

    this.store.cleanTokens().subscribe({
      next: (res) => {
        this.toast.showToast(res.message || 'Đã dọn dẹp các token hết hạn thành công!', 'success');
      },
      error: () => {
        this.toast.showToast('Có lỗi xảy ra khi dọn dẹp token!', 'error');
      }
    });
  }

  // Xóa toàn bộ Token đăng nhập (logout tất cả)
  public triggerCleanAllTokens(): void {
    if (this.store.isCleaningTokens()) return;

    this.modalService.confirm({
      title: 'Xóa tất cả Login Tokens',
      description: 'Chú ý! Hành động này sẽ lập tức xóa sạch toàn bộ token đăng nhập của mọi thành viên hệ thống (bao gồm cả bạn). Tất cả tài khoản sẽ bị logout ngay lập tức. Bạn có muốn tiếp tục?',
      confirmText: 'Xóa tất cả Token',
      cancelText: 'Hủy',
      confirmButtonClass: '!bg-rose-600 hover:!bg-rose-700'
    }).afterClosed$.subscribe((confirmed?: boolean) => {
      if (confirmed) {
        this.store.cleanAllTokens().subscribe({
          next: (res) => {
            this.toast.showToast(res.message || 'Đã xóa tất cả token thành công!', 'success');
            // Reload trang để ép đăng xuất (guard sẽ bắt chuyển về trang login)
            setTimeout(() => {
              window.location.reload();
            }, 1500);
          },
          error: () => {
            this.toast.showToast('Có lỗi xảy ra khi xóa token!', 'error');
          }
        });
      }
    });
  }
}
