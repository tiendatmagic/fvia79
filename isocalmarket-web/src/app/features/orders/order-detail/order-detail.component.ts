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

@Component({
  selector: 'app-order-detail',
  standalone: true,
  imports: [
    CommonModule,
    RouterModule,
    SkeletonLoaderComponent,
    BadgeComponent,
    CardComponent,
    IconComponent
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
        this.state.showToast('Không thể tải chi tiết đơn hàng!', 'error');
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
        this.state.showToast('Đã copy thông tin tài khoản!', 'success');
      }).catch(() => {
        this.state.showToast('Không thể copy tự động!', 'error');
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
    this.state.showToast('Đã tải xuống danh sách tài khoản dạng .txt!', 'success');
  }

  public parseAccountData(data: string): { label: string, value: string, is2FA?: boolean }[] {
    if (!data || !data.includes('|')) {
      return [{ label: 'Dữ liệu thô', value: data }];
    }
    const parts = data.split('|').map(p => p.trim());
    const result: { label: string, value: string, is2FA?: boolean }[] = [];
    
    if (parts[0]) result.push({ label: 'Tài khoản (UID)', value: parts[0] });
    if (parts[1]) result.push({ label: 'Mật khẩu', value: parts[1] });
    if (parts[2]) {
      result.push({ label: 'Mã 2FA (Secret)', value: parts[2], is2FA: true });
    }
    if (parts[3]) result.push({ label: 'Email khôi phục', value: parts[3] });
    if (parts[4]) result.push({ label: 'Mật khẩu email', value: parts[4] });
    
    for (let i = 5; i < parts.length; i++) {
      result.push({ label: `Thông tin khác ${i - 4}`, value: parts[i] });
    }
    
    return result;
  }

  public get2FAOtp(secret: string, index: number) {
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
                this.state.showToast('Đã copy mã OTP: ' + code, 'success');
              });
            }
          } else {
            this.currentOtpCode.set(token);
          }
        } else {
          this.state.showToast('Không lấy được mã OTP, vui lòng kiểm tra lại Secret Key!', 'error');
        }
      },
      error: () => {
        this.loadingOtp.set(false);
        this.state.showToast('Lỗi kết nối API 2fa.live!', 'error');
      }
    });
  }
}
