import { Injectable, inject, signal } from '@angular/core';
import { ApiService } from '@core/services/api.service';
import { tap, finalize } from 'rxjs/operators';
import { Observable, of } from 'rxjs';

export interface SystemSettingsPayload {
  admin_wallet_address: string;
  scanner_api_key: string;
  token_cleanup_days?: number;
  usdt_exchange_rate?: number;
  admin_binance_id?: string;
  binance_api_key?: string;
  binance_api_secret?: string;
}

@Injectable({
  providedIn: 'root'
})
export class SystemSettingStore {
  private readonly api = inject(ApiService);

  public readonly loading = signal<boolean>(false);
  public readonly submitting = signal<boolean>(false);
  public readonly isSavingSettings = signal<boolean>(false);
  public readonly isCleaningTokens = signal<boolean>(false);
  public readonly runningMaintenance = signal<string>('');
  
  public readonly settings = signal<SystemSettingsPayload>({
    admin_wallet_address: '',
    scanner_api_key: '',
    token_cleanup_days: 7,
    usdt_exchange_rate: 0,
    admin_binance_id: '',
    binance_api_key: '',
    binance_api_secret: ''
  });

  private cachedSettings: SystemSettingsPayload | null = null;
  private lastFetchTime = 0;

  public clearCache() {
    this.cachedSettings = null;
    this.lastFetchTime = 0;
  }

  public fetchSettings(): Observable<SystemSettingsPayload> {
    if (this.cachedSettings && (Date.now() - this.lastFetchTime < 60000)) {
      this.settings.set(this.cachedSettings);
      return of(this.cachedSettings);
    }

    this.loading.set(true);
    return this.api.get<SystemSettingsPayload>('v1/admin/settings').pipe(
      tap(res => {
        if (res) {
          // Parse token_cleanup_days sang number nếu backend trả về string
          if (res.token_cleanup_days) {
            res.token_cleanup_days = Number(res.token_cleanup_days);
          } else {
            res.token_cleanup_days = 7;
          }
          if (res.usdt_exchange_rate !== undefined) {
            res.usdt_exchange_rate = Number(res.usdt_exchange_rate);
          } else {
            res.usdt_exchange_rate = 0;
          }
          this.cachedSettings = res;
          this.lastFetchTime = Date.now();
          this.settings.set(res);
        }
      }),
      finalize(() => this.loading.set(false))
    );
  }

  public updateSettings(payload: SystemSettingsPayload): Observable<any> {
    this.submitting.set(true);
    return this.api.put<any>('v1/admin/settings', payload).pipe(
      tap(res => {
        this.clearCache();
        this.settings.set(payload);
      }),
      finalize(() => this.submitting.set(false))
    );
  }

  public updateTokenSettings(days: number): Observable<any> {
    this.isSavingSettings.set(true);
    return this.api.put<any>('v1/admin/settings', { token_cleanup_days: days }).pipe(
      tap(() => {
        this.clearCache();
        const current = this.settings();
        this.settings.set({
          ...current,
          token_cleanup_days: days
        });
      }),
      finalize(() => this.isSavingSettings.set(false))
    );
  }

  public runMaintenance(action: string): Observable<any> {
    this.runningMaintenance.set(action);
    return this.api.post<any>(`v1/admin/maintenance/${action}`, {}).pipe(
      finalize(() => this.runningMaintenance.set(''))
    );
  }

  public cleanTokens(): Observable<any> {
    this.isCleaningTokens.set(true);
    return this.api.post<any>('v1/admin/maintenance/clean-tokens', {}).pipe(
      finalize(() => this.isCleaningTokens.set(false))
    );
  }

  public cleanAllTokens(): Observable<any> {
    this.isCleaningTokens.set(true);
    return this.api.post<any>('v1/admin/maintenance/clean-all-tokens', {}).pipe(
      finalize(() => this.isCleaningTokens.set(false))
    );
  }
}
