import { TestBed } from '@angular/core/testing';
import { Observable, Subject, of, throwError } from 'rxjs';
import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';

import { ApiService } from './api.service';
import {
  MiniPurchaseResponse,
  StorefrontActivityStore,
  StorefrontRecentActivityResponse
} from './storefront-activity.store';

class ApiServiceStub {
  public readonly calls: string[] = [];
  public response$: Observable<unknown> = of(null);

  public get<T>(path: string): Observable<T> {
    this.calls.push(path);
    return this.response$ as Observable<T>;
  }
}

describe('StorefrontActivityStore', () => {
  let api: ApiServiceStub;
  let store: StorefrontActivityStore;

  const purchases: StorefrontRecentActivityResponse = {
    enabled: true,
    mode: 'real',
    data: [{ id: 1 }]
  };

  beforeEach(() => {
    api = new ApiServiceStub();
    TestBed.configureTestingModule({
      providers: [
        StorefrontActivityStore,
        { provide: ApiService, useValue: api }
      ]
    });
    store = TestBed.inject(StorefrontActivityStore);
  });

  afterEach(() => {
    vi.restoreAllMocks();
    TestBed.resetTestingModule();
  });

  it('deduplicates an in-flight request even if a subscriber unsubscribes early', () => {
    const response$ = new Subject<StorefrontRecentActivityResponse>();
    api.response$ = response$;

    const first$ = store.fetchRecentPurchases();
    const subscription = first$.subscribe();
    subscription.unsubscribe();

    const second$ = store.fetchRecentPurchases();

    expect(second$).toBe(first$);
    expect(api.calls).toEqual(['v1/storefront/recent-purchases']);

    response$.next(purchases);
    response$.complete();
  });

  it('uses the cache within the TTL and lets force bypass a fresh cache entry', () => {
    let now = 1_000;
    vi.spyOn(Date, 'now').mockImplementation(() => now);
    api.response$ = of(purchases);

    store.fetchRecentPurchases().subscribe();
    now += 44_999;
    store.fetchRecentPurchases().subscribe();
    store.fetchRecentPurchases(true).subscribe();

    expect(api.calls).toHaveLength(2);
  });

  it('fetches again when the TTL expires', () => {
    let now = 1_000;
    vi.spyOn(Date, 'now').mockImplementation(() => now);
    api.response$ = of(purchases);

    store.fetchRecentPurchases().subscribe();
    now += 45_000;
    store.fetchRecentPurchases().subscribe();

    expect(api.calls).toHaveLength(2);
  });

  it('resets the in-flight state after an error', () => {
    api.response$ = throwError(() => new Error('network error'));
    store.fetchRecentPurchases().subscribe({ error: () => undefined });

    api.response$ = of(purchases);
    store.fetchRecentPurchases().subscribe();

    expect(api.calls).toHaveLength(2);
  });

  it('does not let a stale response repopulate an invalidated cache', () => {
    const staleRequest$ = new Subject<StorefrontRecentActivityResponse>();
    const freshResponse: StorefrontRecentActivityResponse = {
      enabled: true,
      mode: 'real',
      data: [{ id: 2 }]
    };
    api.response$ = staleRequest$;
    store.fetchRecentPurchases().subscribe();

    store.invalidatePurchases();
    api.response$ = of(freshResponse);
    store.fetchRecentPurchases().subscribe();

    staleRequest$.next(purchases);
    staleRequest$.complete();

    let cached: StorefrontRecentActivityResponse | undefined;
    store.fetchRecentPurchases().subscribe((response) => {
      cached = response;
    });

    expect(cached).toBe(freshResponse);
    expect(api.calls).toHaveLength(2);
  });

  it('invalidates both purchase feeds together', () => {
    const mini: MiniPurchaseResponse = {
      enabled: true,
      mode: 'real',
      data: []
    };

    api.response$ = of(purchases);
    store.fetchRecentPurchases().subscribe();
    api.response$ = of(mini);
    store.fetchMiniRecentPurchases().subscribe();

    store.invalidatePurchases();

    api.response$ = of(purchases);
    store.fetchRecentPurchases().subscribe();
    api.response$ = of(mini);
    store.fetchMiniRecentPurchases().subscribe();

    expect(api.calls).toHaveLength(4);
  });
});
