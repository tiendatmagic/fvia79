import { Component, inject, signal, HostListener, OnInit } from '@angular/core';
import { CommonModule, Location } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { RouterModule, Router, ActivatedRoute, NavigationEnd } from '@angular/router';
import { filter } from 'rxjs/operators';
import { StateService } from '@core/services/state.service';
import { PublicSettingStore } from '@core/services/public-setting.store';
import { ButtonComponent } from '@shared/components/button/button.component';
import { LogoComponent } from '@shared/components/logo/logo.component';
import { ThemeSwitcherComponent } from '@shared/components/theme-switcher/theme-switcher.component';
import { CustomInputComponent } from '@shared/components/custom-input/custom-input.component';
import { CustomCheckboxComponent } from '@shared/components/custom-checkbox/custom-checkbox.component';
import { LanguageService, SupportedLanguage } from '@core/services/language.service';
import { TranslatePipe } from '@shared/pipes/translate.pipe';
import { IconComponent } from '@shared/components/icon/icon.component';

export type AuthTab = 'login' | 'register' | 'forgot-password';

@Component({
  selector: 'app-auth-layout',
  standalone: true,
  imports: [
    CommonModule,
    FormsModule,
    RouterModule,
    ButtonComponent,
    LogoComponent,
    ThemeSwitcherComponent,
    CustomInputComponent,
    CustomCheckboxComponent,
    TranslatePipe,
    IconComponent
  ],
  templateUrl: './auth-layout.component.html',
  styles: [`
    :host {
      display: block;
    }
  `]
})
export class AuthLayoutComponent implements OnInit {
  public readonly state = inject(StateService);
  public readonly lang = inject(LanguageService);
  public readonly publicSettingStore = inject(PublicSettingStore);
  public readonly router = inject(Router);
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

  public readonly activeTab = signal<AuthTab>('login');
  public loginEmail = '';
  public loginPassword = '';
  public rememberMe = true;
  public requires2Fa = signal(false);
  public showLoginPassword = signal(false);
  public otpCode = '';
  private tempToken = '';
  private returnUrl = '/';
  public regUsername = '';
  public regEmail = '';
  public regPassword = '';
  public regPasswordConfirmation = '';
  public showRegPassword = signal(false);
  public showRegConfirmPassword = signal(false);

  constructor() {
    const initialUrl = this.router.url;
    if (initialUrl.includes('register')) {
      this.activeTab.set('register');
    } else if (initialUrl.includes('forgot-password')) {
      this.activeTab.set('forgot-password');
    } else {
      this.activeTab.set('login');
    }

    this.returnUrl = this.route.snapshot.queryParams['returnUrl'] || '/';

    this.router.events
      .pipe(filter(e => e instanceof NavigationEnd))
      .subscribe(e => {
        const u = (e as NavigationEnd).url;
        if (u.includes('register')) {
          this.activeTab.set('register');
        } else if (u.includes('forgot-password')) {
          this.activeTab.set('forgot-password');
        } else {
          this.activeTab.set('login');
        }
      });
  }

  ngOnInit(): void {
    this.publicSettingStore.fetchPublicSettings().subscribe();
  }

  public switchTab(tab: AuthTab) {
    const url = tab === 'login' ? '/login' : tab === 'register' ? '/register' : '/forgot-password';
    this.location.replaceState(url);
    this.activeTab.set(tab);
    this.state.authStore.loginError.set('');
    this.state.authStore.registerError.set('');
  }

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

    if (!this.rememberMe) {
      this.state.showToast(this.lang.translate('toast.terms_required'), 'error');
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

  public get showTelegramSupport(): boolean {
    const val = this.publicSettingStore.settings().telegram_support_enabled;
    return String(val) === 'true' || String(val) === '1';
  }

  public get showTelegramBot(): boolean {
    const settings = this.publicSettingStore.settings();
    const val = settings.telegram_bot_contact_enabled;
    const isEnabled = String(val) === 'true' || String(val) === '1';
    return isEnabled && !!this.telegramBotUrl;
  }

  public get telegramSupportUrl(): string {
    const raw = this.publicSettingStore.settings().telegram_support_username;
    const username = (raw && raw.trim()) ? raw.trim() : 'emmy203012';
    if (username.startsWith('http://') || username.startsWith('https://')) {
      return username;
    }
    const clean = username.replace(/^@/, '');
    return `https://t.me/${clean}`;
  }

  public get telegramSupportDisplay(): string {
    const raw = this.publicSettingStore.settings().telegram_support_username;
    const username = (raw && raw.trim()) ? raw.trim() : 'emmy203012';
    if (username.startsWith('http://') || username.startsWith('https://')) {
      const parts = username.split('/');
      const last = parts[parts.length - 1] || username;
      return last.startsWith('@') ? last : `@${last}`;
    }
    return username.startsWith('@') ? username : `@${username}`;
  }

  public get telegramBotUrl(): string {
    const bot = (this.publicSettingStore.settings().telegram_bot_username || '').trim();
    if (!bot) return '';
    if (bot.startsWith('http://') || bot.startsWith('https://')) {
      return bot;
    }
    const clean = bot.replace(/^@/, '');
    return `https://t.me/${clean}`;
  }

  public get telegramBotUsernameDisplay(): string {
    const bot = (this.publicSettingStore.settings().telegram_bot_username || '').trim();
    if (!bot) return '';
    if (bot.startsWith('http://') || bot.startsWith('https://')) {
      const parts = bot.split('/');
      const last = parts[parts.length - 1] || bot;
      return last.startsWith('@') ? last : `@${last}`;
    }
    return bot.startsWith('@') ? bot : `@${bot}`;
  }

  public copyLink(url: string): void {
    if (!url) return;
    if (navigator.clipboard) {
      navigator.clipboard.writeText(url).then(() => {
        this.state.showToast(this.lang.translate('contact.link_copied'), 'success');
      }).catch(() => {
        this.fallbackCopy(url);
      });
    } else {
      this.fallbackCopy(url);
    }
  }

  private fallbackCopy(url: string): void {
    const textArea = document.createElement('textarea');
    textArea.value = url;
    document.body.appendChild(textArea);
    textArea.select();
    try {
      document.execCommand('copy');
      this.state.showToast(this.lang.translate('contact.link_copied'), 'success');
    } catch {
      this.state.showToast(url, 'warning');
    }
    document.body.removeChild(textArea);
  }
}
