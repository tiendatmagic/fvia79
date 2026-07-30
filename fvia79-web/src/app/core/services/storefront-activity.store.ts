import { Injectable, inject } from '@angular/core';
import { ApiService } from '@core/services/api.service';
import { Observable, of } from 'rxjs';
import { finalize, shareReplay, tap } from 'rxjs/operators';

export interface MiniPurchase {
  id: number | string;
  username: string;
  product_name?: string;
  product_name_vi?: string;
  product_name_en?: string;
  quantity: number;
  time_ago?: string;
}

export interface MiniPurchaseResponse {
  enabled: boolean;
  mode: 'real' | 'demo';
  data: MiniPurchase[];
}

export interface StorefrontRecentActivityResponse {
  enabled: boolean;
  mode: 'demo' | 'real';
  data: any[];
}

interface ActivityCacheState<T> {
  entry: { response: T; fetchedAt: number } | null;
  inFlight$: Observable<T> | null;
  generation: number;
}

@Injectable({
  providedIn: 'root'
})
export class StorefrontActivityStore {
  private readonly api = inject(ApiService);
  private readonly cacheTtlMs = 45_000;

  private readonly recentPurchasesState = this.createCacheState<StorefrontRecentActivityResponse>();
  private readonly recentDepositsState = this.createCacheState<StorefrontRecentActivityResponse>();
  private readonly miniPurchasesState = this.createCacheState<MiniPurchaseResponse>();

  public fetchRecentPurchases(force = false): Observable<StorefrontRecentActivityResponse> {
    return this.fetchCached(
      this.recentPurchasesState,
      'v1/storefront/recent-purchases',
      force
    );
  }

  public fetchRecentDeposits(force = false): Observable<StorefrontRecentActivityResponse> {
    return this.fetchCached(
      this.recentDepositsState,
      'v1/storefront/recent-deposits',
      force
    );
  }

  public fetchMiniRecentPurchases(force = false): Observable<MiniPurchaseResponse> {
    return this.fetchCached(
      this.miniPurchasesState,
      'v1/storefront/mini-recent-purchases',
      force
    );
  }

  public invalidatePurchases(): void {
    this.invalidate(this.recentPurchasesState);
    this.invalidate(this.miniPurchasesState);
  }

  public invalidateDeposits(): void {
    this.invalidate(this.recentDepositsState);
  }

  public clearCache(): void {
    this.invalidatePurchases();
    this.invalidateDeposits();
  }

  private createCacheState<T>(): ActivityCacheState<T> {
    return {
      entry: null,
      inFlight$: null,
      generation: 0
    };
  }

  private fetchCached<T>(
    state: ActivityCacheState<T>,
    path: string,
    force: boolean
  ): Observable<T> {
    const now = Date.now();
    if (!force && state.entry && now - state.entry.fetchedAt < this.cacheTtlMs) {
      return of(state.entry.response);
    }
    if (state.inFlight$) {
      return state.inFlight$;
    }

    const requestGeneration = state.generation;
    const request$ = this.api.get<T>(path).pipe(
      tap((response) => {
        if (response && requestGeneration === state.generation) {
          state.entry = { response, fetchedAt: Date.now() };
        }
      }),
      finalize(() => {
        if (state.inFlight$ === request$) {
          state.inFlight$ = null;
        }
      }),
      shareReplay({ bufferSize: 1, refCount: false })
    );

    state.inFlight$ = request$;
    return request$;
  }

  private invalidate<T>(state: ActivityCacheState<T>): void {
    state.generation++;
    state.entry = null;
    state.inFlight$ = null;
  }
}
