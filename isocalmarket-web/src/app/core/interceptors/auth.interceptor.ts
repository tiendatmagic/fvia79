import { HttpInterceptorFn, HttpRequest, HttpHandlerFn, HttpErrorResponse } from '@angular/common/http';
import { inject } from '@angular/core';
import { Router } from '@angular/router';
import { throwError, BehaviorSubject, Observable, Subject } from 'rxjs';
import { catchError, filter, take, switchMap, takeUntil } from 'rxjs/operators';
import { HttpClient } from '@angular/common/http';
import { environment } from '@environments/environment';
import { ToastService } from '@core/services/toast.service';
import { UiStateService } from '@core/services/ui-state.service';

let isRefreshing = false;
const refreshDone$ = new BehaviorSubject<boolean>(false);
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

  const isBypassCancel = req.url.includes('/auth/refresh') || req.url.includes('/auth/logout');

  let requestStream$ = next(req);
  if (!isBypassCancel) {
    requestStream$ = requestStream$.pipe(takeUntil(cancelPending$));
  }

  return requestStream$.pipe(
    catchError((error: HttpErrorResponse) => {
      if (error.status === 503) {
        cancelPending$.next();
        uiStateService.isMaintenance.set(true);
      } else if (error.status === 429) {
        cancelPending$.next();

        const now = Date.now();
        if (now - lastOverloadToastTime > TOAST_DEBOUNCE_MS) {
          lastOverloadToastTime = now;
          toastService.showToast(
            'Hệ thống đang quá tải do nhận được quá nhiều yêu cầu. Vui lòng thử lại sau vài giây!',
            'error'
          );
        }
      }

      const isApiRequest = req.url.includes(environment.apiBaseUrl);
      const isRefreshRequest = req.url.includes('/auth/refresh');
      const isAuthEndpoint = req.url.includes('/auth/login') || req.url.includes('/auth/register')
        || req.url.includes('/v1/auth/login') || req.url.includes('/v1/auth/register');

      if (error.status === 401 && isApiRequest && !isRefreshRequest && !isAuthEndpoint) {
        return handle401Error(req, next, http, router);
      }

      return throwError(() => error);
    })
  );
};

function handle401Error(
  originalReq: HttpRequest<unknown>,
  next: HttpHandlerFn,
  http: HttpClient,
  router: Router
): Observable<any> {
  if (!isRefreshing) {
    isRefreshing = true;
    refreshDone$.next(false);

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

        if (typeof document !== 'undefined') {
          document.cookie = 'auth_logged_in=; path=/; expires=Thu, 01 Jan 1970 00:00:00 GMT';
        }

        router.navigate(['/login']);
        return throwError(() => refreshError);
      })
    );
  }

  return refreshDone$.pipe(
    filter(done => done === true),
    take(1),
    switchMap(() => next(originalReq))
  );
}
