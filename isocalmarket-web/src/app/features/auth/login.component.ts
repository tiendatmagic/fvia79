import { Component, inject, signal } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { RouterModule, ActivatedRoute } from '@angular/router';
import { StateService } from '@core/services/state.service';
import { CustomInputComponent } from '@shared/components/custom-input/custom-input.component';
import { ButtonComponent } from '@shared/components/button/button.component';

@Component({
  selector: 'app-login',
  standalone: true,
  imports: [CommonModule, FormsModule, RouterModule, CustomInputComponent, ButtonComponent],
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

  public email = '';
  public password = '';
  
  private returnUrl = '/';

  constructor() {
    this.returnUrl = this.route.snapshot.queryParams['returnUrl'] || '/';
  }

  public onSubmit() {
    if (!this.email || !this.password) {
      this.state.showToast('Vui lòng điền đầy đủ email và mật khẩu!', 'error');
      return;
    }

    this.state.authStore.login({ email: this.email, password: this.password }, this.returnUrl).subscribe();
  }
}
