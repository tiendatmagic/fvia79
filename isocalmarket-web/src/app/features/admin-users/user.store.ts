import { Injectable, inject, signal } from '@angular/core';
import { ApiService } from '@core/services/api.service';
import { ToastService } from '@core/services/toast.service';
import { HttpParams } from '@angular/common/http';
import { tap, finalize, catchError } from 'rxjs/operators';
import { Observable, of } from 'rxjs';

@Injectable({
  providedIn: 'root'
})
export class UserStore {
  private readonly api = inject(ApiService);
  private readonly toast = inject(ToastService);

  public readonly users = signal<any[]>([]);
  public readonly totalItems = signal<number>(0);
  public readonly lastPage = signal<number>(1);
  public readonly currentPage = signal<number>(1);
  public readonly loading = signal<boolean>(false);

    public fetchUsers(page: number, limit: number, search?: string, role?: string, startDate?: string, endDate?: string): Observable<any> {
    this.loading.set(true);

    let params = new HttpParams()
      .set('page', page.toString())
      .set('limit', limit.toString());

    if (search) {
      params = params.set('search', search);
    }

    if (role) {
      params = params.set('role', role);
    }

    if (startDate) {
      params = params.set('start_date', startDate);
    }

    if (endDate) {
      params = params.set('end_date', endDate);
    }

    return this.api.get<any>('v1/admin/users', params).pipe(
      tap((res) => {
        if (res) {
          this.users.set(res.data || []);
          this.totalItems.set(res.meta?.total || 0);
          this.currentPage.set(res.meta?.page || 1);
          this.lastPage.set(res.meta?.last_page || 1);
        }
      }),
      catchError((err) => {
        this.toast.showToast('Không thể tải danh sách thành viên!', 'error');
        return of(null);
      }),
      finalize(() => this.loading.set(false))
    );
  }

    public createUser(data: any): Observable<any> {
    this.loading.set(true);
    return this.api.post<any>('v1/admin/users', data).pipe(
      tap(() => {
        this.toast.showToast('Tạo thành viên mới thành công!', 'success');
      }),
      catchError((err) => {
        const msg = err.error?.message || 'Không thể tạo thành viên mới!';
        this.toast.showToast(msg, 'error');
        return of(null);
      }),
      finalize(() => this.loading.set(false))
    );
  }

    public updateUser(id: string, data: any): Observable<any> {
    this.loading.set(true);
    return this.api.put<any>(`v1/admin/users/${id}`, data).pipe(
      tap(() => {
        this.toast.showToast('Cập nhật thành viên thành công!', 'success');
      }),
      catchError((err) => {
        const msg = err.error?.message || 'Cập nhật thành viên thất bại!';
        this.toast.showToast(msg, 'error');
        return of(null);
      }),
      finalize(() => this.loading.set(false))
    );
  }

    public deleteUser(id: string): Observable<any> {
    this.loading.set(true);
    return this.api.delete<any>(`v1/admin/users/${id}`).pipe(
      tap(() => {
        this.toast.showToast('Xóa thành viên thành công!', 'success');
      }),
      catchError((err) => {
        const msg = err.error?.message || 'Xóa thành viên thất bại!';
        this.toast.showToast(msg, 'error');
        return of(null);
      }),
      finalize(() => this.loading.set(false))
    );
  }
}
