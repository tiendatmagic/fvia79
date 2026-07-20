import { Injectable, inject, signal } from '@angular/core';
import { HttpParams } from '@angular/common/http';
import { ApiService } from '@core/services/api.service';
import { ToastService } from '@core/services/toast.service';
import { tap, catchError, finalize } from 'rxjs/operators';
import { of, Subscription, Subject } from 'rxjs';

@Injectable({
  providedIn: 'root'
})
export class CouponStore {
  private readonly api = inject(ApiService);
  private readonly toast = inject(ToastService);

  private activeFetchCouponsSub?: Subscription;

  public readonly coupons = signal<any[]>([]);
  public readonly loading = signal<boolean>(false);
  public readonly totalCoupons = signal<number>(0);
  public readonly currentPage = signal<number>(1);
  public readonly lastPage = signal<number>(1);

  public fetchCoupons(page: number, limit: number, search?: string) {
    if (this.activeFetchCouponsSub) {
      this.activeFetchCouponsSub.unsubscribe();
    }

    this.loading.set(true);
    let params = new HttpParams()
      .set('page', page.toString())
      .set('limit', limit.toString());

    if (search) {
      params = params.set('search', search);
    }

    const subject = new Subject<any>();

    const req$ = this.api.get<any>('v1/admin/coupons', params).pipe(
      tap((res) => {
        if (res && res.data) {
          this.coupons.set(res.data);
          this.totalCoupons.set(res.meta.total);
          this.currentPage.set(res.meta.page);
          this.lastPage.set(res.meta.last_page);
        } else {
          this.coupons.set(res || []);
          this.totalCoupons.set((res || []).length);
          this.currentPage.set(1);
          this.lastPage.set(1);
        }
      }),
      catchError((err) => {
        this.toast.showToast('Không thể tải danh sách mã giảm giá!', 'error');
        return of([]);
      }),
      finalize(() => this.loading.set(false))
    );

    this.activeFetchCouponsSub = req$.subscribe({
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

  public createCoupon(data: any) {
    this.loading.set(true);
    return this.api.post<any>('v1/admin/coupons', data).pipe(
      tap(() => {
        this.toast.showToast('Tạo mã giảm giá thành công!', 'success');
      }),
      catchError((err) => {
        const msg = err.error?.message || 'Tạo mã giảm giá thất bại!';
        this.toast.showToast(msg, 'error');
        return of(null);
      }),
      finalize(() => this.loading.set(false))
    );
  }

  public updateCoupon(id: string, data: any) {
    this.loading.set(true);
    return this.api.put<any>(`v1/admin/coupons/${id}`, data).pipe(
      tap(() => {
        this.toast.showToast('Cập nhật mã giảm giá thành công!', 'success');
      }),
      catchError((err) => {
        const msg = err.error?.message || 'Cập nhật mã giảm giá thất bại!';
        this.toast.showToast(msg, 'error');
        return of(null);
      }),
      finalize(() => this.loading.set(false))
    );
  }

  public deleteCoupon(id: string) {
    this.loading.set(true);
    return this.api.delete<any>(`v1/admin/coupons/${id}`).pipe(
      tap(() => {
        this.toast.showToast('Xóa mã giảm giá thành công!', 'success');
      }),
      catchError((err) => {
        const msg = err.error?.message || 'Xóa mã giảm giá thất bại!';
        this.toast.showToast(msg, 'error');
        return of(null);
      }),
      finalize(() => this.loading.set(false))
    );
  }

  public validateCoupon(code: string, cartTotal: number) {
    return this.api.post<any>('v1/coupons/validate', { code, cart_total: cartTotal }).pipe(
      catchError((err) => {
        const msg = err.error?.message || 'Mã giảm giá không hợp lệ!';
        throw new Error(msg);
      })
    );
  }
}
