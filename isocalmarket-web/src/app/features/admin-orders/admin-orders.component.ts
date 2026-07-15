import { Component, OnInit, inject, signal } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { ApiService } from '@core/services/api.service';
import { StateService } from '@core/services/state.service';
import { SkeletonLoaderComponent } from '@shared/components/skeleton-loader/skeleton-loader.component';
import { PaginationComponent } from '@shared/components/pagination/pagination.component';
import { HttpParams } from '@angular/common/http';

@Component({
  selector: 'app-admin-orders',
  standalone: true,
  imports: [
    CommonModule,
    FormsModule,
    SkeletonLoaderComponent,
    PaginationComponent
  ],
  templateUrl: './admin-orders.component.html',
  styles: [`
    :host {
      display: block;
    }
  `]
})
export class AdminOrdersComponent implements OnInit {
  private readonly apiService = inject(ApiService);
  public readonly stateService = inject(StateService);

  public readonly orders = signal<any[]>([]);
  public readonly total = signal<number>(0);
  public readonly totalPages = signal<number>(1);
  public readonly loading = signal<boolean>(true);

  public readonly currentPage = signal<number>(1);
  public readonly limit = 10;
  public readonly selectedStatus = signal<string>(''); // '' đại diện cho Tất cả

  // Chi tiết đơn hàng hiển thị trong Drawer
  public showDetailDrawer = false;
  public selectedOrderDetails = signal<any>(null);
  public loadingDetails = signal<boolean>(false);

  ngOnInit() {
    this.fetchOrders(1);
  }

  public fetchOrders(page: number) {
    this.currentPage.set(page);
    this.loading.set(true);

    let params = new HttpParams()
      .set('page', page.toString())
      .set('limit', this.limit.toString());

    if (this.selectedStatus()) {
      params = params.set('status', this.selectedStatus());
    }

    this.apiService.get<any>('orders', params).subscribe({
      next: (res) => {
        this.orders.set(res.data || []);
        const totalCount = res.meta?.total || 0;
        this.total.set(totalCount);
        this.totalPages.set(Math.ceil(totalCount / this.limit));
        this.loading.set(false);
      },
      error: (err) => {
        this.stateService.showToast('Không thể lấy danh sách đơn hàng!', 'error');
        this.loading.set(false);
      }
    });
  }

  public onStatusChange(status: string) {
    this.selectedStatus.set(status);
    this.fetchOrders(1);
  }

  public openDetailDrawer(orderId: string) {
    this.loadingDetails.set(true);
    this.showDetailDrawer = true;
    this.selectedOrderDetails.set(null);

    this.apiService.get<any>(`orders/${orderId}`).subscribe({
      next: (res) => {
        this.selectedOrderDetails.set(res);
        this.loadingDetails.set(false);
      },
      error: (err) => {
        this.stateService.showToast('Không thể tải chi tiết đơn hàng!', 'error');
        this.loadingDetails.set(false);
        this.showDetailDrawer = false;
      }
    });
  }

  public closeDetailDrawer() {
    this.showDetailDrawer = false;
    this.selectedOrderDetails.set(null);
  }

  public copyToClipboard(text: string) {
    if (typeof window !== 'undefined' && navigator.clipboard) {
      navigator.clipboard.writeText(text).then(() => {
        this.stateService.showToast('Đã copy thông tin tài khoản!', 'success');
      }).catch(() => {
        this.stateService.showToast('Không thể copy tự động!', 'error');
      });
    }
  }

  public downloadAccounts() {
    const detail = this.selectedOrderDetails();
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
    this.stateService.showToast('Đã tải xuống file .txt!', 'success');
  }
}
