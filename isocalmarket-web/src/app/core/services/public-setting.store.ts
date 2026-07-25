import { Injectable, inject, signal } from '@angular/core';
import { ApiService } from '@core/services/api.service';
import { tap, finalize } from 'rxjs/operators';
import { Observable, of } from 'rxjs';

export interface PublicSettings {
  announcement_enabled: boolean;
  announcement_text: string;
  announcement_effect: 'marquee' | 'fade' | 'slide' | 'bounce';
  usdt_exchange_rate: number;
  telegram_bot_username: string;
}

@Injectable({
  providedIn: 'root'
})
export class PublicSettingStore {
  private readonly api = inject(ApiService);

  public readonly loading = signal<boolean>(false);
  public readonly loaded = signal<boolean>(false);
  public readonly settings = signal<PublicSettings>({
    announcement_enabled: false,
    announcement_text: '',
    announcement_effect: 'marquee',
    usdt_exchange_rate: 25400,
    telegram_bot_username: 'isocalmarketnoti_bot'
  });

  private cachedSettings: PublicSettings | null = null;
  private lastFetchTime = 0;
  private readonly CACHE_TTL_MS = 600000; // 10 minutes cache FE

  public clearCache() {
    this.cachedSettings = null;
    this.lastFetchTime = 0;
    this.loaded.set(false);
  }

  public fetchPublicSettings(): Observable<PublicSettings> {
    const now = Date.now();
    if (this.cachedSettings && (now - this.lastFetchTime < this.CACHE_TTL_MS)) {
      this.settings.set(this.cachedSettings);
      this.loaded.set(true);
      return of(this.cachedSettings);
    }

    this.loading.set(true);
    return this.api.get<PublicSettings>('v1/settings/public').pipe(
      tap(res => {
        if (res) {
          const data: PublicSettings = {
            announcement_enabled: Boolean(res.announcement_enabled),
            announcement_text: res.announcement_text || '',
            announcement_effect: (res.announcement_effect as any) || 'marquee',
            usdt_exchange_rate: Number(res.usdt_exchange_rate) || 25400,
            telegram_bot_username: res.telegram_bot_username || 'isocalmarketnoti_bot'
          };
          this.cachedSettings = data;
          this.lastFetchTime = Date.now();
          this.settings.set(data);
          this.loaded.set(true);
        }
      }),
      finalize(() => this.loading.set(false))
    );
  }
}
