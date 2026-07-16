import { Component, inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { StateService } from '@core/services/state.service';
import { CustomInputComponent } from '@shared/components/custom-input/custom-input.component';
import { ButtonComponent } from '@shared/components/button/button.component';
import { QrCodeModule } from 'ng-qrcode';
import { finalize } from 'rxjs/operators';

@Component({
  selector: 'app-profile-security',
  standalone: true,
  imports: [CommonModule, FormsModule, CustomInputComponent, ButtonComponent, QrCodeModule],
  templateUrl: './profile-security.component.html',
  styles: [`
    :host {
      display: block;
    }
  `]
})
export class ProfileSecurityComponent {
  public readonly state = inject(StateService);

  // 2FA variables
  public qrCodeUrl = '';
  public secretKey = '';
  public otpConfirmCode = '';
  public confirmPasswordForDisable = '';

  public showActivationSteps = false;
  public showDisableConfirm = false;
  public isSubmitting = false;

  public startEnable2Fa() {
    this.isSubmitting = true;
    this.state.authStore.enable2Fa()
      .pipe(finalize(() => this.isSubmitting = false))
      .subscribe((res) => {
        if (res) {
          this.secretKey = res.secret;
          this.qrCodeUrl = res.qr_code_url;
          this.showActivationSteps = true;
          this.otpConfirmCode = '';
        }
      });
  }

  public confirm2FaActivation() {
    if (!this.otpConfirmCode || this.otpConfirmCode.length !== 6) {
      this.state.showToast('Vui lòng nhập mã xác thực OTP gồm 6 chữ số!', 'error');
      return;
    }

    this.isSubmitting = true;
    this.state.authStore.confirm2Fa(this.otpConfirmCode)
      .pipe(finalize(() => this.isSubmitting = false))
      .subscribe((res) => {
        if (res) {
          this.showActivationSteps = false;
          this.secretKey = '';
          this.qrCodeUrl = '';
          this.otpConfirmCode = '';
        }
      });
  }

  public cancel2FaActivation() {
    this.showActivationSteps = false;
    this.secretKey = '';
    this.qrCodeUrl = '';
    this.otpConfirmCode = '';
  }

  public startDisable2Fa() {
    this.showDisableConfirm = true;
    this.confirmPasswordForDisable = '';
  }

  public confirm2FaDisable() {
    if (!this.confirmPasswordForDisable) {
      this.state.showToast('Vui lòng nhập mật khẩu xác nhận!', 'error');
      return;
    }

    this.isSubmitting = true;
    this.state.authStore.disable2Fa(this.confirmPasswordForDisable)
      .pipe(finalize(() => this.isSubmitting = false))
      .subscribe((res) => {
        if (res) {
          this.showDisableConfirm = false;
          this.confirmPasswordForDisable = '';
        }
      });
  }

  public cancel2FaDisable() {
    this.showDisableConfirm = false;
    this.confirmPasswordForDisable = '';
  }
}
