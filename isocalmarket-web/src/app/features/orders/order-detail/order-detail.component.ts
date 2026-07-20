import { Component, inject, OnInit, signal } from '@angular/core';
import { CommonModule } from '@angular/common';
import { ActivatedRoute, Router, RouterModule } from '@angular/router';
import { HttpClient } from '@angular/common/http';
import { StateService } from '@core/services/state.service';
import { ApiService } from '@core/services/api.service';
import { SkeletonLoaderComponent } from '@shared/components/skeleton-loader/skeleton-loader.component';
import { BadgeComponent } from '@shared/components/badge/badge.component';
import { CardComponent } from '@shared/components/card/card.component';
import { IconComponent } from '@shared/components/icon/icon.component';
import { PageHeaderComponent } from '@shared/components/page-header/page-header.component';
import { TranslatePipe } from '@shared/pipes/translate.pipe';
import { CurrencyPipe } from '@shared/pipes/currency.pipe';
import { LanguageService } from '@core/services/language.service';
import { TooltipDirective } from '@shared/components/tooltip/tooltip.directive';

@Component({
  selector: 'app-order-detail',
  standalone: true,
  imports: [
    CommonModule,
    RouterModule,
    SkeletonLoaderComponent,
    BadgeComponent,
    CardComponent,
    IconComponent,
    PageHeaderComponent,
    TranslatePipe,
    CurrencyPipe,
    TooltipDirective
  ],
  templateUrl: './order-detail.component.html',
  styles: [`
    :host {
      display: block;
    }
  `]
})
export class OrderDetailComponent implements OnInit {
  private readonly route = inject(ActivatedRoute);
  private readonly router = inject(Router);
  private readonly apiService = inject(ApiService);
  private readonly http = inject(HttpClient);
  public readonly state = inject(StateService);
  public readonly lang = inject(LanguageService);

  public orderId = '';
  public orderDetails = signal<any | null>(null);
  public loadingDetails = signal<boolean>(true);

  public viewMode = signal<'parsed' | 'raw'>('parsed');
  public activeOtpIndex = signal<number | null>(null);
  public currentOtpCode = signal<string>('');
  public loadingOtp = signal<boolean>(false);

  ngOnInit() {
    this.orderId = this.route.snapshot.paramMap.get('id') ?? '';
    if (this.orderId) {
      this.fetchOrderDetails();
    } else {
      this.router.navigate(['/orders']);
    }
  }

  public fetchOrderDetails() {
    this.loadingDetails.set(true);
    this.apiService.get<any>(`v1/orders/${this.orderId}`).subscribe({
      next: (res) => {
        this.orderDetails.set(res);
        this.loadingDetails.set(false);
      },
      error: () => {
        this.state.showToast(this.lang.translate('toast.error_load_order_detail'), 'error');
        this.loadingDetails.set(false);
        this.router.navigate(['/orders']);
      }
    });
  }

  public goBack() {
    this.router.navigate(['/orders']);
  }

  public copyToClipboard(text: string) {
    if (typeof window !== 'undefined' && navigator.clipboard) {
      navigator.clipboard.writeText(text).then(() => {
        this.state.showToast(this.lang.translate('toast.account_copied'), 'success');
      }).catch(() => {
        this.state.showToast(this.lang.translate('toast.copy_error'), 'error');
      });
    }
  }

  public downloadAccounts() {
    const detail = this.orderDetails();
    if (!detail || !detail.accounts || detail.accounts.length === 0) return;

    const content = detail.accounts.map((acc: any) => acc.data).join('\n');
    const blob = new Blob([content], { type: 'text/plain;charset=utf-8' });
    const url = URL.createObjectURL(blob);

    const a = document.createElement('a');
    a.href = url;
    a.download = `isocalmarket_${detail.order_code}_accounts.txt`;
    document.body.appendChild(a);
    a.click();

    document.body.removeChild(a);
    URL.revokeObjectURL(url);
    this.state.showToast(this.lang.translate('toast.accounts_downloaded'), 'success');
  }

  public parseAccountData(data: string): { label: string, value: string, is2FA?: boolean }[] {
    if (!data || !data.includes('|')) {
      return [{ label: this.lang.translate('orders.raw_label'), value: data }];
    }
    const parts = data.split('|').map(p => p.trim());
    const result: { label: string, value: string, is2FA?: boolean }[] = [];
    
    if (parts[0]) result.push({ label: this.lang.translate('orders.account_uid'), value: parts[0] });
    if (parts[1]) result.push({ label: this.lang.translate('orders.password'), value: parts[1] });
    if (parts[2]) {
      result.push({ label: this.lang.translate('orders.secret_2fa'), value: parts[2], is2FA: true });
    }
    if (parts[3]) result.push({ label: this.lang.translate('orders.recovery_email'), value: parts[3] });
    if (parts[4]) result.push({ label: this.lang.translate('orders.email_password'), value: parts[4] });
    
    for (let i = 5; i < parts.length; i++) {
      result.push({ label: this.lang.translate('orders.other_info', { number: i - 4 }), value: parts[i] });
    }
    
    return result;
  }

  public get2FAOtp(secret: string, index: number) {
    if (this.loadingOtp()) return;
    this.activeOtpIndex.set(index);
    this.currentOtpCode.set('');
    this.loadingOtp.set(true);
    const cleanSecret = secret.replace(/\s+/g, '');

    this.http.get<any>(`https://2fa.live/tok/${cleanSecret}`).subscribe({
      next: (res) => {
        this.loadingOtp.set(false);
        if (res) {
          let token = '';
          if (typeof res === 'object') {
            token = res.token || res.code || '';
          } else {
            try {
              const parsed = JSON.parse(res);
              token = parsed.token || parsed.code || res;
            } catch (e) {
              token = res;
            }
          }
          
          const match = token.match(/\d{6}/);
          if (match) {
            const code = match[0];
            this.currentOtpCode.set(code);
            if (typeof window !== 'undefined' && navigator.clipboard) {
              navigator.clipboard.writeText(code).then(() => {
                this.state.showToast(this.lang.translate('toast.otp_copied') + code, 'success');
              });
            }
          } else {
            this.currentOtpCode.set(token);
          }
        } else {
          this.state.showToast(this.lang.translate('toast.otp_failed'), 'error');
        }
      },
      error: () => {
        this.loadingOtp.set(false);
        this.state.showToast(this.lang.translate('toast.api_2fa_error'), 'error');
      }
    });
  }
}
