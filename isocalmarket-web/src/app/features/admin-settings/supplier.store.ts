import { Injectable, inject, signal } from '@angular/core';
import { ApiService } from '@core/services/api.service';
import { finalize, forkJoin, map, Observable, tap } from 'rxjs';

export type PriceAdjustmentType = 'fixed' | 'percent';

export interface SupplierSettings {
  code: string;
  name: string;
  base_url: string;
  catalog_driver: 'clone_store_v1' | 'clone_store_v2';
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

  public readonly suppliers = signal<SupplierSettings[]>([]);
  public readonly loading = signal(false);
  public readonly saving = signal(false);
  public readonly syncing = signal(false);
  public readonly exchangeRate = signal(0);

  public fetchSuppliers(): Observable<SupplierSettings[]> {
    this.loading.set(true);
    return forkJoin({
      suppliers: this.api.get<SupplierSettings[]>('v1/admin/suppliers'),
      rate: this.api.get<{ rate: number }>('v1/deposit/rate')
    }).pipe(
      map(({ suppliers, rate }) => {
        this.exchangeRate.set(Number(rate.rate));
        return suppliers;
      }),
      tap(suppliers => this.suppliers.set(suppliers)),
      finalize(() => this.loading.set(false))
    );
  }

  public create(payload: Partial<SupplierSettings>): Observable<SupplierSettings> {
    this.saving.set(true);
    return this.api.post<SupplierSettings>('v1/admin/suppliers', payload).pipe(
      tap(supplier => this.suppliers.update(items => [...items, supplier])),
      finalize(() => this.saving.set(false))
    );
  }

  public update(code: string, payload: Partial<SupplierSettings>): Observable<SupplierSettings> {
    this.saving.set(true);
    return this.api.put<SupplierSettings>(`v1/admin/suppliers/${code}`, payload).pipe(
      tap(supplier => this.suppliers.update(items => items.map(item => item.code === code ? supplier : item))),
      finalize(() => this.saving.set(false))
    );
  }

  public sync(code: string): Observable<any> {
    this.syncing.set(true);
    return this.api.post<any>(`v1/admin/suppliers/${code}/sync`, {}).pipe(
      tap(response => this.suppliers.update(items => items.map(item => item.code === code ? response.supplier : item))),
      finalize(() => this.syncing.set(false))
    );
  }
}
