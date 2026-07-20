import { Component, inject, signal } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { StateService } from '@core/services/state.service';
import { CustomInputComponent } from '@shared/components/custom-input/custom-input.component';
import { ButtonComponent } from '@shared/components/button/button.component';
import { finalize } from 'rxjs/operators';

@Component({
  selector: 'app-profile-password',
  standalone: true,
  imports: [CommonModule, FormsModule, CustomInputComponent, ButtonComponent],
  templateUrl: './profile-password.component.html',
  styles: [`
    :host {
      display: block;
    }
  `]
})
export class ProfilePasswordComponent {
  public readonly state = inject(StateService);

  public currentPassword = '';
  public newPassword = '';
  public newPasswordConfirmation = '';
  public readonly isSubmitting = signal(false);

  public onSubmit() {
    if (this.isSubmitting()) return;
    if (!this.currentPassword || !this.newPassword || !this.newPasswordConfirmation) {
      this.state.showToast('Vui lòng điền đầy đủ thông tin mật khẩu!', 'error');
      return;
    }

    if (this.newPassword !== this.newPasswordConfirmation) {
      this.state.showToast('Xác nhận mật khẩu mới không trùng khớp!', 'error');
      return;
    }

    this.isSubmitting.set(true);
    const payload = {
      current_password: this.currentPassword,
      new_password: this.newPassword,
      new_password_confirmation: this.newPasswordConfirmation
    };

    this.state.authStore.changePassword(payload)
      .pipe(finalize(() => this.isSubmitting.set(false)))
      .subscribe((res) => {
        if (res) {
          this.currentPassword = '';
          this.newPassword = '';
          this.newPasswordConfirmation = '';
        }
      });
  }
}
