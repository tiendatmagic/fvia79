import { Injectable, inject, signal } from '@angular/core';
import { HttpParams } from '@angular/common/http';
import { ApiService } from '@core/services/api.service';
import { ToastService } from '@core/services/toast.service';
import { tap, catchError, finalize } from 'rxjs/operators';
import { of } from 'rxjs';

@Injectable({
  providedIn: 'root'
})
export class ProductStore {
  private readonly api = inject(ApiService);
  private readonly toast = inject(ToastService);

  // State Signals
  public readonly products = signal<any[]>([]);
  public readonly loading = signal<boolean>(false);
  public readonly totalProducts = signal<number>(0);
  public readonly currentPage = signal<number>(1);
  public readonly lastPage = signal<number>(1);

  public fetchProducts(page?: number, limit?: number) {
    this.loading.set(true);
    let params = new HttpParams();
    if (page !== undefined) {
      params = params.set('page', page.toString());
    }
    if (limit !== undefined) {
      params = params.set('limit', limit.toString());
    }

    return this.api.get<any>('v1/products', params).pipe(
      tap((res) => {
        if (res && res.data) {
          this.products.set(res.data);
          this.totalProducts.set(res.meta.total);
          this.currentPage.set(res.meta.page);
          this.lastPage.set(res.meta.last_page);
        } else {
          this.products.set(res);
          this.totalProducts.set(res.length);
          this.currentPage.set(1);
          this.lastPage.set(1);
        }
      }),
      catchError((err) => {
        this.toast.showToast('Không thể tải danh sách sản phẩm!', 'error');
        return of([]);
      }),
      finalize(() => this.loading.set(false))
    );
  }


  public createProduct(data: any) {
    this.loading.set(true);
    return this.api.post<any>('v1/products', data).pipe(
      tap((res) => {
        this.toast.showToast('Tạo sản phẩm thành công!', 'success');
        this.fetchProducts().subscribe();
      }),
      catchError((err) => {
        const msg = err.error?.message || 'Tạo sản phẩm thất bại!';
        this.toast.showToast(msg, 'error');
        return of(null);
      }),
      finalize(() => this.loading.set(false))
    );
  }

  public updateProduct(id: number, data: any) {
    this.loading.set(true);
    return this.api.put<any>(`v1/products/${id}`, data).pipe(
      tap(() => {
        this.toast.showToast('Cập nhật sản phẩm thành công!', 'success');
        this.fetchProducts().subscribe();
      }),
      catchError((err) => {
        const msg = err.error?.message || 'Cập nhật thất bại!';
        this.toast.showToast(msg, 'error');
        return of(null);
      }),
      finalize(() => this.loading.set(false))
    );
  }

  public deleteProduct(id: number) {
    this.loading.set(true);
    return this.api.delete<any>(`v1/products/${id}`).pipe(
      tap(() => {
        this.toast.showToast('Xóa sản phẩm thành công!', 'success');
        this.fetchProducts().subscribe();
      }),
      catchError((err) => {
        const msg = err.error?.message || 'Xóa sản phẩm thất bại!';
        this.toast.showToast(msg, 'error');
        return of(null);
      }),
      finalize(() => this.loading.set(false))
    );
  }
}
