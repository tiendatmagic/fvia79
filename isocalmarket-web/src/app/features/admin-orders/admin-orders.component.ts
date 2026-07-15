import { Component, OnInit, inject, signal } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { ApiService } from '@core/services/api.service';
import { StateService } from '@core/services/state.service';
import { SkeletonLoaderComponent } from '@shared/components/skeleton-loader/skeleton-loader.component';
import { PaginationComponent } from '@shared/components/pagination/pagination.component';
import { HttpParams } from '@angular/common/http';
import { ModalService } from '@core/services/modal.service';
import { OrderDetailModalComponent } from '../orders/order-detail-modal/order-detail-modal.component';

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
  private readonly modalService = inject(ModalService);

  public readonly orders = signal<any[]>([]);
  public readonly total = signal<number>(0);
  public readonly totalPages = signal<number>(1);
  public readonly loading = signal<boolean>(true);

  public readonly currentPage = signal<number>(1);
  public readonly limit = 10;
  public readonly selectedStatus = signal<string>(''); // '' đại diện cho Tất cả

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

    this.apiService.get<any>('v1/orders', params).subscribe({
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
    this.modalService.open(OrderDetailModalComponent, {
      title: 'Chi Tiết Đơn Hàng',
      size: '2xl',
      data: {
        orderId: orderId
      }
    });
  }
}
