import { Injectable, inject, signal } from '@angular/core';
import { ApiService } from '@core/services/api.service';
import { HttpParams } from '@angular/common/http';
import { tap, finalize } from 'rxjs/operators';
import { Observable, Subscription, Subject } from 'rxjs';
import { DepositHistoryItem } from '../deposit-history/deposit-history.component';
import { CurrencyService } from '@core/services/currency.service';

@Injectable({
  providedIn: 'root'
})
export class DepositStore {
  private readonly api = inject(ApiService);
  private readonly currencyService = inject(CurrencyService);
 
  private activeFetchHistorySub?: Subscription;

  public readonly loading = signal<boolean>(false);
  public readonly rate = signal<number>(26500);
  public readonly currentInvoice = signal<DepositHistoryItem | null>(null);
  
  public readonly historyList = signal<DepositHistoryItem[]>([]);
  public readonly totalItems = signal<number>(0);
  public readonly lastPage = signal<number>(1);
  public readonly currentPage = signal<number>(1);

  public fetchRate(): Observable<{ rate: number }> {
    this.loading.set(true);
    return this.api.get<{ rate: number }>('v1/deposit/rate').pipe(
      tap(res => {
        if (res && res.rate) {
          this.rate.set(res.rate);
          this.currencyService.setUsdToVndRate(res.rate);
        }
      }),
      finalize(() => this.loading.set(false))
    );
  }

  public checkUnseenSuccessDeposit(): Observable<{ deposit: DepositHistoryItem | null }> {
    return this.api.get<{ deposit: DepositHistoryItem | null }>('v1/deposit/unseen-success');
  }

  public markDepositSeen(id: string): Observable<{ success: boolean }> {
    return this.api.post<{ success: boolean }>(`v1/deposit/mark-seen/${id}`, {});
  }

  public createDeposit(amountUsdt: number, paymentMethod: string = 'bep20', payerBinanceId?: string): Observable<{ message: string; invoice: DepositHistoryItem }> {
    const body: any = {
      amount_usdt: amountUsdt,
      payment_method: paymentMethod
    };
    if (payerBinanceId) {
      body.payer_binance_id = payerBinanceId;
    }
    return this.api.post<{ message: string; invoice: DepositHistoryItem }>('v1/deposit', body).pipe(
      tap(res => {
        if (res && res.invoice) {
          this.currentInvoice.set(res.invoice);
          if (typeof localStorage !== 'undefined') {
            localStorage.setItem('has_pending_deposit', 'true');
          }
        }
      })
    );
  }

  public fetchHistory(
    page: number,
    limit: number,
    search?: string,
    status?: string,
    startDate?: string,
    endDate?: string
  ): Observable<{ items: DepositHistoryItem[]; total: number; last_page: number; current_page: number }> {
    if (this.activeFetchHistorySub) {
      this.activeFetchHistorySub.unsubscribe();
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
    if (startDate) {
      params = params.set('start_date', startDate);
    }
    if (endDate) {
      params = params.set('end_date', endDate);
    }
 
    const subject = new Subject<any>();
 
    const req$ = this.api.get<{ items: DepositHistoryItem[]; total: number; last_page: number; current_page: number }>('v1/deposit/history', params).pipe(
      tap(res => {
        if (res) {
          this.historyList.set(res.items || []);
          this.totalItems.set(res.total || 0);
          this.lastPage.set(res.last_page || 1);
          this.currentPage.set(res.current_page || 1);
        }
      }),
      finalize(() => this.loading.set(false))
    );
 
    this.activeFetchHistorySub = req$.subscribe({
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

  public fetchInvoice(id: string, silent: boolean = false): Observable<{ invoice: DepositHistoryItem }> {
    if (!silent) {
      this.loading.set(true);
    }
    return this.api.get<{ invoice: DepositHistoryItem }>(`v1/deposit/invoice/${id}`).pipe(
      tap(res => {
        if (res && res.invoice) {
          this.currentInvoice.set(res.invoice);
        }
      }),
      finalize(() => {
        if (!silent) {
          this.loading.set(false);
        }
      })
    );
  }

  public cancelInvoice(id: string): Observable<{ message: string }> {
    return this.api.post<{ message: string }>(`v1/deposit/invoice/${id}/cancel`, {});
  }

  public checkTransaction(id: string): Observable<{ success: boolean; message: string }> {
    return this.api.post<{ success: boolean; message: string }>(`v1/deposit/invoice/${id}/check`, {});
  }
}
