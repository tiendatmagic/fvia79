import { Injectable, inject, signal } from '@angular/core';
import { ApiService } from '@core/services/api.service';
import { finalize, forkJoin, map, Observable, tap } from 'rxjs';

export type PriceAdjustmentType = 'fixed' | 'percent';

export interface SupplierSettings {
  code: string;
  name: string;
  base_url: string;
  has_api_key: boolean;
  enabled: boolean;
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

@Injectable({ providedIn: 'root' })
export class SupplierStore {
  private readonly api = inject(ApiService);

  public readonly supplier = signal<SupplierSettings | null>(null);
  public readonly loading = signal(false);
  public readonly saving = signal(false);
  public readonly syncing = signal(false);
  public readonly exchangeRate = signal(0);

  public fetchAds7979(): Observable<SupplierSettings> {
    this.loading.set(true);
    return forkJoin({
      suppliers: this.api.get<SupplierSettings[]>('v1/admin/suppliers'),
      rate: this.api.get<{ rate: number }>('v1/deposit/rate')
    }).pipe(
      map(({ suppliers, rate }) => {
        this.exchangeRate.set(Number(rate.rate));
        return suppliers.find(item => item.code === 'ads7979')!;
      }),
      tap(supplier => this.supplier.set(supplier)),
      finalize(() => this.loading.set(false))
    );
  }

  public updateAds7979(payload: Partial<SupplierSettings>): Observable<SupplierSettings> {
    this.saving.set(true);
    return this.api.put<SupplierSettings>('v1/admin/suppliers/ads7979', payload).pipe(
      tap(supplier => this.supplier.set(supplier)),
      finalize(() => this.saving.set(false))
    );
  }

  public syncAds7979(): Observable<any> {
    this.syncing.set(true);
    return this.api.post<any>('v1/admin/suppliers/ads7979/sync', {}).pipe(
      tap(response => this.supplier.set(response.supplier)),
      finalize(() => this.syncing.set(false))
    );
  }
}
