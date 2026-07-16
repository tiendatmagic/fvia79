import { Component, inject, OnInit, signal } from '@angular/core';
import { CommonModule } from '@angular/common';
import { ActivatedRoute, Router, RouterModule } from '@angular/router';
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
  public readonly state = inject(StateService);

  public orderId = '';
  public orderDetails = signal<any | null>(null);
  public loadingDetails = signal<boolean>(true);

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
}
