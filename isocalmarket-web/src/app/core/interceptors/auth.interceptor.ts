import { HttpInterceptorFn, HttpRequest, HttpHandlerFn, HttpErrorResponse } from '@angular/common/http';
import { inject } from '@angular/core';
import { Router } from '@angular/router';
import { throwError, BehaviorSubject, Observable } from 'rxjs';
import { catchError, filter, take, switchMap } from 'rxjs/operators';
import { HttpClient } from '@angular/common/http';
import { environment } from '@environments/environment';

// Shared state để xử lý nhiều request 401 xảy ra đồng thời (chỉ gọi refresh 1 lần)
let isRefreshing = false;
const refreshDone$ = new BehaviorSubject<boolean>(false);

/**
 * HTTP Interceptor tự động làm mới JWT Access Token khi nhận lỗi 401.
 *
 * Luồng xử lý:
 * 1. Request bất kỳ → Nhận về lỗi 401 → Bắt đầu refresh
 * 2. Trong khi đang refresh: các request 401 khác sẽ được "xếp hàng" chờ
 * 3. Sau khi refresh thành công: toàn bộ request xếp hàng được gửi lại tự động
 * 4. Nếu refresh thất bại (refresh_token hết hạn): xóa cookie, chuyển về /auth/login
 */
export const authInterceptor: HttpInterceptorFn = (
  req: HttpRequest<unknown>,
  next: HttpHandlerFn
): Observable<any> => {
  const router = inject(Router);
  const http = inject(HttpClient);

  return next(req).pipe(
    catchError((error: HttpErrorResponse) => {
      // Chỉ xử lý lỗi 401 từ API của dự án (bỏ qua các request bên ngoài)
      // Bỏ qua auth endpoints: login/register/2fa trả về 401 khi sai thông tin → không phải token hết hạn
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
    // Bắt đầu quá trình refresh
    isRefreshing = true;
    refreshDone$.next(false);

    const refreshUrl = `${environment.apiBaseUrl}/v1/auth/refresh`;

    return http.post<any>(refreshUrl, {}, { withCredentials: true }).pipe(
      switchMap(() => {
        // Refresh thành công — phát tín hiệu cho các request đang chờ
        isRefreshing = false;
        refreshDone$.next(true);

        // Gửi lại request gốc đã bị lỗi 401
        return next(originalReq);
      }),
      catchError((refreshError) => {
        // Refresh Token cũng đã hết hạn → buộc người dùng đăng nhập lại
        isRefreshing = false;
        refreshDone$.next(false);

        // Xóa cookie chỉ báo đăng nhập để Guards hoạt động chính xác
        if (typeof document !== 'undefined') {
          document.cookie = 'auth_logged_in=; path=/; expires=Thu, 01 Jan 1970 00:00:00 GMT';
        }

        router.navigate(['/login']);
        return throwError(() => refreshError);
      })
    );
  }

  // Đang có một quá trình refresh đang chạy → xếp hàng chờ kết quả
  return refreshDone$.pipe(
    filter(done => done === true),
    take(1),
    switchMap(() => next(originalReq))
  );
}
