import { Component, inject, OnInit, signal } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { Router, RouterModule } from '@angular/router';
import { StateService } from '@core/services/state.service';
import { ModalService } from '@core/services/modal.service';
import { CustomInputComponent } from '@shared/components/custom-input/custom-input.component';
import { ButtonComponent } from '@shared/components/button/button.component';
import { PageHeaderComponent } from '@shared/components/page-header/page-header.component';
import { CardComponent } from '@shared/components/card/card.component';
import { IconComponent } from '@shared/components/icon/icon.component';
import { SkeletonLoaderComponent } from '@shared/components/skeleton-loader/skeleton-loader.component';
import { TranslatePipe } from '@shared/pipes/translate.pipe';
import { LanguageService } from '@core/services/language.service';

@Component({
  selector: 'app-deposit',
  standalone: true,
  imports: [
    CommonModule,
    FormsModule,
    RouterModule,
    CustomInputComponent,
    ButtonComponent,
    PageHeaderComponent,
    CardComponent,
    IconComponent,
    SkeletonLoaderComponent,
    TranslatePipe
  ],
  templateUrl: './deposit.component.html',
  styles: [`
    :host {
      display: block;
    }
  `]
})
export class DepositComponent implements OnInit {
  public readonly state = inject(StateService);
  private readonly router = inject(Router);
  private readonly modalService = inject(ModalService);
  public readonly lang = inject(LanguageService);

  public amountUsdt: number | null = null;
  public vndEquivalent = 0;
  public selectedNetwork = 'BEP20';
  public paymentMethod = 'binance';
  public payerBinanceId = '';
  public readonly isSubmitting = signal(false);

  ngOnInit() {
    this.state.authStore.checkMe(true).subscribe();
    this.state.publicSettingStore.fetchPublicSettings().subscribe({
      next: (settings) => {
        if (settings) {
          if (settings.payment_binance_enabled) {
            this.paymentMethod = 'binance';
          } else if (settings.payment_bep20_enabled) {
            this.paymentMethod = 'bep20';
          } else {
            this.paymentMethod = '';
          }
        }
      }
    });
    this.state.depositStore.fetchRate().subscribe({
      next: () => this.onAmountChange(),
      error: () => this.state.showToast(this.lang.translate('toast.coingecko_error'), 'error')
    });
  }

  public onAmountChange() {
    const rate = this.state.depositStore.rate();
    if (this.amountUsdt && this.amountUsdt > 0) {
      this.vndEquivalent = this.amountUsdt * rate;
    } else {
      this.vndEquivalent = 0;
    }
  }

  public onCreateInvoice() {
    if (this.isSubmitting()) return;

    if (!this.paymentMethod) {
      this.state.showToast(this.lang.translate('deposit.all_methods_disabled'), 'error');
      return;
    }

    if (this.amountUsdt === null || this.amountUsdt === undefined || this.amountUsdt < 0.1 || this.amountUsdt > 100000) {
      if (this.amountUsdt !== null && this.amountUsdt !== undefined && this.amountUsdt > 100000) {
        this.state.showToast(this.lang.translate('toast.deposit_max_limit'), 'error');
      } else {
        this.state.showToast(this.lang.translate('toast.deposit_min_limit'), 'error');
      }
      return;
    }

    this.isSubmitting.set(true);

    this.state.depositStore.createDeposit(
      this.amountUsdt, 
      this.paymentMethod
    ).subscribe({
      next: (res) => {
        this.state.showToast(this.lang.translate('toast.deposit_created_success'), 'success');
        setTimeout(() => {
          this.isSubmitting.set(false);
          this.router.navigate(['/deposit/invoice', res.invoice.id]);
        }, 800);
      },
      error: (err) => {
        this.isSubmitting.set(false);
        const rawMsg = err?.error?.message;
        let msg = typeof rawMsg === 'string' ? rawMsg : this.lang.translate('toast.deposit_create_failed');

        if (msg.toLowerCase().includes('at least 0.1')) {
          msg = this.lang.translate('toast.deposit_min_limit');
          this.state.showToast(msg, 'error');
        } else if (msg.toLowerCase().includes('greater than 100000') || msg.includes('100000')) {
          msg = this.lang.translate('toast.deposit_max_limit');
          this.state.showToast(msg, 'error');
        } else if (msg.includes('configured the receiving USDT wallet')) {
          msg = this.lang.translate('toast.admin_wallet_not_configured');
          this.state.showToast(msg, 'error');
        } else if (msg.includes('configured the scanner API key')) {
          msg = this.lang.translate('toast.scanner_key_not_configured');
          this.state.showToast(msg, 'error');
        } else if (msg.includes('already have an active deposit order')) {
          const activeInvoiceId = err?.error?.active_invoice_id;

          this.modalService.confirm({
            title: this.lang.translate('modal.pending_deposit_title'),
            description: this.lang.translate('modal.pending_deposit_desc'),
            confirmText: this.lang.translate('modal.pending_deposit_confirm'),
            cancelText: this.lang.translate('modal.pending_deposit_cancel'),
            confirmButtonClass: 'btn-primary',
            size: 'md'
          }).afterClosed$.subscribe((confirmed) => {
            if (confirmed) {
              if (activeInvoiceId) {
                this.router.navigate(['/deposit/invoice', activeInvoiceId]);
              } else {
                this.router.navigate(['/deposit/history']);
              }
            }
          });
        } else {
          this.state.showToast(msg, 'error');
        }
      }
    });
  }
}
