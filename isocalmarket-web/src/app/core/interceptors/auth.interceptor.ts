import { HttpInterceptorFn, HttpRequest, HttpHandlerFn, HttpErrorResponse } from '@angular/common/http';
import { inject } from '@angular/core';
import { Router } from '@angular/router';
import { throwError, BehaviorSubject, Observable } from 'rxjs';
import { catchError, filter, take, switchMap } from 'rxjs/operators';
import { HttpClient } from '@angular/common/http';
import { environment } from '@environments/environment';

let isRefreshing = false;
const refreshDone$ = new BehaviorSubject<boolean>(false);

export const authInterceptor: HttpInterceptorFn = (
  req: HttpRequest<unknown>,
  next: HttpHandlerFn
): Observable<any> => {
  const router = inject(Router);
  const http = inject(HttpClient);

  return next(req).pipe(
    catchError((error: HttpErrorResponse) => {
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
