import { Injectable, inject, signal } from '@angular/core';
import { ApiService } from '@core/services/api.service';
import { ToastService } from '@core/services/toast.service';
import { LanguageService } from '@core/services/language.service';
import { HttpParams } from '@angular/common/http';
import { tap, catchError, finalize } from 'rxjs/operators';
import { of, Subscription, Subject } from 'rxjs';

import { ProductStore } from '../products/product.store';

@Injectable({
  providedIn: 'root'
})
export class InventoryStore {
  private readonly api = inject(ApiService);
  private readonly toast = inject(ToastService);
  private readonly lang = inject(LanguageService);
  private readonly productStore = inject(ProductStore);
 
  private activeFetchItemsSub?: Subscription;
 
  private readonly itemsCache = new Map<string, { data: any; timestamp: number }>();
 
  public clearCache() {
    this.itemsCache.clear();
    this.productStore.clearCache();
  }

  public readonly items = signal<any[]>([]);
  public readonly loading = signal<boolean>(false);
  public readonly totalItems = signal<number>(0);
  public readonly currentPage = signal<number>(1);
  public readonly lastPage = signal<number>(1);

  public fetchItems(filters: { product_id?: string; status?: string; search?: string } = {}, page?: number, limit?: number) {
    const cacheKey = JSON.stringify({ filters, page, limit });
    const cached = this.itemsCache.get(cacheKey);
    if (cached && (Date.now() - cached.timestamp < 60000)) {
      const res = cached.data;
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
      return of(res);
    }
 
    if (this.activeFetchItemsSub) {
      this.activeFetchItemsSub.unsubscribe();
    }
 
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
 
    const subject = new Subject<any>();
 
    const req$ = this.api.get<any>('v1/inventory/items', params).pipe(
      tap((res) => {
        this.itemsCache.set(cacheKey, { data: res, timestamp: Date.now() });
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
        this.toast.showToast(this.lang.translate('toast.error_load_stock'), 'error');
        return of([]);
      }),
      finalize(() => this.loading.set(false))
    );
 
    this.activeFetchItemsSub = req$.subscribe({
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

  public importItems(productId: number, batchName: string, rawData: string) {
    this.loading.set(true);
    return this.api.post<any>('v1/inventory/import', {
      product_id: productId,
      batch_name: batchName,
      raw_data: rawData
    }).pipe(
      tap((res) => {
        this.clearCache();
        this.toast.showToast(this.lang.translate('toast.stock_imported'), 'success');
      }),
      catchError((err) => {
        const msg = err.error?.message || this.lang.translate('toast.stock_import_failed');
        this.toast.showToast(msg, 'error');
        return of(null);
      }),
      finalize(() => this.loading.set(false))
    );
  }
 
  public fetchItemDetail(id: string) {
    return this.api.get<any>(`v1/inventory/items/${id}`);
  }
 
  public updateItem(id: string, payload: { product_id: string; batch_name: string; keys_pool: string; status: string; last_updated_at?: string }) {
    this.loading.set(true);
    return this.api.put<any>(`v1/inventory/items/${id}`, payload).pipe(
      tap(() => {
        this.clearCache();
        this.toast.showToast(this.lang.translate('toast.stock_updated'), 'success');
      }),
      catchError((err) => {
        const msg = err.error?.message || this.lang.translate('toast.stock_update_failed');
        this.toast.showToast(msg, 'error');
        return of(null);
      }),
      finalize(() => this.loading.set(false))
    );
  }
 
  public deleteItem(id: string | number, productIdFilter?: string) {
    this.loading.set(true);
    return this.api.delete<any>(`v1/inventory/items/${id}`).pipe(
      tap(() => {
        this.clearCache();
        this.toast.showToast(this.lang.translate('toast.stock_deleted'), 'success');
      }),
      catchError((err) => {
        const msg = err.error?.message || this.lang.translate('toast.stock_delete_failed');
        this.toast.showToast(msg, 'error');
        return of(null);
      }),
      finalize(() => this.loading.set(false))
    );
  }

  // --- Resource Items (Digital Account Records) ---
  public readonly resourceItems = signal<any[]>([]);
  public readonly resourceLoading = signal<boolean>(false);
  public readonly resourceTotalItems = signal<number>(0);
  public readonly resourceCurrentPage = signal<number>(1);
  public readonly resourceLastPage = signal<number>(1);

  public fetchResourceItems(filters: { product_id?: string; batch_id?: string; status?: string; search?: string } = {}, page: number = 1, limit: number = 10) {
    this.resourceLoading.set(true);
    let params = new HttpParams();
    if (filters.product_id) params = params.set('product_id', filters.product_id);
    if (filters.batch_id) params = params.set('batch_id', filters.batch_id);
    if (filters.status) params = params.set('status', filters.status);
    if (filters.search) params = params.set('search', filters.search);
    params = params.set('page', page.toString());
    params = params.set('limit', limit.toString());

    return this.api.get<any>('v1/inventory/resource-items', params).pipe(
      tap((res) => {
        if (res && res.items) {
          this.resourceItems.set(res.items);
          this.resourceTotalItems.set(res.total);
          this.resourceCurrentPage.set(res.current_page);
          this.resourceLastPage.set(res.last_page);
        } else {
          this.resourceItems.set([]);
          this.resourceTotalItems.set(0);
          this.resourceCurrentPage.set(1);
          this.resourceLastPage.set(1);
        }
      }),
      catchError(() => {
        this.toast.showToast(this.lang.translate('toast.error_load_stock'), 'error');
        return of(null);
      }),
      finalize(() => this.resourceLoading.set(false))
    );
  }

  public updateResourceItem(id: string, payload: { secret_data?: string; status?: string }) {
    this.resourceLoading.set(true);
    return this.api.put<any>(`v1/inventory/resource-items/${id}`, payload).pipe(
      tap(() => {
        this.clearCache();
        this.toast.showToast(this.lang.translate('toast.stock_updated'), 'success');
      }),
      catchError((err) => {
        const msg = err.error?.message || this.lang.translate('toast.stock_update_failed');
        this.toast.showToast(msg, 'error');
        return of(null);
      }),
      finalize(() => this.resourceLoading.set(false))
    );
  }

  public deleteResourceItem(id: string) {
    this.resourceLoading.set(true);
    return this.api.delete<any>(`v1/inventory/resource-items/${id}`).pipe(
      tap(() => {
        this.clearCache();
        this.toast.showToast(this.lang.translate('toast.stock_deleted'), 'success');
      }),
      catchError((err) => {
        const msg = err.error?.message || this.lang.translate('toast.stock_delete_failed');
        this.toast.showToast(msg, 'error');
        return of(null);
      }),
      finalize(() => this.resourceLoading.set(false))
    );
  }
}
