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
    this.loading.set(true);
    let params = new HttpParams()
      .set('page', page.toString())
      .set('limit', limit.toString());

    if (search) {
      params = params.set('search', search);
    }

    return this.api.get<any>('v1/categories', params).pipe(
      tap((res) => {
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
        this.toast.showToast('Không thể tải danh sách danh mục phân trang!', 'error');
        return of([]);
      }),
      finalize(() => this.loading.set(false))
    );
  }

  public fetchCategories(search?: string) {
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
        this.toast.showToast('Không thể tải danh sách danh mục!', 'error');
        return of([]);
      })
    );
  }

  public fetchProducts(page?: number, limit?: number, categoryId?: string, search?: string, status?: string) {
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
      }),
      catchError((err) => {
        const msg = err.error?.message || 'Tạo sản phẩm thất bại!';
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
        this.toast.showToast('Cập nhật sản phẩm thành công!', 'success');
      }),
      catchError((err) => {
        const msg = err.error?.message || 'Cập nhật thất bại!';
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
        this.toast.showToast('Xóa sản phẩm thành công!', 'success');
      }),
      catchError((err) => {
        const msg = err.error?.message || 'Xóa sản phẩm thất bại!';
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
        this.toast.showToast('Tạo danh mục thành công!', 'success');
      }),
      catchError((err) => {
        const msg = err.error?.message || 'Tạo danh mục thất bại!';
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
        this.toast.showToast('Cập nhật danh mục thành công!', 'success');
      }),
      catchError((err) => {
        const msg = err.error?.message || 'Cập nhật danh mục thất bại!';
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
        this.toast.showToast('Xóa danh mục thành công!', 'success');
      }),
      catchError((err) => {
        let msg = err.error?.message || 'Xóa danh mục thất bại!';
        if (
          msg.includes('Cannot delete category containing active products') ||
          msg.includes('foreign key constraint fails') ||
          msg.includes('Integrity constraint violation')
        ) {
          msg = 'Không thể xóa danh mục này vì hiện đang có sản phẩm thuộc danh mục!';
        }
        this.toast.showToast(msg, 'error');
        return of(null);
      }),
      finalize(() => this.loading.set(false))
    );
  }
}
