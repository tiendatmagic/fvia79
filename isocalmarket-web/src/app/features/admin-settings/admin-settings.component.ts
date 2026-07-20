import { Component, OnInit, OnDestroy, inject, signal } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { ActivatedRoute, Router } from '@angular/router';
import { Subscription } from 'rxjs';
import { SystemSettingStore, SystemSettingsPayload } from './system-setting.store';
import { PageHeaderComponent } from '@shared/components/page-header/page-header.component';
import { CardComponent } from '@shared/components/card/card.component';
import { ButtonComponent } from '@shared/components/button/button.component';
import { SkeletonLoaderComponent } from '@shared/components/skeleton-loader/skeleton-loader.component';
import { IconComponent } from '@shared/components/icon/icon.component';
import { ToastService } from '@core/services/toast.service';
import { ModalService } from '@core/services/modal.service';
import { LanguageService } from '@core/services/language.service';
import { TranslatePipe } from '@shared/pipes/translate.pipe';

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
    IconComponent,
    TranslatePipe
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
export class AdminSettingsComponent implements OnInit, OnDestroy {
  public readonly store = inject(SystemSettingStore);
  private readonly toast = inject(ToastService);
  private readonly modalService = inject(ModalService);
  public readonly lang = inject(LanguageService);
  private readonly route = inject(ActivatedRoute);
  private readonly router = inject(Router);
  private routeSub?: Subscription;

  public readonly loading = this.store.loading;
  public readonly submitting = this.store.submitting;
  public readonly isLocked = signal<boolean>(true);
  public readonly showApiKey = signal<boolean>(false);
  public readonly showBinanceKey = signal<boolean>(false);
  public readonly showBinanceSecret = signal<boolean>(false);

  // Tab management - driven by route param
  public readonly activeTab = signal<'general' | 'maintenance'>('general');
  public readonly tokenCleanupDays = signal<number>(7);

  public form: SystemSettingsPayload = {
    admin_wallet_address: '',
    scanner_api_key: '',
    token_cleanup_days: 7,
    usdt_exchange_rate: 0,
    admin_binance_id: '',
    binance_api_key: '',
    binance_api_secret: ''
  };

  ngOnInit(): void {
    // Sync active tab from URL param
    this.routeSub = this.route.paramMap.subscribe(params => {
      const tab = params.get('tab');
      if (tab === 'maintenance') {
        this.activeTab.set('maintenance');
      } else {
        this.activeTab.set('general');
      }
    });

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
        this.toast.showToast(this.lang.translate('toast.settings_load_failed'), 'error');
      }
    });
  }

  ngOnDestroy(): void {
    this.routeSub?.unsubscribe();
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
    this.router.navigate(['/admin/settings', tab]);
  }

  public onSubmit(): void {
    if (this.submitting() || this.isLocked()) return;

    const walletAddress = this.form.admin_wallet_address.trim();
    if (!walletAddress) {
      this.toast.showToast(this.lang.translate('toast.wallet_required'), 'warning');
      return;
    }

    // Kiểm tra định dạng ví BEP20/EVM (0x + 40 ký tự hex)
    const walletRegex = /^0x[a-fA-F0-9]{40}$/;
    if (!walletRegex.test(walletAddress)) {
      this.toast.showToast(this.lang.translate('toast.wallet_invalid'), 'error');
      return;
    }

    if (!this.form.scanner_api_key.trim()) {
      this.toast.showToast(this.lang.translate('toast.scanner_key_required'), 'warning');
      return;
    }

    const binanceId = this.form.admin_binance_id?.trim();
    const binanceKey = this.form.binance_api_key?.trim();
    const binanceSecret = this.form.binance_api_secret?.trim();

    if (binanceId || binanceKey || binanceSecret) {
      if (!binanceId) {
        this.toast.showToast(this.lang.translate('toast.binance_id_required'), 'warning');
        return;
      }
      if (!binanceKey) {
        this.toast.showToast(this.lang.translate('toast.binance_key_required'), 'warning');
        return;
      }
      if (!binanceSecret) {
        this.toast.showToast(this.lang.translate('toast.binance_secret_required'), 'warning');
        return;
      }
    }

    this.store.updateSettings(this.form).subscribe({
      next: () => {
        this.toast.showToast(this.lang.translate('toast.settings_updated'), 'success');
        this.isLocked.set(true);
      },
      error: (err) => {
        const errorMsg = err?.error?.errors?.admin_wallet_address?.[0] || err?.error?.message || this.lang.translate('toast.settings_update_failed');
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
          this.toast.showToast(res.message || this.lang.translate('maintenance.task_success'), 'success');
        },
        error: (err) => {
          this.toast.showToast(err.error?.message || this.lang.translate('maintenance.task_failed'), 'error');
        }
      });
    };

    if (action === 'clear-all') {
      this.modalService.confirm({
        title: this.lang.translate('modal.clear_cache_title'),
        description: this.lang.translate('modal.clear_cache_desc'),
        confirmText: this.lang.translate('action.confirm'),
        cancelText: this.lang.translate('action.cancel'),
        confirmButtonClass: '!bg-rose-600 hover:!bg-rose-700'
      }).afterClosed$.subscribe((confirmed?: boolean) => {
        if (confirmed) run();
      });
    } else {
      run();
    }
  }

  public saveTokenSettings(): void {
    const days = this.tokenCleanupDays();
    if (days < 1 || days > 365) {
      this.toast.showToast('toast.token_days_invalid', 'warning');
      return;
    }

    this.store.updateTokenSettings(days).subscribe({
      next: () => {
        this.toast.showToast('toast.token_settings_updated', 'success');
      },
      error: () => {
        this.toast.showToast('toast.token_settings_update_failed', 'error');
      }
    });
  }

  // Thực hiện dọn dẹp Token hết hạn thủ công
  public triggerCleanTokens(): void {
    if (this.store.isCleaningTokens()) return;

    this.store.cleanTokens().subscribe({
      next: (res) => {
        this.toast.showToast(
          this.toast.resolveApiMessage(res.message, 0, 'toast.expired_tokens_cleaned'),
          'success'
        );
      },
      error: () => {
        this.toast.showToast('toast.expired_tokens_clean_failed', 'error');
      }
    });
  }

  // Xóa toàn bộ Token đăng nhập (logout tất cả)
  public triggerCleanAllTokens(): void {
    if (this.store.isCleaningTokens()) return;

    this.modalService.confirm({
      title: this.lang.translate('modal.clean_all_tokens_title'),
      description: this.lang.translate('modal.clean_all_tokens_desc'),
      confirmText: this.lang.translate('action.confirm'),
      cancelText: this.lang.translate('action.cancel'),
      confirmButtonClass: '!bg-rose-600 hover:!bg-rose-700'
    }).afterClosed$.subscribe((confirmed?: boolean) => {
      if (confirmed) {
        this.store.cleanAllTokens().subscribe({
          next: (res) => {
            this.toast.showToast(res.message || this.lang.translate('toast.tokens_cleaned'), 'success');
            // Reload trang để ép đăng xuất (guard sẽ bắt chuyển về trang login)
            setTimeout(() => {
              window.location.reload();
            }, 1500);
          },
          error: () => {
            this.toast.showToast(this.lang.translate('toast.tokens_clean_failed'), 'error');
          }
        });
      }
    });
  }
}
