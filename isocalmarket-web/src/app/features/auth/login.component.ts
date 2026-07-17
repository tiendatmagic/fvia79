import { Component, inject, signal, effect } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { RouterModule, ActivatedRoute, Router } from '@angular/router';
import { StateService } from '@core/services/state.service';
import { CustomInputComponent } from '@shared/components/custom-input/custom-input.component';
import { ButtonComponent } from '@shared/components/button/button.component';
import { CardComponent } from '@shared/components/card/card.component';
import { IconComponent } from '@shared/components/icon/icon.component';

@Component({
  selector: 'app-login',
  standalone: true,
  imports: [CommonModule, FormsModule, RouterModule, CustomInputComponent, ButtonComponent, CardComponent, IconComponent],
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
    if (this.requires2Fa()) {
      if (!this.otpCode || this.otpCode.length !== 6) {
        this.state.showToast('Vui lòng nhập mã xác thực OTP gồm 6 chữ số!', 'error');
        return;
      }
      this.state.authStore.login2Fa(this.tempToken, this.otpCode, this.returnUrl).subscribe();
      return;
    }

    if (!this.email || !this.password) {
      this.state.showToast('Vui lòng điền đầy đủ email và mật khẩu!', 'error');
      return;
    }

    this.state.authStore.login({ email: this.email, password: this.password }, this.returnUrl).subscribe((res: any) => {
      if (res && res.requires_2fa) {
        this.requires2Fa.set(true);
        this.tempToken = res.temp_token;
        this.state.showToast('Tài khoản đã kích hoạt 2FA. Vui lòng nhập mã OTP để tiếp tục.', 'success');
      }
    });
  }
}
