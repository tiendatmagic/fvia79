import { Injectable, inject, signal } from '@angular/core';
import { ApiService } from '@core/services/api.service';
import { ToastService } from '@core/services/toast.service';
import { LanguageService } from '@core/services/language.service';
import { HttpParams } from '@angular/common/http';
import { tap, finalize, catchError } from 'rxjs/operators';
import { Observable, of, Subscription, Subject } from 'rxjs';

@Injectable({
  providedIn: 'root'
})
export class UserStore {
  private readonly api = inject(ApiService);
  private readonly toast = inject(ToastService);
  public readonly lang = inject(LanguageService);
 
  private activeFetchUsersSub?: Subscription;
  private readonly usersCache = new Map<string, { data: any; timestamp: number }>();
 
  public readonly users = signal<any[]>([]);
  public readonly totalItems = signal<number>(0);
  public readonly lastPage = signal<number>(1);
  public readonly currentPage = signal<number>(1);
  public readonly loading = signal<boolean>(false);

  public clearCache() {
    this.usersCache.clear();
  }

  public fetchUsers(page: number, limit: number, search?: string, role?: string, startDate?: string, endDate?: string): Observable<any> {
    if (this.activeFetchUsersSub) {
      this.activeFetchUsersSub.unsubscribe();
    }

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

    const subject = new Subject<any>();

    const req$ = this.api.get<any>('v1/admin/users', params).pipe(
      tap((res) => {
        if (res && res.data) {
          this.users.set(res.data);
          this.totalItems.set(res.meta.total);
          this.currentPage.set(res.meta.page);
          this.lastPage.set(res.meta.last_page);
        } else {
          this.users.set(res || []);
          this.totalItems.set((res || []).length);
          this.currentPage.set(1);
          this.lastPage.set(1);
        }
      }),
      catchError((err) => {
        this.toast.showToast(this.lang.translate('toast.user_load_failed'), 'error');
        return of(null);
      }),
      finalize(() => this.loading.set(false))
    );

    this.activeFetchUsersSub = req$.subscribe({
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

  public createUser(data: any): Observable<any> {
    this.loading.set(true);
    return this.api.post<any>('v1/admin/users', data).pipe(
      tap(() => {
        this.clearCache();
        this.toast.showToast(this.lang.translate('toast.user_created'), 'success');
      }),
      catchError((err) => {
        const msg = err.error?.message || this.lang.translate('toast.user_create_failed');
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
        this.clearCache();
        this.toast.showToast(this.lang.translate('toast.user_updated'), 'success');
      }),
      catchError((err) => {
        const msg = err.error?.message || this.lang.translate('toast.user_update_failed');
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
        this.clearCache();
        this.toast.showToast(this.lang.translate('toast.user_deleted'), 'success');
      }),
      catchError((err) => {
        const msg = err.error?.message || this.lang.translate('toast.user_delete_failed');
        this.toast.showToast(msg, 'error');
        return of(null);
      }),
      finalize(() => this.loading.set(false))
    );
  }

  public adjustBalance(id: string, type: 'add' | 'subtract', amount: number, reason: string, affectCumulative: boolean = true): Observable<any> {
    this.loading.set(true);
    return this.api.post<any>(`v1/admin/users/${id}/adjust-balance`, { type, amount, reason, affect_cumulative_deposit: affectCumulative }).pipe(
      tap(() => {
        this.clearCache();
        this.toast.showToast(this.lang.translate('toast.user_balance_adjusted'), 'success');
      }),
      catchError((err) => {
        let msg = err.error?.message || this.lang.translate('toast.user_balance_failed');
        if (msg.includes('Insufficient user balance')) {
          msg = this.lang.translate('toast.user_balance_insufficient');
        }
        this.toast.showToast(msg, 'error');
        return of(null);
      }),
      finalize(() => this.loading.set(false))
    );
  }

  public adjustCumulativeDeposit(id: string, type: 'add' | 'subtract' | 'set', amount: number, reason: string): Observable<any> {
    this.loading.set(true);
    return this.api.post<any>(`v1/admin/users/${id}/adjust-cumulative-deposit`, { type, amount, reason }).pipe(
      tap(() => {
        this.clearCache();
        this.toast.showToast(this.lang.translate('toast.cumulative_deposit_adjusted'), 'success');
      }),
      catchError((err) => {
        const msg = err.error?.message || this.lang.translate('toast.cumulative_deposit_adjust_failed');
        this.toast.showToast(msg, 'error');
        return of(null);
      }),
      finalize(() => this.loading.set(false))
    );
  }

  public bulkUpdateStatus(userIds: string[], status: 'active' | 'banned'): Observable<any> {
    this.loading.set(true);
    return this.api.post<any>('v1/admin/users/bulk-status', { user_ids: userIds, status }).pipe(
      tap(() => {
        this.clearCache();
        this.toast.showToast(this.lang.translate('toast.bulk_user_status_updated'), 'success');
      }),
      catchError((err) => {
        const msg = err.error?.message || this.lang.translate('toast.user_update_failed');
        this.toast.showToast(msg, 'error');
        return of(null);
      }),
      finalize(() => this.loading.set(false))
    );
  }

  public bulkDeleteUsers(userIds: string[]): Observable<any> {
    this.loading.set(true);
    return this.api.post<any>('v1/admin/users/bulk-delete', { user_ids: userIds }).pipe(
      tap(() => {
        this.clearCache();
        this.toast.showToast(this.lang.translate('toast.bulk_users_deleted'), 'success');
      }),
      catchError((err) => {
        const msg = err.error?.message || this.lang.translate('toast.user_delete_failed');
        this.toast.showToast(msg, 'error');
        return of(null);
      }),
      finalize(() => this.loading.set(false))
    );
  }
}
