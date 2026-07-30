import { Injectable, inject, signal } from '@angular/core';
import { HttpParams } from '@angular/common/http';
import { ApiService } from '@core/services/api.service';
import { ToastService } from '@core/services/toast.service';
import { LanguageService } from '@core/services/language.service';
import { tap, catchError, finalize } from 'rxjs/operators';
import { of, Subscription, Subject } from 'rxjs';

@Injectable({
  providedIn: 'root'
})
export class OrderStore {
  private readonly api = inject(ApiService);
  private readonly toast = inject(ToastService);
  public readonly lang = inject(LanguageService);

  private activeFetchOrdersSub?: Subscription;

  private readonly ordersCache = new Map<string, { data: any; timestamp: number }>();

  public clearCache() {
    this.ordersCache.clear();
  }

  public readonly orders = signal<any[]>([]);
  public readonly loading = signal<boolean>(false);
  public readonly currentOrderDetail = signal<any | null>(null);
  public readonly totalOrders = signal<number>(0);
  public readonly currentPage = signal<number>(1);
  public readonly lastPage = signal<number>(1);

  public fetchOrders(
    page?: number,
    limit?: number,
    search?: string,
    status?: string,
    startDate?: string,
    endDate?: string
  ) {
    const cacheKey = JSON.stringify({ page, limit, search, status, startDate, endDate });
    const cached = this.ordersCache.get(cacheKey);
    if (cached && (Date.now() - cached.timestamp < 30000)) {
      const res = cached.data;
      if (res && res.data) {
        this.orders.set(res.data);
        this.totalOrders.set(res.meta.total);
        this.currentPage.set(res.meta.page);
        this.lastPage.set(res.meta.last_page);
      } else {
        this.orders.set(res || []);
        this.totalOrders.set((res || []).length);
        this.currentPage.set(1);
        this.lastPage.set(1);
      }
      return of(res);
    }

    if (this.activeFetchOrdersSub) {
      this.activeFetchOrdersSub.unsubscribe();
    }

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
    if (status) {
      params = params.set('status', status);
    }
    if (startDate) {
      params = params.set('start_date', startDate);
    }
    if (endDate) {
      params = params.set('end_date', endDate);
    }

    const subject = new Subject<any>();

    const req$ = this.api.get<any>('v1/orders', params).pipe(
      tap((res) => {
        this.ordersCache.set(cacheKey, { data: res, timestamp: Date.now() });
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
        this.toast.showToast(this.lang.translate('toast.error_load_orders'), 'error');
        return of([]);
      }),
      finalize(() => this.loading.set(false))
    );

    this.activeFetchOrdersSub = req$.subscribe({
      next: (res) => {
        subject.next(res);
        subject.complete();
      },
      error: (err) => {
        subject.error(err);
      }
    });

    return subject.asObservable();
  }

  public fetchOrderDetail(id: number) {
    this.loading.set(true);
    this.currentOrderDetail.set(null);
    return this.api.get<any>(`v1/orders/${id}`).pipe(
      tap((res) => {
        this.currentOrderDetail.set(res);
      }),
      catchError((err) => {
        const msg = err.error?.message || this.lang.translate('toast.error_load_order_detail');
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
        this.clearCache();
        this.toast.showToast(this.lang.translate('cart.order_success'), 'success');
        this.fetchOrders().subscribe();
      }),
      catchError((err) => {
        const msg = err.error?.message || this.lang.translate('toast.order_failed');
        this.toast.showToast(msg, 'error');
        return of(null);
      }),
      finalize(() => this.loading.set(false))
    );
  }
}
