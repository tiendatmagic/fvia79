import { CommonModule } from '@angular/common';
import { Component, effect, inject, OnDestroy, signal } from '@angular/core';
import { LanguageService } from '@core/services/language.service';
import { StateService } from '@core/services/state.service';
import type { MiniPurchase } from '@core/services/storefront-activity.store';
import { ToastService } from '@core/services/toast.service';
import { IconComponent } from '@shared/components/icon/icon.component';

@Component({
  selector: 'app-mini-purchase-notification',
  imports: [CommonModule, IconComponent],
  templateUrl: './mini-purchase-notification.component.html',
  styles: [`
    .mini-purchase {
      animation: mini-purchase-in 280ms cubic-bezier(.22, 1, .36, 1);
      box-shadow: 0 12px 32px -12px rgba(15, 23, 42, .25), 0 4px 10px -6px rgba(15, 23, 42, .16);
    }

    :host-context(.dark) .mini-purchase {
      box-shadow: 0 16px 36px -12px rgba(0, 0, 0, .6), 0 0 0 1px rgba(255, 255, 255, .02);
    }

    @keyframes mini-purchase-in {
      from { opacity: 0; transform: translateY(14px) scale(.97); }
      to { opacity: 1; transform: translateY(0); }
    }

    @media (prefers-reduced-motion: reduce) {
      .mini-purchase {
        animation: none;
      }
    }
  `]
})
export class MiniPurchaseNotificationComponent implements OnDestroy {
  public readonly state = inject(StateService);
  public readonly lang = inject(LanguageService);
  public readonly toast = inject(ToastService);

  public readonly notice = signal<MiniPurchase | null>(null);
  public readonly visible = signal(false);

  private pollTimer: number | null = null;
  private hideTimer: number | null = null;
  private requestInProgress = false;
  private lastRealPurchaseId: number | string | null = null;

  constructor() {
    effect(() => {
      if (this.state.isAuthenticated()) {
        this.startPolling();
      } else {
        this.stopPolling();
      }
    });
  }

  public ngOnDestroy(): void {
    this.stopPolling();
  }

  public dismiss(): void {
    this.visible.set(false);
    this.clearHideTimer();
  }

  public productName(purchase: MiniPurchase): string {
    if (this.lang.currentLang() === 'vi') {
      return purchase.product_name_vi || purchase.product_name || purchase.product_name_en || this.lang.translate('common.product');
    }

    return purchase.product_name_en || purchase.product_name || purchase.product_name_vi || this.lang.translate('common.product');
  }

  private startPolling(): void {
    if (this.pollTimer !== null || this.requestInProgress || typeof window === 'undefined') {
      return;
    }

    this.refresh(false, true);
  }

  private stopPolling(): void {
    if (this.pollTimer !== null && typeof window !== 'undefined') {
      window.clearInterval(this.pollTimer);
    }
    this.pollTimer = null;
    this.requestInProgress = false;
    this.dismiss();
  }

  private refresh(shouldDisplay: boolean, startPollingWhenEnabled = false): void {
    if (this.requestInProgress || !this.state.isAuthenticated()) {
      return;
    }

    this.requestInProgress = true;
    const forceFetch = shouldDisplay && !startPollingWhenEnabled;
    this.state.storefrontActivityStore.fetchMiniRecentPurchases(forceFetch).subscribe({
      next: response => {
        this.requestInProgress = false;
        if (!this.state.isAuthenticated() || !response?.enabled) {
          this.stopPolling();
          return;
        }

        if (startPollingWhenEnabled && this.pollTimer === null && typeof window !== 'undefined') {
          if (response.mode === 'real') {
            this.lastRealPurchaseId = response.data?.[0]?.id ?? null;
          }
          this.pollTimer = window.setInterval(() => this.refresh(true), 120_000);
        }

        if (shouldDisplay) {
          const purchase = response.mode === 'demo'
            ? this.createDemoPurchase()
            : this.pickRealPurchase(response.data || []);
          if (purchase) {
            this.show(purchase);
          }
        }
      },
      error: () => {
        this.requestInProgress = false;
      }
    });
  }

  private pickRealPurchase(purchases: MiniPurchase[]): MiniPurchase | null {
    if (purchases.length === 0) {
      return null;
    }

    const purchase = purchases[0];
    if (purchase.id === this.lastRealPurchaseId) {
      return null;
    }
    this.lastRealPurchaseId = purchase.id;
    return purchase;
  }

  private createDemoPurchase(): MiniPurchase {
    const names = ['nguy***', 'minh***', 'hoan***', 'tran***', 'user***'];
    const viProducts = ['Tài khoản quảng cáo', 'Tài khoản mạng xã hội', 'Via ngoại', 'BM đã xác minh'];
    const enProducts = ['Advertising account', 'Social media account', 'Foreign account', 'Verified BM'];
    const index = Math.floor(Math.random() * viProducts.length);

    return {
      id: `demo-${Date.now()}`,
      username: names[Math.floor(Math.random() * names.length)],
      quantity: Math.floor(Math.random() * 3) + 1,
      product_name_vi: viProducts[index],
      product_name_en: enProducts[index]
    };
  }

  private show(purchase: MiniPurchase): void {
    this.clearHideTimer();
    this.notice.set(purchase);
    this.visible.set(true);
    if (typeof window !== 'undefined') {
      this.hideTimer = window.setTimeout(() => this.visible.set(false), 3_000);
    }
  }

  private clearHideTimer(): void {
    if (this.hideTimer !== null && typeof window !== 'undefined') {
      window.clearTimeout(this.hideTimer);
    }
    this.hideTimer = null;
  }
}
