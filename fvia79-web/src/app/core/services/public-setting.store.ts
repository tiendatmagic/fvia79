import { Injectable, inject, signal } from '@angular/core';
import { ApiService } from '@core/services/api.service';
import { tap, finalize, shareReplay } from 'rxjs/operators';
import { Observable, of } from 'rxjs';

export interface PublicSettings {
  announcement_enabled: boolean;
  announcement_text_vi?: string;
  announcement_text_en?: string;
  announcement_text: string;
  announcement_effect: 'marquee' | 'fade' | 'slide' | 'bounce';
  usdt_exchange_rate: number;
  telegram_bot_username: string;
  membership_tiers_feature_enabled: boolean;
  membership_tiers_enabled: boolean;
  coupons_feature_enabled: boolean;
  coupons_enabled: boolean;
  payment_bep20_enabled: boolean;
  payment_binance_enabled: boolean;
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
    announcement_text_vi: '',
    announcement_text_en: '',
    announcement_text: '',
    announcement_effect: 'marquee',
    usdt_exchange_rate: 25400,
    telegram_bot_username: 'adsbm79noti_bot',
    membership_tiers_feature_enabled: true,
    membership_tiers_enabled: true,
    coupons_feature_enabled: true,
    coupons_enabled: true,
    payment_bep20_enabled: true,
    payment_binance_enabled: true
  });

  private cachedSettings: PublicSettings | null = null;
  private lastFetchTime = 0;
  private readonly CACHE_TTL_MS = 600000; // 10 minutes cache FE

  /** In-flight request: share Observable among concurrent callers while request is pending */
  private inFlight$: Observable<PublicSettings> | null = null;

  public clearCache() {
    this.cachedSettings = null;
    this.lastFetchTime = 0;
    this.inFlight$ = null;
    this.loaded.set(false);
  }

  public fetchPublicSettings(): Observable<PublicSettings> {
    // 1. Valid cache -> return cached result directly without HTTP call
    const now = Date.now();
    if (this.cachedSettings && (now - this.lastFetchTime < this.CACHE_TTL_MS)) {
      this.settings.set(this.cachedSettings);
      this.loaded.set(true);
      return of(this.cachedSettings);
    }

    // 2. In-flight request existing -> share Observable without creating new API request
    if (this.inFlight$) {
      return this.inFlight$;
    }

    // 3. No existing request -> initialize new in-flight Observable
    this.loading.set(true);
    this.inFlight$ = this.api.get<PublicSettings>('v1/settings/public').pipe(
      tap(res => {
        if (res) {
          const data: PublicSettings = {
            announcement_enabled: Boolean(res.announcement_enabled),
            announcement_text_vi: res.announcement_text_vi || '',
            announcement_text_en: res.announcement_text_en || '',
            announcement_text: res.announcement_text || '',
            announcement_effect: (res.announcement_effect as any) || 'marquee',
            usdt_exchange_rate: Number(res.usdt_exchange_rate) || 25400,
            telegram_bot_username: res.telegram_bot_username || 'adsbm79noti_bot',
            membership_tiers_feature_enabled: res.membership_tiers_feature_enabled !== undefined ? Boolean(res.membership_tiers_feature_enabled) : true,
            membership_tiers_enabled: res.membership_tiers_enabled !== undefined ? Boolean(res.membership_tiers_enabled) : true,
            coupons_feature_enabled: res.coupons_feature_enabled !== undefined ? Boolean(res.coupons_feature_enabled) : true,
            coupons_enabled: res.coupons_enabled !== undefined ? Boolean(res.coupons_enabled) : true,
            payment_bep20_enabled: res.payment_bep20_enabled !== undefined ? Boolean(res.payment_bep20_enabled) : true,
            payment_binance_enabled: res.payment_binance_enabled !== undefined ? Boolean(res.payment_binance_enabled) : true
          };
          this.cachedSettings = data;
          this.lastFetchTime = Date.now();
          this.settings.set(data);
          this.loaded.set(true);
        }
      }),
      finalize(() => {
        this.loading.set(false);
        this.inFlight$ = null; // Clear in-flight reference after request settles (success or error)
      }),
      shareReplay(1) // All subscribers receive same result from single HTTP call
    );

    return this.inFlight$;
  }
}
