import { Injectable, inject, signal, computed } from '@angular/core';
import { ApiService } from '@core/services/api.service';
import { ToastService } from '@core/services/toast.service';
import { Router } from '@angular/router';
import { tap, catchError, finalize, map, shareReplay } from 'rxjs/operators';
import { of, Observable } from 'rxjs';

@Injectable({
  providedIn: 'root'
})
export class AuthStore {
  private readonly api = inject(ApiService);
  private readonly toast = inject(ToastService);
  private readonly router = inject(Router);

  // State Signals
  public readonly currentUser = signal<any | null>(null);
  public readonly authLoading = signal<boolean>(false);

  // Computed Signals
  public readonly isAuthenticated = computed(() => this.currentUser() !== null);
  public readonly isAdmin = computed(() => this.currentUser()?.role === 'admin');

  private checkMeObservable$: Observable<any> | null = null;

  public login(credentials: { email: string; password: string }, returnUrl: string = '/') {
    this.authLoading.set(true);
    return this.api.post<any>('v1/auth/login', credentials).pipe(
      tap((res) => {
        this.currentUser.set(res.user);
        this.toast.showToast('Đăng nhập thành công!', 'success');
        this.router.navigateByUrl(returnUrl);
      }),
      catchError((err) => {
        const msg = err.error?.message || 'Đăng nhập thất bại. Vui lòng kiểm tra lại!';
        this.toast.showToast(msg, 'error');
        return of(null);
      }),
      finalize(() => this.authLoading.set(false))
    );
  }

  public register(data: any) {
    this.authLoading.set(true);
    return this.api.post<any>('v1/auth/register', data).pipe(
      tap(() => {
        this.toast.showToast('Đăng ký tài khoản thành công! Vui lòng đăng nhập.', 'success');
        this.router.navigate(['/auth/login']);
      }),
      catchError((err) => {
        const msg = err.error?.message || 'Đăng ký thất bại. Vui lòng thử lại!';
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
}
