import { Injectable, inject, signal } from '@angular/core';
import { ApiService } from '@core/services/api.service';
import { ToastService } from '@core/services/toast.service';
import { HttpParams } from '@angular/common/http';
import { tap, catchError, finalize } from 'rxjs/operators';
import { of } from 'rxjs';

@Injectable({
  providedIn: 'root'
})
export class InventoryStore {
  private readonly api = inject(ApiService);
  private readonly toast = inject(ToastService);

  public readonly items = signal<any[]>([]);
  public readonly loading = signal<boolean>(false);
  public readonly totalItems = signal<number>(0);
  public readonly currentPage = signal<number>(1);
  public readonly lastPage = signal<number>(1);

  public fetchItems(filters: { product_id?: string; status?: string; search?: string } = {}, page?: number, limit?: number) {
    this.loading.set(true);
    let params = new HttpParams();
    if (filters.product_id) {
      params = params.set('product_id', filters.product_id);
    }
    if (filters.status) {
      params = params.set('status', filters.status);
    }
    if (filters.search) {
      params = params.set('search', filters.search);
    }
    if (page !== undefined) {
      params = params.set('page', page.toString());
    }
    if (limit !== undefined) {
      params = params.set('limit', limit.toString());
    }

    return this.api.get<any>('v1/inventory/items', params).pipe(
      tap((res) => {
        if (res && res.data) {
          this.items.set(res.data);
          this.totalItems.set(res.meta.total);
          this.currentPage.set(res.meta.page);
          this.lastPage.set(res.meta.last_page);
        } else {
          this.items.set(res);
          this.totalItems.set(res.length);
          this.currentPage.set(1);
          this.lastPage.set(1);
        }
      }),
      catchError(() => {
        this.toast.showToast('Không thể tải dữ liệu kho!', 'error');
        return of([]);
      }),
      finalize(() => this.loading.set(false))
    );
  }

  public importItems(productId: number, rawData: string) {
    this.loading.set(true);
    return this.api.post<any>('v1/inventory/import', { product_id: productId, raw_data: rawData }).pipe(
      tap((res) => {
        this.toast.showToast(res.message || 'Import kho thành công!', 'success');
      }),
      catchError((err) => {
        const msg = err.error?.message || 'Import kho thất bại!';
        this.toast.showToast(msg, 'error');
        return of(null);
      }),
      finalize(() => this.loading.set(false))
    );
  }

  public deleteItem(id: number, productIdFilter?: string) {
    this.loading.set(true);
    return this.api.delete<any>(`v1/inventory/items/${id}`).pipe(
      tap(() => {
        this.toast.showToast('Xóa tài nguyên kho thành công!', 'success');
      }),
      catchError((err) => {
        const msg = err.error?.message || 'Xóa thất bại!';
        this.toast.showToast(msg, 'error');
        return of(null);
      }),
      finalize(() => this.loading.set(false))
    );
  }
}
