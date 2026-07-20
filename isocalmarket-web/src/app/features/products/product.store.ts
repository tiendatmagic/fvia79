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
export class ProductStore {
  private readonly api = inject(ApiService);
  private readonly toast = inject(ToastService);
  private readonly lang = inject(LanguageService);
 
  private activeFetchCategoriesPaginatedSub?: Subscription;
  private activeFetchProductsSub?: Subscription;
 
  private readonly productsCache = new Map<string, { data: any; timestamp: number }>();
  private readonly categoriesPaginatedCache = new Map<string, { data: any; timestamp: number }>();
 
  public clearCache() {
    this.productsCache.clear();
    this.categoriesPaginatedCache.clear();
  }

  public readonly products = signal<any[]>([]);
  public readonly categories = signal<any[]>([]);
  public readonly paginatedCategories = signal<any[]>([]);
  public readonly loading = signal<boolean>(false);
  public readonly totalProducts = signal<number>(0);
  public readonly currentPage = signal<number>(1);
  public readonly lastPage = signal<number>(1);
  public readonly totalCategories = signal<number>(0);
  public readonly currentCategoryPage = signal<number>(1);
  public readonly lastCategoryPage = signal<number>(1);

  public fetchCategoriesPaginated(page: number, limit: number, search?: string) {
    const cacheKey = JSON.stringify({ page, limit, search });
    const cached = this.categoriesPaginatedCache.get(cacheKey);
    if (cached && (Date.now() - cached.timestamp < 60000)) {
      const res = cached.data;
      if (res && res.data) {
        this.paginatedCategories.set(res.data);
        this.totalCategories.set(res.meta.total);
        this.currentCategoryPage.set(res.meta.page);
        this.lastCategoryPage.set(res.meta.last_page);
      } else {
        this.paginatedCategories.set(res || []);
        this.totalCategories.set((res || []).length);
        this.currentCategoryPage.set(1);
        this.lastCategoryPage.set(1);
      }
      return of(res);
    }
 
    if (this.activeFetchCategoriesPaginatedSub) {
      this.activeFetchCategoriesPaginatedSub.unsubscribe();
    }
 
    this.loading.set(true);
    let params = new HttpParams()
      .set('page', page.toString())
      .set('limit', limit.toString());
 
    if (search) {
      params = params.set('search', search);
    }
 
    const subject = new Subject<any>();
 
    const req$ = this.api.get<any>('v1/categories', params).pipe(
      tap((res) => {
        this.categoriesPaginatedCache.set(cacheKey, { data: res, timestamp: Date.now() });
        if (res && res.data) {
          this.paginatedCategories.set(res.data);
          this.totalCategories.set(res.meta.total);
          this.currentCategoryPage.set(res.meta.page);
          this.lastCategoryPage.set(res.meta.last_page);
        } else {
          this.paginatedCategories.set(res || []);
          this.totalCategories.set((res || []).length);
          this.currentCategoryPage.set(1);
          this.lastCategoryPage.set(1);
        }
      }),
      catchError((err) => {
        this.toast.showToast(this.lang.translate('toast.error_load_categories'), 'error');
        return of([]);
      }),
      finalize(() => this.loading.set(false))
    );
 
    this.activeFetchCategoriesPaginatedSub = req$.subscribe({
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

  public fetchCategories(search?: string) {
    if (!search && this.categories().length > 0) {
      this.api.get<any[]>('v1/categories').subscribe({
        next: (res) => {
          if (res) this.categories.set(res);
        }
      });
      return of(this.categories());
    }

    let params = new HttpParams();
    if (search) {
      params = params.set('search', search);
    }
    return this.api.get<any[]>('v1/categories', params).pipe(
      tap((res) => {
        if (res) {
          this.categories.set(res);
        }
      }),
      catchError((err) => {
        this.toast.showToast(this.lang.translate('toast.error_load_categories'), 'error');
        return of([]);
      })
    );
  }

  public getProductsRaw(page?: number, limit?: number, categoryId?: string, search?: string, status?: string, filterDeleted?: string) {
    let params = new HttpParams();
    if (page !== undefined) {
      params = params.set('page', page.toString());
    }
    if (limit !== undefined) {
      params = params.set('limit', limit.toString());
    }
    if (categoryId) {
      params = params.set('category_id', categoryId);
    }
    if (search) {
      params = params.set('search', search);
    }
    if (status) {
      params = params.set('status', status);
    }
    if (filterDeleted) {
      params = params.set('filter_deleted', filterDeleted);
    }
    return this.api.get<any>('v1/products', params);
  }

  public fetchGroupedProducts(limit?: number, search?: string) {
    let params = new HttpParams();
    if (limit !== undefined) {
      params = params.set('limit', limit.toString());
    }
    if (search) {
      params = params.set('search', search);
    }
    return this.api.get<any>('v1/products/grouped', params);
  }

  public fetchProducts(page?: number, limit?: number, categoryId?: string, search?: string, status?: string, filterDeleted?: string) {
    const isStorefrontLoad = page === undefined && limit === undefined && categoryId === undefined && search === undefined && status === 'active' && filterDeleted === undefined;
    if (!isStorefrontLoad) {
      const cacheKey = JSON.stringify({ page, limit, categoryId, search, status, filterDeleted });
      const cached = this.productsCache.get(cacheKey);
      if (cached && (Date.now() - cached.timestamp < 60000)) {
        const res = cached.data;
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
        return of(res);
      }
    }
 
    if (this.activeFetchProductsSub) {
      this.activeFetchProductsSub.unsubscribe();
    }
 
    if (isStorefrontLoad && this.products().length > 0) {
      let params = new HttpParams();
      if (status) {
        params = params.set('status', status);
      }
      this.api.get<any>('v1/products', params).subscribe({
        next: (res) => {
          if (res && res.data) {
            this.products.set(res.data);
          } else {
            this.products.set(res);
          }
        }
      });
      return of(this.products());
    }
 
    this.loading.set(true);
    let params = new HttpParams();
    if (page !== undefined) {
      params = params.set('page', page.toString());
    }
    if (limit !== undefined) {
      params = params.set('limit', limit.toString());
    }
    if (categoryId) {
      params = params.set('category_id', categoryId);
    }
    if (search) {
      params = params.set('search', search);
    }
    if (status) {
      params = params.set('status', status);
    }
    if (filterDeleted) {
      params = params.set('filter_deleted', filterDeleted);
    }
 
    const subject = new Subject<any>();
 
    const req$ = this.api.get<any>('v1/products', params).pipe(
      tap((res) => {
        if (!isStorefrontLoad) {
          const cacheKey = JSON.stringify({ page, limit, categoryId, search, status, filterDeleted });
          this.productsCache.set(cacheKey, { data: res, timestamp: Date.now() });
        }
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
        this.toast.showToast(this.lang.translate('toast.error_load_products'), 'error');
        return of([]);
      }),
      finalize(() => this.loading.set(false))
    );
 
    this.activeFetchProductsSub = req$.subscribe({
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

  public createProduct(data: any) {
    this.loading.set(true);
    return this.api.post<any>('v1/products', data).pipe(
      tap((res) => {
        this.clearCache();
        this.toast.showToast(this.lang.translate('toast.product_created'), 'success');
      }),
      catchError((err) => {
        const msg = err.error?.message || this.lang.translate('toast.product_create_failed');
        this.toast.showToast(msg, 'error');
        return of(null);
      }),
      finalize(() => this.loading.set(false))
    );
  }
 
  public updateProduct(id: string, data: any) {
    this.loading.set(true);
    return this.api.put<any>(`v1/products/${id}`, data).pipe(
      tap(() => {
        this.clearCache();
        this.toast.showToast(this.lang.translate('toast.product_updated'), 'success');
      }),
      catchError((err) => {
        const msg = err.error?.message || this.lang.translate('toast.product_update_failed');
        this.toast.showToast(msg, 'error');
        return of(null);
      }),
      finalize(() => this.loading.set(false))
    );
  }
 
  public deleteProduct(id: string) {
    this.loading.set(true);
    return this.api.delete<any>(`v1/products/${id}`).pipe(
      tap(() => {
        this.clearCache();
        this.toast.showToast(this.lang.translate('toast.product_deleted'), 'success');
      }),
      catchError((err) => {
        const msg = err.error?.message || this.lang.translate('toast.product_delete_failed');
        this.toast.showToast(msg, 'error');
        return of(null);
      }),
      finalize(() => this.loading.set(false))
    );
  }
 
  public restoreProduct(id: string) {
    this.loading.set(true);
    return this.api.post<any>(`v1/products/${id}/restore`, {}).pipe(
      tap(() => {
        this.clearCache();
        this.toast.showToast(this.lang.translate('toast.product_restored'), 'success');
      }),
      catchError((err) => {
        const msg = err.error?.message || this.lang.translate('toast.product_restore_failed');
        this.toast.showToast(msg, 'error');
        return of(null);
      }),
      finalize(() => this.loading.set(false))
    );
  }

  public createCategory(data: any) {
    this.loading.set(true);
    return this.api.post<any>('v1/categories', data).pipe(
      tap(() => {
        this.clearCache();
        this.toast.showToast(this.lang.translate('toast.category_created'), 'success');
      }),
      catchError((err) => {
        const msg = err.error?.message || this.lang.translate('toast.category_create_failed');
        this.toast.showToast(msg, 'error');
        return of(null);
      }),
      finalize(() => this.loading.set(false))
    );
  }
 
  public updateCategory(id: string, data: any) {
    this.loading.set(true);
    return this.api.put<any>(`v1/categories/${id}`, data).pipe(
      tap(() => {
        this.clearCache();
        this.toast.showToast(this.lang.translate('toast.category_updated'), 'success');
      }),
      catchError((err) => {
        const msg = err.error?.message || this.lang.translate('toast.category_update_failed');
        this.toast.showToast(msg, 'error');
        return of(null);
      }),
      finalize(() => this.loading.set(false))
    );
  }
 
  public deleteCategory(id: string) {
    this.loading.set(true);
    return this.api.delete<any>(`v1/categories/${id}`).pipe(
      tap(() => {
        this.clearCache();
        this.toast.showToast(this.lang.translate('toast.category_deleted'), 'success');
      }),
      catchError((err) => {
        let msg = err.error?.message || this.lang.translate('toast.category_delete_failed');
        if (
          msg.includes('Cannot delete category containing active products') ||
          msg.includes('foreign key constraint fails') ||
          msg.includes('Integrity constraint violation')
        ) {
          msg = this.lang.translate('toast.category_has_products');
        }
        this.toast.showToast(msg, 'error');
        return of(null);
      }),
      finalize(() => this.loading.set(false))
    );
  }
}
