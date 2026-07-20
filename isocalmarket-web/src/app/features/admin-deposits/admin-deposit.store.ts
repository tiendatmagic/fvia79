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
export class AdminDepositStore {
  private readonly api = inject(ApiService);
  private readonly toast = inject(ToastService);
  public readonly lang = inject(LanguageService);

  private activeFetchDepositsSub?: Subscription;
  private readonly depositsCache = new Map<string, { data: any; timestamp: number }>();

  public clearCache() {
    this.depositsCache.clear();
  }

  public readonly deposits = signal<any[]>([]);
  public readonly totalItems = signal<number>(0);
  public readonly lastPage = signal<number>(1);
  public readonly currentPage = signal<number>(1);
  public readonly loading = signal<boolean>(false);

  public fetchDeposits(
    page: number,
    limit: number,
    search?: string,
    status?: string,
    gateway?: string,
    startDate?: string,
    endDate?: string
  ): Observable<any> {
    const cacheKey = JSON.stringify({ page, limit, search, status, gateway, startDate, endDate });
    const cached = this.depositsCache.get(cacheKey);

    if (cached && (Date.now() - cached.timestamp < 30000)) { // Cache 30s
      const res = cached.data;
      if (res) {
        this.deposits.set(res.data || []);
        this.totalItems.set(res.meta?.total || 0);
        this.currentPage.set(res.meta?.page || 1);
        this.lastPage.set(res.meta?.last_page || 1);
      }
      return of(res);
    }

    if (this.activeFetchDepositsSub) {
      this.activeFetchDepositsSub.unsubscribe();
    }

    this.loading.set(true);

    let params = new HttpParams()
      .set('page', page.toString())
      .set('limit', limit.toString());

    if (search) {
      params = params.set('search', search);
    }
    if (status) {
      params = params.set('status', status);
    }
    if (gateway) {
      params = params.set('gateway', gateway);
    }
    if (startDate) {
      params = params.set('start_date', startDate);
    }
    if (endDate) {
      params = params.set('end_date', endDate);
    }

    const subject = new Subject<any>();

    const req$ = this.api.get<any>('v1/admin/deposits', params).pipe(
      tap((res) => {
        this.depositsCache.set(cacheKey, { data: res, timestamp: Date.now() });
        if (res) {
          this.deposits.set(res.data || []);
          this.totalItems.set(res.meta?.total || 0);
          this.currentPage.set(res.meta?.page || 1);
          this.lastPage.set(res.meta?.last_page || 1);
        }
      }),
      catchError((err) => {
        this.toast.showToast(this.lang.translate('toast.error_load_deposits'), 'error');
        return of(null);
      }),
      finalize(() => this.loading.set(false))
    );

    this.activeFetchDepositsSub = req$.subscribe({
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

  public approveDeposit(id: string, txHash: string): Observable<any> {
    this.loading.set(true);
    return this.api.post<any>(`v1/admin/deposits/${id}/approve`, { tx_hash: txHash }).pipe(
      tap((res) => {
        this.toast.showToast(this.lang.translate('toast.deposit_approved'), 'success');
        this.clearCache();
      }),
      catchError((err) => {
        const errorMsg = err.error?.message || this.lang.translate('toast.deposit_approve_failed');
        this.toast.showToast(errorMsg, 'error');
        return of(null);
      }),
      finalize(() => this.loading.set(false))
    );
  }

  public rejectDeposit(id: string): Observable<any> {
    this.loading.set(true);
    return this.api.post<any>(`v1/admin/deposits/${id}/reject`, {}).pipe(
      tap((res) => {
        this.toast.showToast(this.lang.translate('toast.deposit_rejected'), 'success');
        this.clearCache();
      }),
      catchError((err) => {
        const errorMsg = err.error?.message || this.lang.translate('toast.deposit_reject_failed');
        this.toast.showToast(errorMsg, 'error');
        return of(null);
      }),
      finalize(() => this.loading.set(false))
    );
  }
}
