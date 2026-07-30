import { CommonModule } from '@angular/common';
import { Component, effect, inject, OnDestroy, signal } from '@angular/core';
import { ApiService } from '@core/services/api.service';
import { LanguageService } from '@core/services/language.service';
import { StateService } from '@core/services/state.service';
import { ToastService } from '@core/services/toast.service';
import { IconComponent } from '@shared/components/icon/icon.component';

interface MiniPurchase {
  id: number | string;
  username: string;
  quantity: number;
  product_name?: string;
  product_name_vi?: string;
  product_name_en?: string;
}

interface MiniPurchaseResponse {
  enabled: boolean;
  mode: 'demo' | 'real';
  data: MiniPurchase[];
}

@Component({
  selector: 'app-mini-purchase-notification',
  imports: [CommonModule, IconComponent],
  template: `
    @if (state.isAuthenticated() && visible() && notice(); as purchase) {
      <aside
        class="mini-purchase fixed left-3 bottom-4 sm:left-5 sm:bottom-5 z-[9000] w-[calc(100vw-1.5rem)] max-w-[260px] sm:w-[272px] sm:max-w-none overflow-hidden rounded-2xl bg-white/95 dark:bg-slate-900/95 backdrop-blur-xl transition-[bottom] duration-200"
        [class.!bottom-24]="toast.toasts().length > 0"
        role="status"
        aria-live="polite"
      >
        <button
          type="button"
          class="absolute right-2 top-2 z-10 flex h-6 w-6 items-center justify-center rounded-full bg-slate-100/90 text-sm leading-none text-slate-400 transition-colors hover:bg-slate-200 hover:text-slate-700 dark:bg-slate-800 dark:text-slate-500 dark:hover:bg-slate-700 dark:hover:text-slate-200"
          (click)="dismiss()"
          [attr.aria-label]="lang.translate('common.close')"
        >
          &times;
        </button>
        <div class="flex items-center gap-2.5 py-3 pl-4 pr-9">
          <span class="relative flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-emerald-50 to-teal-100 text-emerald-600 ring-1 ring-emerald-200/70 dark:from-emerald-500/15 dark:to-teal-500/10 dark:text-emerald-400 dark:ring-emerald-400/20">
            <app-icon name="shopping-bag" class="h-[18px] w-[18px]" />
            <span class="absolute -right-0.5 -top-0.5 h-2.5 w-2.5 rounded-full border-2 border-white bg-emerald-500 dark:border-slate-900"></span>
          </span>
          <div class="min-w-0">
            <p class="truncate text-[10px] sm:text-[11px] font-medium text-slate-500 dark:text-slate-400">
              <span class="font-bold text-slate-700 dark:text-slate-200">{{ purchase.username }}</span>
              {{ lang.translate('mini_purchase.bought') }}
            </p>
            <div class="mt-1 flex min-w-0 items-start gap-1.5">
              <span class="shrink-0 rounded-md bg-emerald-50 px-1.5 py-0.5 text-[9px] sm:text-[10px] font-extrabold text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300">{{ purchase.quantity }}&times;</span>
              <p class="line-clamp-2 text-[10px] sm:text-[11px] font-bold leading-4 text-slate-900 dark:text-white">{{ productName(purchase) }}</p>
            </div>
            <p class="mt-1 flex items-center gap-1 text-[9px] text-slate-400 dark:text-slate-500">
              <span class="h-1 w-1 rounded-full bg-emerald-500"></span>{{ lang.translate('mini_purchase.just_now') }}
            </p>
          </div>
        </div>
      </aside>
    }
  `,
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
  private readonly api = inject(ApiService);
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
      return purchase.product_name_vi || purchase.product_name || purchase.product_name_en || 'Sản phẩm';
    }

    return purchase.product_name_en || purchase.product_name || purchase.product_name_vi || 'Product';
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
    this.api.get<MiniPurchaseResponse>('v1/storefront/mini-recent-purchases').subscribe({
      next: response => {
        this.requestInProgress = false;
        if (!this.state.isAuthenticated() || !response.enabled) {
          this.stopPolling();
          return;
        }

        if (startPollingWhenEnabled && this.pollTimer === null && typeof window !== 'undefined') {
          this.pollTimer = window.setInterval(() => this.refresh(true), 60_000);
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

    const purchase = purchases.find(item => item.id !== this.lastRealPurchaseId) || purchases[0];
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
