import { Component, inject, OnInit, OnDestroy, signal, HostListener } from '@angular/core';
import { CommonModule } from '@angular/common';
import { ActivatedRoute, Router, RouterModule } from '@angular/router';
import { StateService } from '@core/services/state.service';
import { ModalService } from '@core/services/modal.service';
import { ButtonComponent } from '@shared/components/button/button.component';
import { CardComponent } from '@shared/components/card/card.component';
import { IconComponent } from '@shared/components/icon/icon.component';
import { QrCodeModule } from 'ng-qrcode';
import { DepositHistoryItem } from '../deposit-history/deposit-history.component';
import { TranslatePipe } from '@shared/pipes/translate.pipe';
import { LanguageService } from '@core/services/language.service';
import { Subscription, interval, timer } from 'rxjs';
import { finalize } from 'rxjs/operators';

import { QrZoomModalComponent } from './qr-zoom-modal.component';

const DEPOSIT_EXPIRATION_MS = 15 * 60 * 1000;

@Component({
  selector: 'app-deposit-invoice',
  standalone: true,
  imports: [
    CommonModule,
    RouterModule,
    ButtonComponent,
    CardComponent,
    IconComponent,
    QrCodeModule,
    TranslatePipe
  ],
  templateUrl: './deposit-invoice.component.html',
  styles: [`
    :host {
      display: block;
    }
  `]
})
export class DepositInvoiceComponent implements OnInit, OnDestroy {
  private readonly route = inject(ActivatedRoute);
  private readonly router = inject(Router);
  public readonly state = inject(StateService);
  private readonly modalService = inject(ModalService);
  public readonly lang = inject(LanguageService);

  public readonly adminWalletAddress = signal('');

  public invoice: DepositHistoryItem | null = null;

  public readonly isLoadingInvoice = signal(true);
  public readonly invoiceError = signal<string | null>(null);
  public readonly imageLoadError = signal(false);
  public openQrZoom(): void {
    this.modalService.open(QrZoomModalComponent, {
      title: this.invoice?.paymentMethod === 'binance' ? this.lang.translate('invoice.qr_zoom_binance_title') : this.lang.translate('invoice.qr_zoom_bep20_title'),
      size: 'lg',
      data: {
        paymentMethod: this.invoice?.paymentMethod,
        binancePayRawLink: this.binancePayRawLink,
        adminWalletAddress: this.adminWalletAddress(),
        imageLoadError: this.imageLoadError()
      }
    });
  }

  public onImageError(): void {
    this.imageLoadError.set(true);
  }

  public readonly timeLeft = signal(900);
  public readonly countdownText = signal('15:00');
  private timerSubscription: Subscription | null = null;
  private pollingSubscription: Subscription | null = null;
  private expirationTime = 0;

  public isCancelling = false;
  public isChecking = false;

  public get isAdmin(): boolean {
    return this.state.authStore.currentUser()?.role === 'admin';
  }

  public get binancePayRawLink(): string {
    const raw = (this.invoice?.adminBinancePayLink || '').trim();
    if (!raw) return '';
    if (raw.startsWith('/storage/')) {
      if (typeof window !== 'undefined' && window.location.hostname === 'localhost') {
        return `http://localhost:8000${raw}`;
      }
    }
    return raw;
  }

  private visibilityHandler = () => {
    if (document.hidden) {
      this.stopStatusPolling();
    } else if (this.invoice && this.invoice.id && this.invoice.status === 'pending_payment') {
      this.startStatusPolling(this.invoice.id);
    }
  };

  private routeSub: Subscription | null = null;

  ngOnInit() {
    this.routeSub = this.route.paramMap.subscribe(params => {
      const id = params.get('id');
      if (id) {
        this.loadInvoice(id);
      }
    });
    if (typeof document !== 'undefined') {
      document.addEventListener('visibilitychange', this.visibilityHandler);
    }
  }

  ngOnDestroy() {
    if (this.routeSub) {
      this.routeSub.unsubscribe();
      this.routeSub = null;
    }
    this.stopCountdown();
    this.stopStatusPolling();
    if (typeof document !== 'undefined') {
      document.removeEventListener('visibilitychange', this.visibilityHandler);
    }
  }

  private loadInvoice(invoiceId: string) {
    this.stopCountdown();
    this.stopStatusPolling();
    this.isChecking = false;
    this.isLoadingInvoice.set(true);
    this.invoiceError.set(null);

    this.state.depositStore.fetchInvoice(invoiceId).subscribe({
      next: (res) => {
        this.isLoadingInvoice.set(false);
        this.invoice = res.invoice;

        if (!this.invoice) {
          this.invoiceError.set(this.lang.translate('invoice.not_found_or_forbidden'));
          return;
        }

        const method = this.invoice.paymentMethod || 'bep20';
        if (method === 'bep20') {
          const wallet = this.invoice.adminWalletAddress || '';
          this.adminWalletAddress.set(wallet);
        }

        if (this.invoice.status === 'pending_payment') {
          let createdTime = NaN;
          if (this.invoice.createdAt) {
            let isoStr = String(this.invoice.createdAt).replace(' ', 'T');
            if (!isoStr.endsWith('Z') && !isoStr.includes('+')) {
              isoStr += 'Z';
            }
            createdTime = new Date(isoStr).getTime();
          }

          if (isNaN(createdTime)) {
            createdTime = Date.now();
          }

          this.expirationTime = createdTime + DEPOSIT_EXPIRATION_MS;
          const nowTime = Date.now();
          const remaining = Math.floor((this.expirationTime - nowTime) / 1000);

          if (remaining <= 0) {
            this.timeLeft.set(0);
            setTimeout(() => {
              this.onInvoiceExpired();
            }, 50);
          } else {
            this.timeLeft.set(remaining);
            this.startCountdown();
            this.startStatusPolling(this.invoice.id);
          }
        } else {
          this.stopCountdown();
          this.stopStatusPolling();
          this.timeLeft.set(0);
          this.countdownText.set('00:00');
        }
      },
      error: (err) => {
        this.isLoadingInvoice.set(false);
        const status = err?.status;
        const errMsg = err?.error?.message || '';

        if (errMsg.includes('configured the receiving USDT wallet')) {
          this.invoiceError.set(this.lang.translate('invoice.wallet_admin'));
        } else if (errMsg.includes('configured the scanner API key')) {
          this.invoiceError.set(this.lang.translate('invoice.scanner_admin'));
        } else if (status === 404) {
          this.invoiceError.set(this.lang.translate('invoice.not_found_or_forbidden'));
        } else if (status === 401 || status === 403) {
          this.invoiceError.set(this.lang.translate('invoice.login_required'));
        } else {
          this.invoiceError.set(this.lang.translate('invoice.detail_failed'));
        }
      }
    });
  }

  private startCountdown() {
    this.stopCountdown();
    this.updateTimerDisplay();

    this.timerSubscription = interval(1000).subscribe(() => {
      this.updateTimerDisplay();
    });
  }

  private updateTimerDisplay() {
    const nowTime = Date.now();
    const remaining = Math.max(0, Math.floor((this.expirationTime - nowTime) / 1000));

    this.timeLeft.set(remaining);
    const mins = Math.floor(remaining / 60);
    const secs = remaining % 60;
    this.countdownText.set(`${mins.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`);

    if (remaining <= 0) {
      this.onInvoiceExpired();
    }
  }

  private stopCountdown() {
    if (this.timerSubscription) {
      this.timerSubscription.unsubscribe();
      this.timerSubscription = null;
    }
  }

  private startStatusPolling(invoiceId: string) {
    this.stopStatusPolling();
    this.pollingSubscription = timer(5000, 15000).subscribe(() => {
      if (typeof document !== 'undefined' && document.hidden) {
        return;
      }

      if (this.isChecking) {
        return;
      }

      this.isChecking = true;

      this.state.depositStore.checkTransaction(invoiceId).pipe(
        finalize(() => this.isChecking = false)
      ).subscribe({
        next: (res) => {
          if (res.success) {
            this.showSuccessModal();
          }
        }
      });
    });
  }

  private stopStatusPolling() {
    if (this.pollingSubscription) {
      this.pollingSubscription.unsubscribe();
      this.pollingSubscription = null;
    }
  }

  private onInvoiceExpired() {
    this.stopCountdown();
    if (this.invoice && !this.isCancelling) {
      this.isCancelling = true;
      this.state.depositStore.cancelInvoice(this.invoice.id).pipe(
        finalize(() => this.isCancelling = false)
      ).subscribe({
        next: () => {
          this.state.showToast(this.lang.translate('toast.deposit_auto_cancelled'), 'error');
          setTimeout(() => {
            this.router.navigate(['/deposit/history']);
          }, 50);
        },
        error: () => {
          setTimeout(() => {
            this.router.navigate(['/deposit/history']);
          }, 50);
        }
      });
    }
  }

  public copyText(text: string, label: string) {
    navigator.clipboard.writeText(text).then(() => {
      this.state.showToast(this.lang.translate('toast.copied_text') + ' ' + label, 'success');
    }).catch(() => {
      this.state.showToast(this.lang.translate('toast.copy_error_text') + ' ' + label, 'error');
    });
  }

  public cancelInvoice() {
    if (!this.invoice || this.isCancelling) return;

    this.modalService.confirm({
      title: this.lang.translate('modal.cancel_deposit_title'),
      descriptionHtml: `
        <div class="space-y-3">
          <p class="text-sm text-slate-600 dark:text-slate-400">${this.lang.translate('modal.cancel_deposit_desc')}</p>
          <div class="p-3 bg-amber-50 dark:bg-amber-950/20 border border-amber-200/50 dark:border-amber-800/40 rounded-xl flex gap-2">
            <svg class="w-5 h-5 text-amber-500 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
            <p class="text-xs text-amber-700 dark:text-amber-400 font-bold leading-normal">
              ${this.lang.translate('modal.cancel_deposit_warning')}
            </p>
          </div>
        </div>
      `,
      confirmText: this.lang.translate('modal.cancel_deposit_confirm'),
      cancelText: this.lang.translate('action.back'),
      confirmButtonClass: 'btn-danger'
    }).afterClosed$.subscribe((confirmed?: boolean) => {
      if (confirmed) {
        this.isCancelling = true;
        this.state.depositStore.cancelInvoice(this.invoice!.id).pipe(
          finalize(() => this.isCancelling = false)
        ).subscribe({
          next: () => {
            this.stopCountdown();
            this.stopStatusPolling();
            this.state.showToast(this.lang.translate('toast.deposit_cancelled_success'), 'success');
            this.router.navigate(['/deposit/history']);
          },
          error: (err) => {
            const msg = err?.error?.message || this.lang.translate('toast.deposit_cancel_failed');
            this.state.showToast(msg, 'error');
          }
        });
      }
    });
  }

  public checkTransaction() {
    if (this.isChecking || !this.invoice) return;
    this.isChecking = true;

    if (this.invoice.paymentMethod === 'binance') {
      this.state.showToast(this.lang.translate('toast.bot_scanning_binance'), 'warning');
    } else {
      this.state.showToast(this.lang.translate('toast.bot_scanning_bsc'), 'warning');
    }

    this.state.depositStore.checkTransaction(this.invoice.id).pipe(
      finalize(() => this.isChecking = false)
    ).subscribe({
      next: (res) => {
        if (res.success) {
          this.showSuccessModal();
        } else {
          this.state.showToast(this.translateCheckMessage(res.message), 'error');
        }
      },
      error: (err) => {
        const rawMsg = err?.error?.message || '';
        const msg = this.translateCheckMessage(rawMsg);
        this.state.showToast(msg, 'error');
      }
    });
  }

  private translateCheckMessage(msg: string): string {
    if (!msg) {
      return this.lang.translate('invoice.transaction_not_found');
    }

    const lowerMsg = msg.toLowerCase();

    if (lowerMsg.includes('receiving usdt wallet') || lowerMsg.includes('receiving wallet')) {
      return this.lang.translate('invoice.reconcile_wallet_error');
    }

    if (lowerMsg.includes('scanner api key')) {
      return this.lang.translate('invoice.reconcile_scanner_error');
    }

    if (lowerMsg.includes('binance api key/secret') || lowerMsg.includes('binance api key') || lowerMsg.includes('binance_api_key')) {
      return this.lang.translate('invoice.reconcile_binance_error');
    }

    if (lowerMsg.includes('blockchain scanner api') || lowerMsg.includes('deep-index.moralis.io')) {
      return this.lang.translate('invoice.blockchain_api_error');
    }

    if (lowerMsg.includes('binance pay api') || lowerMsg.includes('api.binance.com')) {
      return this.lang.translate('invoice.binance_api_error');
    }

    if (lowerMsg.includes('could not detect') || lowerMsg.includes('not detect your usdt payment')) {
      if (this.invoice?.paymentMethod === 'binance') {
        return this.lang.translate('invoice.binance_transaction_missing');
      } else {
        return this.lang.translate('invoice.bsc_transaction_missing');
      }
    }

    return msg;
  }

  private showSuccessModal() {
    this.stopCountdown();
    this.stopStatusPolling();
    this.state.authStore.checkMe(true).subscribe();

    this.modalService.confirm({
      title: this.lang.translate('modal.deposit_success_title'),
      variant: 'success',
      description: this.lang.translate('modal.deposit_success_desc'),
      confirmText: this.lang.translate('modal.deposit_success_confirm'),
      cancelText: this.lang.translate('common.close'),
      confirmButtonClass: 'btn-primary'
    }).afterClosed$.subscribe((viewHistory?: boolean) => {
      if (viewHistory) {
        this.router.navigate(['/deposit/history']);
      } else {
        this.router.navigate(['/storefront']);
      }
    });
  }
}
