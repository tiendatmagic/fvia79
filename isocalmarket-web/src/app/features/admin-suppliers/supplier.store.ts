import { Injectable, inject, signal } from '@angular/core';
import { ApiService } from '@core/services/api.service';
import { catchError, finalize, forkJoin, map, Observable, of, shareReplay, tap } from 'rxjs';

export type PriceAdjustmentType = 'fixed' | 'percent';

export interface SupplierSettings {
  code: string;
  name: string;
  base_url: string;
  catalog_driver: 'clone_store_v1' | 'clone_store_v2';
  has_api_key: boolean;
  enabled: boolean;
  products_enabled: boolean;
  import_out_of_stock: boolean;
  auto_sync: boolean;
  sync_interval_minutes: 10 | 30;
  price_adjustment_type: PriceAdjustmentType;
  price_adjustment_value: number;
  last_synced_at: string | null;
  last_sync_status: 'success' | 'failed' | null;
  last_sync_error: string | null;
  products_count: number;
  api_key?: string;
}

export interface SupplierBalance {
  balance: number;
  currency: string;
  account: {
    id: string | number | null;
    username: string | null;
    email: string | null;
    level: number | null;
    percent: number | null;
  };
}

@Injectable({ providedIn: 'root' })
export class SupplierStore {
  private readonly api = inject(ApiService);
  private readonly balanceCacheTtlMs = 5 * 60 * 1000;
  private readonly balanceCache = new Map<string, { data: SupplierBalance; fetchedAt: number }>();
  private readonly balanceRequests = new Map<string, Observable<SupplierBalance>>();
  private balanceRequestCode: string | null = null;

  public readonly suppliers = signal<SupplierSettings[]>([]);
  public readonly loading = signal(false);
  public readonly saving = signal(false);
  public readonly syncing = signal(false);
  public readonly balanceLoading = signal(false);
  public readonly balance = signal<SupplierBalance | null>(null);
  public readonly exchangeRate = signal(0);

  public fetchSuppliers(): Observable<SupplierSettings[]> {
    this.loading.set(true);
    return forkJoin({
      suppliers: this.api.get<SupplierSettings[]>('v1/admin/suppliers'),
      rate: this.api
        .get<{ rate: number }>('v1/deposit/rate')
        .pipe(catchError(() => of({ rate: 0 }))),
    }).pipe(
      map(({ suppliers, rate }) => {
        this.exchangeRate.set(Number(rate.rate));
        return suppliers;
      }),
      tap((suppliers) => this.suppliers.set(suppliers)),
      finalize(() => this.loading.set(false)),
    );
  }

  public create(payload: Partial<SupplierSettings>): Observable<SupplierSettings> {
    this.saving.set(true);
    return this.api.post<SupplierSettings>('v1/admin/suppliers', payload).pipe(
      tap((supplier) => this.suppliers.update((items) => [...items, supplier])),
      finalize(() => this.saving.set(false)),
    );
  }

  public update(code: string, payload: Partial<SupplierSettings>): Observable<SupplierSettings> {
    this.saving.set(true);
    return this.api.put<SupplierSettings>(`v1/admin/suppliers/${code}`, payload).pipe(
      tap((supplier) =>
        this.suppliers.update((items) =>
          items.map((item) => (item.code === code ? supplier : item)),
        ),
      ),
      finalize(() => this.saving.set(false)),
    );
  }

  public sync(code: string): Observable<any> {
    this.syncing.set(true);
    return this.api.post<any>(`v1/admin/suppliers/${code}/sync`, {}).pipe(
      tap((response) =>
        this.suppliers.update((items) =>
          items.map((item) => (item.code === code ? response.supplier : item)),
        ),
      ),
      finalize(() => this.syncing.set(false)),
    );
  }

  public fetchBalance(code: string, forceRefresh = false): Observable<SupplierBalance> {
    const cached = this.balanceCache.get(code);
    if (!forceRefresh && cached && Date.now() - cached.fetchedAt < this.balanceCacheTtlMs) {
      this.balanceRequestCode = code;
      this.balance.set(cached.data);
      this.balanceLoading.set(false);

      return of(cached.data);
    }

    this.balanceRequestCode = code;
    this.balanceLoading.set(true);
    this.balance.set(null);

    const pendingRequest = this.balanceRequests.get(code);
    if (pendingRequest) {
      return pendingRequest;
    }

    const request = this.api.post<SupplierBalance>(`v1/admin/suppliers/${code}/balance`, {}).pipe(
      tap((balance) => {
        const normalizedBalance = {
          balance: Number(balance.balance),
          currency: balance.currency,
          account: balance.account,
        };
        this.balanceCache.set(code, {
          data: normalizedBalance,
          fetchedAt: Date.now(),
        });

        if (this.balanceRequestCode === code) {
          this.balance.set(normalizedBalance);
        }
      }),
      finalize(() => {
        this.balanceRequests.delete(code);
        if (this.balanceRequestCode === code) {
          this.balanceLoading.set(false);
        }
      }),
      shareReplay({ bufferSize: 1, refCount: true }),
    );
    this.balanceRequests.set(code, request);

    return request;
  }

  public clearBalance(): void {
    this.balanceRequestCode = null;
    this.balance.set(null);
    this.balanceLoading.set(false);
  }
}
