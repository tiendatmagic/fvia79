import { Component, OnInit, OnDestroy, inject, signal, computed } from '@angular/core';
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
import { CustomSwitchComponent } from '@shared/components/custom-switch/custom-switch.component';
import { CustomSelectComponent } from '@shared/components/custom-select/custom-select.component';
import { DatabaseBackupPanelComponent } from './database-backup-panel/database-backup-panel.component';

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
    TranslatePipe,
    CustomSwitchComponent,
    CustomSelectComponent,
    DatabaseBackupPanelComponent
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
    @keyframes marqueeScroll {
      0% { transform: translateX(100%); }
      100% { transform: translateX(-100%); }
    }
    @keyframes fadePulse {
      0%, 100% { opacity: 1; }
      50% { opacity: 0.3; }
    }
    @keyframes colorGlowPulse {
      0%, 100% { color: #f59e0b; text-shadow: 0 0 8px rgba(245, 158, 11, 0.4); }
      33% { color: #10b981; text-shadow: 0 0 12px rgba(16, 185, 129, 0.6); }
      66% { color: #3b82f6; text-shadow: 0 0 10px rgba(59, 130, 246, 0.5); }
    }
    .effect-marquee-preview {
      display: inline-block;
      white-space: nowrap;
      animation: marqueeScroll 14s linear infinite;
    }
    .effect-fade-preview {
      animation: fadePulse 2s ease-in-out infinite;
    }
    .effect-bounce-preview {
      animation: colorGlowPulse 3s ease-in-out infinite;
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
  public readonly showTelegramToken = signal<boolean>(false);
  public readonly testingTelegramBot = signal<boolean>(false);

  public readonly activeTab = signal<'general' | 'announcement' | 'maintenance' | 'backup'>('general');
  public readonly tokenCleanupDays = signal<number>(7);

  public readonly effectOptions = computed(() => [
    { label: this.lang.translate('settings.effect_marquee'), value: 'marquee' },
    { label: this.lang.translate('settings.effect_fade'), value: 'fade' },
    { label: this.lang.translate('settings.effect_bounce'), value: 'bounce' },
  ]);

  public form: SystemSettingsPayload = {
    admin_wallet_address: '',
    scanner_api_key: '',
    token_cleanup_days: 7,
    usdt_exchange_rate: 0,
    admin_binance_id: '',
    binance_api_key: '',
    binance_api_secret: '',
    telegram_bot_token: '',
    telegram_bot_username: 'isocalmarketnoti_bot',
    telegram_bot_enabled: true,
    announcement_enabled: true,
    announcement_text: 'Hệ thống tự động cập nhật sản phẩm liên tục 24/7. Chúc quý khách mua sắm vui vẻ!',
    announcement_effect: 'marquee'
  };

  ngOnInit(): void {
    this.routeSub = this.route.paramMap.subscribe(params => {
      const tab = params.get('tab');
      if (tab === 'announcement' || tab === 'maintenance' || tab === 'backup') {
        this.activeTab.set(tab as any);
      } else {
        this.activeTab.set('general');
      }
    });

    this.store.fetchSettings().subscribe({
      next: (settings) => {
        if (settings) {
          this.form = {
            ...settings,
            scanner_api_key: '',
            binance_api_key: '',
            binance_api_secret: '',
            telegram_bot_token: '',
            telegram_bot_username: settings.telegram_bot_username || 'isocalmarketnoti_bot',
            telegram_bot_enabled: settings.telegram_bot_enabled !== undefined ? Boolean(Number(settings.telegram_bot_enabled)) : true,
            announcement_enabled: settings.announcement_enabled !== undefined ? Boolean(Number(settings.announcement_enabled)) : true,
            announcement_text: settings.announcement_text ?? 'Hệ thống tự động cập nhật sản phẩm liên tục 24/7. Chúc quý khách mua sắm vui vẻ!',
            announcement_effect: settings.announcement_effect || 'marquee'
          };
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

  public toggleTelegramTokenVisibility(): void {
    this.showTelegramToken.set(!this.showTelegramToken());
  }

  public testTelegramBotConnection(): void {
    if (this.testingTelegramBot()) return;
    this.testingTelegramBot.set(true);

    const token = this.form.telegram_bot_token ? this.form.telegram_bot_token.trim() : undefined;
    this.store.testTelegramBot(token).subscribe({
      next: (res) => {
        this.testingTelegramBot.set(false);
        const botName = res.bot?.username ? `@${res.bot.username}` : '';
        this.toast.showToast(
          `${this.lang.translate('admin_settings.telegram_test_success')} ${botName}`,
          'success'
        );
      },
      error: (err) => {
        this.testingTelegramBot.set(false);
        const errMsg = err.error?.message || this.lang.translate('admin_settings.telegram_test_failed');
        this.toast.showToast(errMsg, 'error');
      }
    });
  }

  public changeTab(tab: 'general' | 'announcement' | 'maintenance' | 'backup'): void {
    this.router.navigate(['/admin/settings', tab]);
  }

  public onSubmit(): void {
    if (this.submitting() || this.isLocked()) return;

    const scannerApiKey = this.form.scanner_api_key?.trim();

    if (this.activeTab() === 'general') {
      const walletAddress = (this.form.admin_wallet_address || '').trim();
      if (!walletAddress) {
        this.toast.showToast(this.lang.translate('toast.wallet_required'), 'warning');
        return;
      }

      const walletRegex = /^0x[a-fA-F0-9]{40}$/;
      if (!walletRegex.test(walletAddress)) {
        this.toast.showToast(this.lang.translate('toast.wallet_invalid'), 'error');
        return;
      }

      if (!scannerApiKey && !this.form.has_scanner_api_key) {
        this.toast.showToast(this.lang.translate('toast.scanner_key_required'), 'warning');
        return;
      }
    }

    const binanceId = this.form.admin_binance_id?.trim();
    const binanceKey = this.form.binance_api_key?.trim();
    const binanceSecret = this.form.binance_api_secret?.trim();

    const hasStoredBinanceCredentials = this.form.has_binance_api_key && this.form.has_binance_api_secret;
    if (binanceId || binanceKey || binanceSecret || hasStoredBinanceCredentials) {
      if (!binanceId) {
        this.toast.showToast(this.lang.translate('toast.binance_id_required'), 'warning');
        return;
      }
      if (!binanceKey && !this.form.has_binance_api_key) {
        this.toast.showToast(this.lang.translate('toast.binance_key_required'), 'warning');
        return;
      }
      if (!binanceSecret && !this.form.has_binance_api_secret) {
        this.toast.showToast(this.lang.translate('toast.binance_secret_required'), 'warning');
        return;
      }
    }

    const payload: SystemSettingsPayload = { ...this.form };
    if (!scannerApiKey) delete payload.scanner_api_key;
    if (!binanceKey) delete payload.binance_api_key;
    if (!binanceSecret) delete payload.binance_api_secret;

    this.store.updateSettings(payload).subscribe({
      next: () => {
        this.form.scanner_api_key = '';
        this.form.binance_api_key = '';
        this.form.binance_api_secret = '';
        if (scannerApiKey) this.form.has_scanner_api_key = true;
        if (binanceKey) this.form.has_binance_api_key = true;
        if (binanceSecret) this.form.has_binance_api_secret = true;
        this.toast.showToast(this.lang.translate('toast.settings_updated'), 'success');
        this.isLocked.set(true);
      },
      error: (err) => {
        const errorMsg = err?.error?.errors?.admin_wallet_address?.[0] || err?.error?.message || this.lang.translate('toast.settings_update_failed');
        this.toast.showToast(errorMsg, 'error');
      }
    });
  }

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
    } else if (action === 'reset-profit') {
      this.modalService.confirm({
        title: this.lang.translate('modal.reset_profit_title'),
        description: this.lang.translate('modal.reset_profit_desc'),
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
