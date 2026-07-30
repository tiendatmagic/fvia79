import { HttpInterceptorFn, HttpRequest, HttpHandlerFn, HttpErrorResponse } from '@angular/common/http';
import { inject } from '@angular/core';
import { Router } from '@angular/router';
import { throwError, BehaviorSubject, Observable, Subject } from 'rxjs';
import { catchError, filter, take, switchMap, takeUntil } from 'rxjs/operators';
import { HttpClient } from '@angular/common/http';
import { environment } from '@environments/environment';
import { ToastService } from '@core/services/toast.service';
import { UiStateService } from '@core/services/ui-state.service';
import { AuthStore } from '../../features/auth/auth.store';
import { isDefinitiveAuthFailure } from '@core/utils/auth-error.utils';

let isRefreshing = false;
const refreshDone$ = new BehaviorSubject<boolean | null>(null);
const cancelPending$ = new Subject<void>();
let lastOverloadToastTime = 0;
const TOAST_DEBOUNCE_MS = 3000;

export const authInterceptor: HttpInterceptorFn = (
  req: HttpRequest<unknown>,
  next: HttpHandlerFn
): Observable<any> => {
  const router = inject(Router);
  const http = inject(HttpClient);
  const toastService = inject(ToastService);
  const uiStateService = inject(UiStateService);
  const authStore = inject(AuthStore);
  const isApiRequest = req.url.startsWith(environment.apiBaseUrl);
  const protectedReq = isApiRequest
    ? req.clone({ setHeaders: { 'X-Requested-With': 'XMLHttpRequest' } })
    : req;

  const isBypassCancel = req.url.includes('/auth/refresh') || req.url.includes('/auth/logout');

  let requestStream$ = next(protectedReq);
  if (!isBypassCancel) {
    requestStream$ = requestStream$.pipe(takeUntil(cancelPending$));
  }

  return requestStream$.pipe(
    catchError((error: HttpErrorResponse) => {
      const isMaintenanceResponse = error.status === 503 && (
        error.error?.message === 'Service Unavailable'
        || (typeof error.error === 'string' && error.error.includes('Service Unavailable'))
      );

      if (isMaintenanceResponse) {
        cancelPending$.next();
        uiStateService.isMaintenance.set(true);
      } else if (error.status === 429) {
        cancelPending$.next();

        const now = Date.now();
        if (now - lastOverloadToastTime > TOAST_DEBOUNCE_MS) {
          lastOverloadToastTime = now;
          toastService.showToast('error.too_many_attempts', 'error');
        }
      }

      const isRefreshRequest = req.url.includes('/auth/refresh');
      const isSessionCheckRequest = req.url.includes('/auth/me');
      const isAuthEndpoint = req.url.includes('/auth/login') || req.url.includes('/auth/register')
        || req.url.includes('/v1/auth/login') || req.url.includes('/v1/auth/register');

      if (
        error.status === 401 &&
        isApiRequest &&
        !isRefreshRequest &&
        !isAuthEndpoint &&
        (authStore.hasLoggedInMarker() || isSessionCheckRequest)
      ) {
        return handle401Error(protectedReq, next, http, router, authStore);
      }

      return throwError(() => error);
    })
  );
};

function handle401Error(
  originalReq: HttpRequest<unknown>,
  next: HttpHandlerFn,
  http: HttpClient,
  router: Router,
  authStore: AuthStore
): Observable<any> {
  if (!isRefreshing) {
    isRefreshing = true;
    refreshDone$.next(null);

    const refreshUrl = `${environment.apiBaseUrl}/v1/auth/refresh`;

    return http.post<any>(refreshUrl, {}, { withCredentials: true }).pipe(
      switchMap(() => {
        isRefreshing = false;
        refreshDone$.next(true);

        return next(originalReq);
      }),
      catchError((refreshError) => {
        isRefreshing = false;
        refreshDone$.next(false);

        // An aborted refresh (status 0), timeout, rate limit, or server error
        // does not prove that the refresh token is invalid. Preserve the
        // session so a reload/retry can authenticate again.
        if (isDefinitiveAuthFailure(refreshError)) {
          authStore.currentUser.set(null);

          if (typeof document !== 'undefined') {
            document.cookie = 'auth_logged_in=; path=/; expires=Thu, 01 Jan 1970 00:00:00 GMT; SameSite=Lax';
          }

          const currentUrl = router.url || '';
          const isPublicRoute = currentUrl === '/' ||
                                currentUrl.startsWith('/storefront') ||
                                currentUrl.startsWith('/login') ||
                                currentUrl.startsWith('/register') ||
                                currentUrl.startsWith('/check-uid') ||
                                currentUrl.startsWith('/faq') ||
                                currentUrl.startsWith('/contact') ||
                                currentUrl.startsWith('/terms') ||
                                currentUrl.startsWith('/privacy');
          if (!isPublicRoute) {
            router.navigate(['/login']);
          }
        }
        return throwError(() => refreshError);
      })
    );
  }

  return refreshDone$.pipe(
    filter(done => done !== null),
    take(1),
    switchMap(success => {
      if (success) {
        return next(originalReq);
      } else {
        return throwError(() => new HttpErrorResponse({ status: 401, statusText: 'Unauthorized' }));
      }
    })
  );
}
