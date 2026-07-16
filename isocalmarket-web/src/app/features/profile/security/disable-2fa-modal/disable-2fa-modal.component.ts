import { Component, inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { ModalRef } from '@core/services/modal-ref';
import { StateService } from '@core/services/state.service';
import { CustomInputComponent } from '@shared/components/custom-input/custom-input.component';
import { ButtonComponent } from '@shared/components/button/button.component';
import { finalize } from 'rxjs/operators';

@Component({
  selector: 'app-disable-2fa-modal',
  standalone: true,
  imports: [CommonModule, FormsModule, CustomInputComponent, ButtonComponent],
  templateUrl: './disable-2fa-modal.component.html',
  styles: [`
    :host {
      display: block;
    }
  `]
})
export class Disable2faModalComponent {
  private readonly modalRef = inject(ModalRef);
  public readonly state = inject(StateService);

  public password = '';
  public isSubmitting = false;

  public onClose() {
    this.modalRef.close(false);
  }

  public onConfirm() {
    if (!this.password) {
      this.state.showToast('Vui lòng nhập mật khẩu xác nhận!', 'error');
      return;
    }

    this.isSubmitting = true;
    this.state.authStore.disable2Fa(this.password)
      .pipe(finalize(() => this.isSubmitting = false))
      .subscribe((res) => {
        if (res) {
          this.modalRef.close(true);
        }
      });
  }
}
