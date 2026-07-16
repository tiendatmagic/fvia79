import { Injectable, inject, signal } from '@angular/core';
import { HttpParams } from '@angular/common/http';
import { ApiService } from '@core/services/api.service';
import { ToastService } from '@core/services/toast.service';
import { tap, catchError, finalize } from 'rxjs/operators';
import { of } from 'rxjs';

@Injectable({
  providedIn: 'root'
})
export class OrderStore {
  private readonly api = inject(ApiService);
  private readonly toast = inject(ToastService);

  // State Signals
  public readonly orders = signal<any[]>([]);
  public readonly loading = signal<boolean>(false);
  public readonly currentOrderDetail = signal<any | null>(null);
  public readonly totalOrders = signal<number>(0);
  public readonly currentPage = signal<number>(1);
  public readonly lastPage = signal<number>(1);

  public fetchOrders(page?: number, limit?: number, search?: string) {
    this.loading.set(true);
    let params = new HttpParams();
    if (page !== undefined) {
      params = params.set('page', page.toString());
    }
    if (limit !== undefined) {
      params = params.set('limit', limit.toString());
    }
    if (search) {
      params = params.set('search', search);
    }

    return this.api.get<any>('v1/orders', params).pipe(
      tap((res) => {
        if (res && res.data) {
          this.orders.set(res.data);
          this.totalOrders.set(res.meta.total);
          this.currentPage.set(res.meta.page);
          this.lastPage.set(res.meta.last_page);
        } else {
          this.orders.set(res);
          this.totalOrders.set(res.length);
          this.currentPage.set(1);
          this.lastPage.set(1);
        }
      }),
      catchError(() => {
        this.toast.showToast('Không thể tải danh sách đơn hàng!', 'error');
        return of([]);
      }),
      finalize(() => this.loading.set(false))
    );
  }


  public fetchOrderDetail(id: number) {
    this.loading.set(true);
    this.currentOrderDetail.set(null);
    return this.api.get<any>(`v1/orders/${id}`).pipe(
      tap((res) => {
        this.currentOrderDetail.set(res);
      }),
      catchError((err) => {
        const msg = err.error?.message || 'Không thể lấy thông tin chi tiết đơn hàng!';
        this.toast.showToast(msg, 'error');
        return of(null);
      }),
      finalize(() => this.loading.set(false))
    );
  }

  public createOrder(items: { product_id: number; quantity: number }[]) {
    this.loading.set(true);
    return this.api.post<any>('v1/orders', { items }).pipe(
      tap((res) => {
        this.toast.showToast('Đặt hàng thành công!', 'success');
        this.fetchOrders().subscribe();
      }),
      catchError((err) => {
        const msg = err.error?.message || 'Đặt hàng thất bại!';
        this.toast.showToast(msg, 'error');
        return of(null);
      }),
      finalize(() => this.loading.set(false))
    );
  }
}
