import { Component, inject, signal, effect } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { RouterModule, ActivatedRoute, Router } from '@angular/router';
import { StateService } from '@core/services/state.service';
import { CustomInputComponent } from '@shared/components/custom-input/custom-input.component';
import { ButtonComponent } from '@shared/components/button/button.component';
import { CardComponent } from '@shared/components/card/card.component';
import { IconComponent } from '@shared/components/icon/icon.component';
import { LanguageService } from '@core/services/language.service';
import { TranslatePipe } from '@shared/pipes/translate.pipe';

@Component({
  selector: 'app-login',
  standalone: true,
  imports: [CommonModule, FormsModule, RouterModule, CustomInputComponent, ButtonComponent, CardComponent, IconComponent, TranslatePipe],
  templateUrl: './login.component.html',
  styles: [`
    :host {
      display: block;
    }
  `]
})
export class LoginComponent {
  public readonly state = inject(StateService);
  private readonly route = inject(ActivatedRoute);
  private readonly router = inject(Router);
  public readonly lang = inject(LanguageService);

  public email = '';
  public password = '';
  
  public requires2Fa = signal<boolean>(false);
  public otpCode = '';
  private tempToken = '';
  private returnUrl = '/';

  constructor() {
    this.returnUrl = this.route.snapshot.queryParams['returnUrl'] || '/';
    
    effect(() => {
      if (this.state.isAuthenticated()) {
        const dest = this.state.isAdmin() ? '/admin/dashboard' : this.returnUrl;
        this.router.navigateByUrl(dest);
      }
    });
  }

  public onSubmit() {
    if (this.state.authStore.authLoading()) return;

    if (this.requires2Fa()) {
      if (!this.otpCode || this.otpCode.length !== 6) {
        this.state.showToast(this.lang.translate('toast.otp_digits_required'), 'error');
        return;
      }
      this.state.authStore.login2Fa(this.tempToken, this.otpCode, this.returnUrl).subscribe();
      return;
    }

    if (!this.email || !this.password) {
      this.state.showToast(this.lang.translate('toast.login_info_required'), 'error');
      return;
    }

    if (this.email.includes('@')) {
      const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
      if (!emailRegex.test(this.email)) {
        this.state.showToast(this.lang.translate('toast.email_invalid_format'), 'error');
        return;
      }
    } else {
      const usernameRegex = /^[a-zA-Z0-9_]{3,50}$/;
      if (!usernameRegex.test(this.email)) {
        this.state.showToast(this.lang.translate('toast.username_invalid_format'), 'error');
        return;
      }
    }

    this.state.authStore.login({ email: this.email, password: this.password }, this.returnUrl).subscribe((res: any) => {
      if (res && res.requires_2fa) {
        this.requires2Fa.set(true);
        this.tempToken = res.temp_token;
        this.state.showToast(this.lang.translate('toast.requires_2fa_notice'), 'success');
      }
    });
  }
}
