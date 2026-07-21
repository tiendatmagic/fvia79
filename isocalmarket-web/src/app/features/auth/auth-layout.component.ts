import { Component, inject, signal, HostListener } from '@angular/core';
import { CommonModule, Location } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { RouterModule, Router, ActivatedRoute, NavigationEnd } from '@angular/router';
import { filter } from 'rxjs/operators';
import { StateService } from '@core/services/state.service';
import { ButtonComponent } from '@shared/components/button/button.component';
import { LogoComponent } from '@shared/components/logo/logo.component';
import { ThemeSwitcherComponent } from '@shared/components/theme-switcher/theme-switcher.component';
import { CustomInputComponent } from '@shared/components/custom-input/custom-input.component';
import { LanguageService, SupportedLanguage } from '@core/services/language.service';
import { TranslatePipe } from '@shared/pipes/translate.pipe';

@Component({
  selector: 'app-auth-layout',
  standalone: true,
  imports: [CommonModule, FormsModule, RouterModule, ButtonComponent, LogoComponent, ThemeSwitcherComponent, CustomInputComponent, TranslatePipe],
  templateUrl: './auth-layout.component.html',
  styles: [`
    :host {
      display: block;
    }
  `]
})
export class AuthLayoutComponent {
  public readonly state = inject(StateService);
  public readonly lang = inject(LanguageService);
  private readonly router = inject(Router);
  private readonly route = inject(ActivatedRoute);
  private readonly location = inject(Location);

  public showLangDropdown = signal(false);

  public toggleLangDropdown(event: Event) {
    event.stopPropagation();
    this.showLangDropdown.update(v => !v);
  }

  public selectLang(lang: SupportedLanguage) {
    this.lang.setLanguage(lang);
    this.showLangDropdown.set(false);
  }

  @HostListener('document:click')
  public closeDropdowns() {
    this.showLangDropdown.set(false);
  }

  // Tab state - driven by URL, never triggers left panel re-render
  public readonly isLogin = signal(true);

  // Login fields
  public loginEmail = '';
  public loginPassword = '';
  public requires2Fa = signal(false);
  public showLoginPassword = signal(false);
  public otpCode = '';
  private tempToken = '';
  private returnUrl = '/';

  // Register fields
  public regUsername = '';
  public regEmail = '';
  public regPassword = '';
  public regPasswordConfirmation = '';
  public showRegPassword = signal(false);
  public showRegConfirmPassword = signal(false);

  constructor() {
    // Detect initial tab from URL
    this.isLogin.set(!this.router.url.includes('register'));
    this.returnUrl = this.route.snapshot.queryParams['returnUrl'] || '/';

    // Sync tab when browser back/forward is used
    this.router.events
      .pipe(filter(e => e instanceof NavigationEnd))
      .subscribe(e => {
        this.isLogin.set(!(e as NavigationEnd).url.includes('register'));
      });
  }

  public switchTab(tab: 'login' | 'register') {
    const url = tab === 'login' ? '/login' : '/register';
    // Update URL without triggering route change = zero component swap = zero flicker
    this.location.replaceState(url);
    this.isLogin.set(tab === 'login');
    this.state.authStore.loginError.set('');
    this.state.authStore.registerError.set('');
  }

  // --- Login methods ---
  public toggleLoginPassword() {
    this.showLoginPassword.update(v => !v);
  }

  public onLoginSubmit() {
    if (this.state.authStore.authLoading()) return;

    if (this.requires2Fa()) {
      if (!this.otpCode || this.otpCode.length !== 6) {
        this.state.showToast(this.lang.translate('toast.otp_digits_required'), 'error');
        return;
      }
      this.state.authStore.login2Fa(this.tempToken, this.otpCode, this.returnUrl).subscribe();
      return;
    }

    if (!this.loginEmail || !this.loginPassword) {
      this.state.showToast(this.lang.translate('toast.login_info_required'), 'error');
      return;
    }

    if (this.loginEmail.includes('@')) {
      const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
      if (!emailRegex.test(this.loginEmail)) {
        this.state.showToast(this.lang.translate('toast.email_invalid_format'), 'error');
        return;
      }
    } else {
      const usernameRegex = /^[a-zA-Z0-9_]{3,50}$/;
      if (!usernameRegex.test(this.loginEmail)) {
        this.state.showToast(this.lang.translate('toast.username_invalid_format'), 'error');
        return;
      }
    }

    this.state.authStore.login({ email: this.loginEmail, password: this.loginPassword }, this.returnUrl).subscribe((res: any) => {
      if (res && res.requires_2fa) {
        this.requires2Fa.set(true);
        this.tempToken = res.temp_token;
        this.state.showToast(this.lang.translate('toast.requires_2fa_notice'), 'success');
      }
    });
  }

  // --- Register methods ---
  public toggleRegPassword() {
    this.showRegPassword.update(v => !v);
  }

  public toggleRegConfirmPassword() {
    this.showRegConfirmPassword.update(v => !v);
  }

  public onRegisterSubmit() {
    if (this.state.authStore.authLoading()) return;

    if (!this.regUsername || !this.regEmail || !this.regPassword || !this.regPasswordConfirmation) {
      this.state.showToast(this.lang.translate('toast.register_info_required'), 'error');
      return;
    }

    const usernameRegex = /^[a-zA-Z0-9_]{3,50}$/;
    if (!usernameRegex.test(this.regUsername)) {
      this.state.showToast(this.lang.translate('toast.username_invalid_format'), 'error');
      return;
    }

    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(this.regEmail)) {
      this.state.showToast(this.lang.translate('toast.email_invalid_format'), 'error');
      return;
    }

    if (this.regPassword.length < 6) {
      this.state.showToast(this.lang.translate('toast.password_too_short'), 'error');
      return;
    }

    if (this.regPassword !== this.regPasswordConfirmation) {
      this.state.showToast(this.lang.translate('toast.password_confirm_mismatch'), 'error');
      return;
    }

    this.state.authStore.register({
      username: this.regUsername.toLowerCase(),
      email: this.regEmail,
      password: this.regPassword,
      password_confirmation: this.regPasswordConfirmation
    }).subscribe();
  }
}
