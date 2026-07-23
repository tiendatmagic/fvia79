import { Injectable, inject, signal, computed } from '@angular/core';
import { ApiService } from '@core/services/api.service';
import { ToastService } from '@core/services/toast.service';
import { ModalService } from '@core/services/modal.service';
import { LanguageService } from '@core/services/language.service';
import { Router } from '@angular/router';
import { tap, catchError, finalize, map, shareReplay } from 'rxjs/operators';
import { of, Observable } from 'rxjs';
import { shouldShowWelcomeNotice, WelcomeNoticeModalComponent } from './welcome-notice-modal/welcome-notice-modal.component';
import { ProductStore } from '../products/product.store';
import { InventoryStore } from '../inventory/inventory.store';
import { OrderStore } from '../orders/order.store';
import { UserStore } from '../admin-users/user.store';
import { SystemSettingStore } from '../admin-settings/system-setting.store';

@Injectable({
  providedIn: 'root'
})
export class AuthStore {
  private readonly api = inject(ApiService);
  private readonly toast = inject(ToastService);
  private readonly router = inject(Router);
  private readonly modalService = inject(ModalService);
  public readonly lang = inject(LanguageService);
 
  private readonly productStore = inject(ProductStore);
  private readonly inventoryStore = inject(InventoryStore);
  private readonly orderStore = inject(OrderStore);
  private readonly userStore = inject(UserStore);
  private readonly systemSettingStore = inject(SystemSettingStore);

  public readonly currentUser = signal<any | null>(null);
  public readonly authLoading = signal<boolean>(false);

  public readonly isAuthenticated = computed(() => this.currentUser() !== null);
  public readonly isAdmin = computed(() => this.currentUser()?.role === 'admin');

  private checkMeObservable$: Observable<any> | null = null;
  private lastCheckMeTime = 0;

  public readonly loginError = signal<string>('');
  public readonly registerError = signal<string>('');

  private translateAuthError(err: any, fallback: string): string {
    const raw: string = err?.error?.message || err?.message || '';
    const status: number = err?.status || 0;
    if (status === 422) {
      const firstField = err?.error?.errors;
      if (firstField) {
        const firstKey = Object.keys(firstField)[0];
        if (firstKey && firstField[firstKey]?.[0]) {
          return this.translateAuthError({ message: firstField[firstKey][0] }, fallback);
        }
      }
    }
    return this.toast.resolveApiMessage(raw, status, fallback);
  }

  public login(credentials: any, returnUrl: string = '/') {
    this.authLoading.set(true);
    this.loginError.set('');
    return this.api.post<any>('v1/auth/login', credentials).pipe(
      tap((res) => {
        if (res && res.requires_2fa) {
          return;
        }
        this.setFrontendLoggedInCookie();
        this.currentUser.set(res.user);
        this.toast.showToast(this.lang.translate('toast.login_success'), 'success');
        this.router.navigateByUrl(returnUrl);
        this.openWelcomeNoticeIfNeeded();
      }),
      catchError((err) => {
        const msg = this.translateAuthError(err, this.lang.translate('toast.login_failed'));
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
        this.toast.showToast(this.lang.translate('toast.register_success'), 'success');
        this.router.navigate(['/login']);
      }),
      catchError((err) => {
        const msg = this.translateAuthError(err, this.lang.translate('toast.register_failed'));
        this.registerError.set(msg);
        this.toast.showToast(msg, 'error');
        return of(null);
      }),
      finalize(() => this.authLoading.set(false))
    );
  }

  public checkMe(force = false, verifyWithoutMarker = false) {
    if (!verifyWithoutMarker && !this.hasLoggedInMarker()) {
      this.currentUser.set(null);
      return of(null);
    }

    if (!force && this.currentUser()) {
      return of(this.currentUser());
    }

    const now = Date.now();
    if (this.checkMeObservable$ && now - this.lastCheckMeTime < 2000) {
      return this.checkMeObservable$;
    }

    this.lastCheckMeTime = now;
    this.checkMeObservable$ = this.api.get<any>('v1/auth/me').pipe(
      map((res) => res?.user || null),
      tap((user) => {
        this.currentUser.set(user);
        if (user) {
          this.setFrontendLoggedInCookie();
        }
      }),
      catchError(() => {
        this.clearFrontendLoggedInCookie();
        this.currentUser.set(null);
        return of(null);
      }),
      finalize(() => {
        this.checkMeObservable$ = null;
      }),
      shareReplay(1)
    );

    return this.checkMeObservable$;
  }

  public hasLoggedInMarker(): boolean {
    if (typeof document === 'undefined') {
      return false;
    }

    return document.cookie
      .split(';')
      .some(cookie => cookie.trim() === 'auth_logged_in=true');
  }

  public logout() {
    this.authLoading.set(true);
    this.productStore.clearCache();
    this.inventoryStore.clearCache();
    this.orderStore.clearCache();
    this.userStore.clearCache();
    this.systemSettingStore.clearCache();
 
    return this.api.post<any>('v1/auth/logout', {}).pipe(
      tap(() => {
        this.clearFrontendLoggedInCookie();
        this.currentUser.set(null);
        this.toast.showToast(this.lang.translate('toast.logout_success'), 'success');
        this.router.navigate(['/storefront']);
      }),
      catchError(() => {
        this.clearFrontendLoggedInCookie();
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
        this.setFrontendLoggedInCookie();
        this.currentUser.set(res.user);
        this.toast.showToast(this.lang.translate('toast.login_success'), 'success');
        this.router.navigateByUrl(returnUrl);
        this.openWelcomeNoticeIfNeeded();
      }),
      catchError((err) => {
        const msg = err.error?.message || this.lang.translate('toast.otp_invalid');
        this.toast.showToast(msg, 'error');
        return of(null);
      }),
      finalize(() => this.authLoading.set(false))
    );
  }

  public updateProfile(name: string, phone: string) {
    this.authLoading.set(true);
    return this.api.put<any>('v1/profile', { name, phone }).pipe(
      tap((res) => {
        this.currentUser.set(res.user);
        this.toast.showToast(this.lang.translate('toast.profile_updated'), 'success');
      }),
      catchError((err) => {
        const msg = err.error?.message || this.lang.translate('toast.profile_failed');
        this.toast.showToast(msg, 'error');
        return of(null);
      }),
      finalize(() => this.authLoading.set(false))
    );
  }

  public regenerateApiKey(): Observable<any> {
    this.authLoading.set(true);
    return this.api.post<any>('v1/profile/api-key', {}).pipe(
      tap((res) => {
        if (res && res.user) {
          this.currentUser.set(res.user);
          this.toast.showToast(this.lang.translate('toast.apikey_created'), 'success');
        }
      }),
      catchError((err) => {
        const msg = err.error?.message || this.lang.translate('toast.apikey_failed');
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
        this.toast.showToast(this.lang.translate('toast.password_changed'), 'success');
      }),
      catchError((err) => {
        const msg = err.error?.message || this.lang.translate('toast.password_failed');
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
        const msg = err.error?.message || this.lang.translate('toast.2fa_enable_failed');
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
        this.toast.showToast(this.lang.translate('toast.2fa_enabled'), 'success');
      }),
      catchError((err) => {
        const msg = err.error?.message || this.lang.translate('toast.otp_invalid');
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
        this.toast.showToast(this.lang.translate('toast.2fa_disabled'), 'success');
      }),
      catchError((err) => {
        const rawMsg = err.error?.message;
        const msg = rawMsg === 'Invalid 2FA verification code.'
          ? this.lang.translate('toast.otp_invalid')
          : (rawMsg || this.lang.translate('toast.2fa_disable_failed'));
        this.toast.showToast(msg, 'error');
        return of(null);
      }),
      finalize(() => this.authLoading.set(false))
    );
  }

    private openWelcomeNoticeIfNeeded(): void {
    const show = shouldShowWelcomeNotice();
    console.log('[AuthStore] Checking welcome notice. Should show?', show);
    if (!show) return;
    
    setTimeout(() => {
      console.log('[AuthStore] Opening welcome notice modal now...');
      try {
        const ref = this.modalService.open(WelcomeNoticeModalComponent, {
          title: this.lang.translate('modal.system_notice'),
          size: 'sm',
          closeOnBackdropClick: true,
        });
        console.log('[AuthStore] Modal opened successfully', ref);
      } catch (err) {
        console.error('[AuthStore] Error opening welcome notice modal:', err);
      }
    }, 400);
  }

  private setFrontendLoggedInCookie() {
    if (typeof document !== 'undefined') {
      document.cookie = "auth_logged_in=true; path=/; max-age=604800; SameSite=Lax";
    }
  }

  private clearFrontendLoggedInCookie() {
    if (typeof document !== 'undefined') {
      document.cookie = "auth_logged_in=; path=/; expires=Thu, 01 Jan 1970 00:00:00 GMT; SameSite=Lax";
    }
  }
}
