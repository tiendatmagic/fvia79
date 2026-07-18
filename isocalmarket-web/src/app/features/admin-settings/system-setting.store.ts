import { Injectable, inject, signal } from '@angular/core';
import { ApiService } from '@core/services/api.service';
import { tap, finalize } from 'rxjs/operators';
import { Observable, of } from 'rxjs';

export interface SystemSettingsPayload {
  admin_wallet_address: string;
  scanner_api_key: string;
}

@Injectable({
  providedIn: 'root'
})
export class SystemSettingStore {
  private readonly api = inject(ApiService);

  public readonly loading = signal<boolean>(false);
  public readonly submitting = signal<boolean>(false);
  public readonly settings = signal<SystemSettingsPayload>({
    admin_wallet_address: '',
    scanner_api_key: ''
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
}
