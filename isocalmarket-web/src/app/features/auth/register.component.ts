import { Component, inject, effect } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { RouterModule, Router } from '@angular/router';
import { StateService } from '@core/services/state.service';
import { CustomInputComponent } from '@shared/components/custom-input/custom-input.component';
import { ButtonComponent } from '@shared/components/button/button.component';
import { CardComponent } from '@shared/components/card/card.component';
import { IconComponent } from '@shared/components/icon/icon.component';

@Component({
  selector: 'app-register',
  standalone: true,
  imports: [CommonModule, FormsModule, RouterModule, CustomInputComponent, ButtonComponent, CardComponent, IconComponent],
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

  public name = '';
  public email = '';
  public password = '';
  public passwordConfirmation = '';

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

    if (!this.name || !this.email || !this.password || !this.passwordConfirmation) {
      this.state.showToast('Vui lòng điền đầy đủ các thông tin!', 'error');
      return;
    }

    if (this.password !== this.passwordConfirmation) {
      this.state.showToast('Mật khẩu xác nhận không khớp!', 'error');
      return;
    }

    this.state.authStore.register({
      name: this.name,
      email: this.email,
      password: this.password,
      password_confirmation: this.passwordConfirmation
    }).subscribe();
  }
}
