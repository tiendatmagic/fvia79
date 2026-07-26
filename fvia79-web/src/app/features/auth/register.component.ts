import { Component, inject, effect, signal } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { RouterModule, Router } from '@angular/router';
import { StateService } from '@core/services/state.service';
import { ButtonComponent } from '@shared/components/button/button.component';
import { ThemeSwitcherComponent } from '@shared/components/theme-switcher/theme-switcher.component';
import { CustomInputComponent } from '@shared/components/custom-input/custom-input.component';
import { LanguageService } from '@core/services/language.service';
import { TranslatePipe } from '@shared/pipes/translate.pipe';
import { IconComponent } from '@shared/components/icon/icon.component';

@Component({
  selector: 'app-register',
  standalone: true,
  imports: [CommonModule, FormsModule, RouterModule, ButtonComponent, ThemeSwitcherComponent, CustomInputComponent, TranslatePipe, IconComponent],
  templateUrl: './register.component.html',
  styles: [`
    :host {
      display: block;
    }
  `]
})
export class RegisterComponent {
  public readonly state = inject(StateService);
  private readonly router = inject(Router);
  public readonly lang = inject(LanguageService);

  public username = '';
  public email = '';
  public password = '';
  public passwordConfirmation = '';
  
  public showPassword = signal(false);
  public showConfirmPassword = signal(false);

  public toggleShowPassword() {
    this.showPassword.update(v => !v);
  }

  public toggleShowConfirmPassword() {
    this.showConfirmPassword.update(v => !v);
  }

  constructor() {
    effect(() => {
      if (this.state.isAuthenticated()) {
        const dest = this.state.isAdmin() ? '/admin/dashboard' : '/storefront';
        this.router.navigateByUrl(dest);
      }
    });
  }

  public onSubmit() {
    if (this.state.authStore.authLoading()) return;

    if (!this.username || !this.email || !this.password || !this.passwordConfirmation) {
      this.state.showToast(this.lang.translate('toast.register_info_required'), 'error');
      return;
    }

    const usernameRegex = /^[a-zA-Z0-9_]{3,50}$/;
    if (!usernameRegex.test(this.username)) {
      this.state.showToast(this.lang.translate('toast.username_invalid_format'), 'error');
      return;
    }

    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(this.email)) {
      this.state.showToast(this.lang.translate('toast.email_invalid_format'), 'error');
      return;
    }

    if (this.password.length < 6) {
      this.state.showToast(this.lang.translate('toast.password_too_short'), 'error');
      return;
    }

    if (this.password !== this.passwordConfirmation) {
      this.state.showToast(this.lang.translate('toast.password_confirm_mismatch'), 'error');
      return;
    }

    this.state.authStore.register({
      username: this.username.toLowerCase(),
      email: this.email,
      password: this.password,
      password_confirmation: this.passwordConfirmation
    }).subscribe();
  }
}
