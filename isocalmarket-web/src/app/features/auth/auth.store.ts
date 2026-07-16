import { Injectable, inject, signal, computed } from '@angular/core';
import { ApiService } from '@core/services/api.service';
import { ToastService } from '@core/services/toast.service';
import { ModalService } from '@core/services/modal.service';
import { Router } from '@angular/router';
import { tap, catchError, finalize, map, shareReplay } from 'rxjs/operators';
import { of, Observable } from 'rxjs';
import { shouldShowWelcomeNotice, WelcomeNoticeModalComponent } from './welcome-notice-modal/welcome-notice-modal.component';

@Injectable({
  providedIn: 'root'
})
export class AuthStore {
  private readonly api = inject(ApiService);
  private readonly toast = inject(ToastService);
  private readonly router = inject(Router);
  private readonly modalService = inject(ModalService);

  // State Signals
  public readonly currentUser = signal<any | null>(null);
  public readonly authLoading = signal<boolean>(false);

  // Computed Signals
  public readonly isAuthenticated = computed(() => this.currentUser() !== null);
  public readonly isAdmin = computed(() => this.currentUser()?.role === 'admin');

  private checkMeObservable$: Observable<any> | null = null;

  // Signal lưu lỗi inline để hiển thị ngay trong form (không chỉ toast)
  public readonly loginError = signal<string>('');
  public readonly registerError = signal<string>('');

  /** Dịch thông báo lỗi từ API (tiếng Anh) sang tiếng Việt thân thiện */
  private translateAuthError(err: any, fallback: string): string {
    const raw: string = err?.error?.message || err?.message || '';
    const status: number = err?.status || 0;

    // Map message cụ thể từ backend
    const messageMap: Record<string, string> = {
      'Invalid email/username or password.': 'Tài khoản hoặc mật khẩu không chính xác!',
      'Unauthorized.': 'Phiên đăng nhập không hợp lệ. Vui lòng đăng nhập lại!',
      'Too Many Attempts.': 'Bạn đã thử quá nhiều lần. Vui lòng chờ vài phút rồi thử lại!',
      'The email has already been taken.': 'Email này đã được sử dụng. Vui lòng chọn email khác!',
      'The name has already been taken.': 'Tên tài khoản này đã tồn tại. Vui lòng chọn tên khác!',
      'The email field must be a valid email address.': 'Địa chỉ email không đúng định dạng!',
      'The password field must be at least 6 characters.': 'Mật khẩu phải có ít nhất 6 ký tự!',
      'Invalid or expired verification token.': 'Mã xác thực đã hết hạn. Vui lòng đăng nhập lại từ đầu!',
      'Invalid OTP code. Please try again.': 'Mã OTP không chính xác. Vui lòng thử lại!',
      'Invalid 2FA verification code.': 'Mã xác thực 2FA không chính xác!',
      'User not found.': 'Tài khoản không tồn tại trong hệ thống!',
    };

    if (messageMap[raw]) return messageMap[raw];

    // Map theo status code nếu không có message cụ thể
    if (status === 401) return 'Tài khoản hoặc mật khẩu không chính xác!';
    if (status === 422) {
      // Laravel validation errors — thường có `errors` object
      const firstField = err?.error?.errors;
      if (firstField) {
        const firstKey = Object.keys(firstField)[0];
        if (firstKey && firstField[firstKey]?.[0]) {
          return this.translateAuthError({ message: firstField[firstKey][0] }, fallback);
        }
      }
      return 'Thông tin nhập vào không hợp lệ. Vui lòng kiểm tra lại!';
    }
    if (status === 429) return 'Bạn đã thử quá nhiều lần. Vui lòng chờ vài phút rồi thử lại!';
    if (status === 500) return 'Lỗi máy chủ. Vui lòng thử lại sau!';
    if (status === 0) return 'Không thể kết nối máy chủ. Vui lòng kiểm tra mạng!';

    return raw || fallback;
  }

  public login(credentials: { email: string; password: string }, returnUrl: string = '/') {
    this.authLoading.set(true);
    this.loginError.set('');
    return this.api.post<any>('v1/auth/login', credentials).pipe(
      tap((res) => {
        if (res && res.requires_2fa) {
          return;
        }
        this.currentUser.set(res.user);
        this.toast.showToast('Đăng nhập thành công!', 'success');
        this.router.navigateByUrl(returnUrl);
        this.openWelcomeNoticeIfNeeded();
      }),
      catchError((err) => {
        const msg = this.translateAuthError(err, 'Đăng nhập thất bại. Vui lòng thử lại!');
        this.loginError.set(msg);
        this.toast.showToast(msg, 'error');
        return of(null);
      }),
      finalize(() => this.authLoading.set(false))
    );
  }

  public register(data: any) {
    this.authLoading.set(true);
    this.registerError.set('');
    return this.api.post<any>('v1/auth/register', data).pipe(
      tap(() => {
        this.toast.showToast('Đăng ký tài khoản thành công! Vui lòng đăng nhập.', 'success');
        this.router.navigate(['/login']);
      }),
      catchError((err) => {
        const msg = this.translateAuthError(err, 'Đăng ký thất bại. Vui lòng thử lại!');
        this.registerError.set(msg);
        this.toast.showToast(msg, 'error');
        return of(null);
      }),
      finalize(() => this.authLoading.set(false))
    );
  }

  public checkMe() {
    // Check trạng thái login indicator trước để tránh gọi API thừa khi chưa đăng nhập
    const isLocalLoggedIn = typeof document !== 'undefined' && document.cookie.includes('auth_logged_in=true');
    if (!isLocalLoggedIn) {
      this.currentUser.set(null);
      return of(null);
    }

    if (this.checkMeObservable$) {
      return this.checkMeObservable$;
    }

    this.checkMeObservable$ = this.api.get<any>('v1/auth/me').pipe(
      map((res) => res?.user || null),
      tap((user) => {
        this.currentUser.set(user);
      }),
      catchError(() => {
        // authInterceptor sẽ tự xử lý: refresh token → retry → hoặc redirect về /login
        // Ở đây chỉ cần reset signal về null
        this.currentUser.set(null);
        return of(null);
      }),
      shareReplay(1),
      finalize(() => {
        this.checkMeObservable$ = null;
      })
    );

    return this.checkMeObservable$;
  }

  public logout() {
    this.authLoading.set(true);
    return this.api.post<any>('v1/auth/logout', {}).pipe(
      tap(() => {
        this.currentUser.set(null);
        this.toast.showToast('Đã đăng xuất!', 'success');
        this.router.navigate(['/storefront']);
      }),
      catchError(() => {
        this.currentUser.set(null);
        this.router.navigate(['/storefront']);
        return of(null);
      }),
      finalize(() => this.authLoading.set(false))
    );
  }

  public login2Fa(tempToken: string, code: string, returnUrl: string = '/') {
    this.authLoading.set(true);
    return this.api.post<any>('v1/auth/login/2fa', { temp_token: tempToken, code }).pipe(
      tap((res) => {
        this.currentUser.set(res.user);
        this.toast.showToast('Đăng nhập thành công!', 'success');
        this.router.navigateByUrl(returnUrl);
        this.openWelcomeNoticeIfNeeded();
      }),
      catchError((err) => {
        const msg = err.error?.message || 'Mã xác thực không đúng hoặc đã hết hạn!';
        this.toast.showToast(msg, 'error');
        return of(null);
      }),
      finalize(() => this.authLoading.set(false))
    );
  }

  public updateProfile(phone: string) {
    this.authLoading.set(true);
    return this.api.put<any>('v1/profile', { phone }).pipe(
      tap((res) => {
        this.currentUser.set(res.user);
        this.toast.showToast('Cập nhật thông tin thành công!', 'success');
      }),
      catchError((err) => {
        const msg = err.error?.message || 'Cập nhật thông tin thất bại!';
        this.toast.showToast(msg, 'error');
        return of(null);
      }),
      finalize(() => this.authLoading.set(false))
    );
  }

  public changePassword(data: any) {
    this.authLoading.set(true);
    return this.api.put<any>('v1/profile/password', data).pipe(
      tap(() => {
        this.toast.showToast('Đổi mật khẩu thành công!', 'success');
      }),
      catchError((err) => {
        const msg = err.error?.message || 'Đổi mật khẩu thất bại!';
        this.toast.showToast(msg, 'error');
        return of(null);
      }),
      finalize(() => this.authLoading.set(false))
    );
  }

  public enable2Fa() {
    this.authLoading.set(true);
    return this.api.post<any>('v1/profile/2fa/enable', {}).pipe(
      catchError((err) => {
        const msg = err.error?.message || 'Không thể khởi tạo 2FA!';
        this.toast.showToast(msg, 'error');
        return of(null);
      }),
      finalize(() => this.authLoading.set(false))
    );
  }

  public confirm2Fa(code: string) {
    this.authLoading.set(true);
    return this.api.post<any>('v1/profile/2fa/confirm', { code }).pipe(
      tap((res) => {
        this.currentUser.set(res.user);
        this.toast.showToast('Đã kích hoạt bảo mật 2FA thành công!', 'success');
      }),
      catchError((err) => {
        const msg = err.error?.message || 'Mã xác thực OTP không chính xác!';
        this.toast.showToast(msg, 'error');
        return of(null);
      }),
      finalize(() => this.authLoading.set(false))
    );
  }

  public disable2Fa(code: string) {
    this.authLoading.set(true);
    return this.api.post<any>('v1/profile/2fa/disable', { code }).pipe(
      tap((res) => {
        this.currentUser.set(res.user);
        this.toast.showToast('Đã hủy kích hoạt bảo mật 2FA!', 'success');
      }),
      catchError((err) => {
        const rawMsg = err.error?.message;
        const msg = rawMsg === 'Invalid 2FA verification code.'
          ? 'Mã xác thực 2FA không chính xác!'
          : (rawMsg || 'Hủy kích hoạt 2FA thất bại!');
        this.toast.showToast(msg, 'error');
        return of(null);
      }),
      finalize(() => this.authLoading.set(false))
    );
  }

  /** Hiển thị modal thông báo chào mừng nếu người dùng chưa chọn "Không hỏi lại trong 7 ngày" */
  private openWelcomeNoticeIfNeeded(): void {
    if (!shouldShowWelcomeNotice()) return;
    // Trì hoãn nhẹ để router navigation hoàn tất trước khi mở modal
    setTimeout(() => {
      this.modalService.open(WelcomeNoticeModalComponent, {
        title: '🔔 Thông Báo Hệ Thống',
        size: 'sm',
        closeOnBackdropClick: true,
      });
    }, 400);
  }
}
