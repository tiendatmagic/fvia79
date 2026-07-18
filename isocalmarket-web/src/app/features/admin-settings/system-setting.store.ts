import { Injectable, inject, signal } from '@angular/core';
import { ApiService } from '@core/services/api.service';
import { tap, finalize } from 'rxjs/operators';
import { Observable } from 'rxjs';

export interface SystemSettingsPayload {
  admin_wallet_address: string;
  scanner_api_key: string;
}

@Injectable({
  providedIn: 'root'
})
export class SystemSettingStore {
  private readonly api = inject(ApiService);

  // States
  public readonly loading = signal<boolean>(false);
  public readonly submitting = signal<boolean>(false);
  public readonly settings = signal<SystemSettingsPayload>({
    admin_wallet_address: '',
    scanner_api_key: ''
  });

  /**
   * Lấy cấu hình hệ thống từ backend
   */
  public fetchSettings(): Observable<SystemSettingsPayload> {
    this.loading.set(true);
    return this.api.get<SystemSettingsPayload>('v1/admin/settings').pipe(
      tap(res => {
        if (res) {
          this.settings.set(res);
        }
      }),
      finalize(() => this.loading.set(false))
    );
  }

  /**
   * Cập nhật cấu hình hệ thống
   */
  public updateSettings(payload: SystemSettingsPayload): Observable<any> {
    this.submitting.set(true);
    return this.api.put<any>('v1/admin/settings', payload).pipe(
      tap(res => {
        // Cập nhật lại local state
        this.settings.set(payload);
      }),
      finalize(() => this.submitting.set(false))
    );
  }
}
