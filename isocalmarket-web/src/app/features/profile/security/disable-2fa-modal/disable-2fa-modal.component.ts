import { Component, inject, signal } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { ModalRef } from '@core/services/modal-ref';
import { StateService } from '@core/services/state.service';
import { CustomInputComponent } from '@shared/components/custom-input/custom-input.component';
import { ButtonComponent } from '@shared/components/button/button.component';
import { LanguageService } from '@core/services/language.service';
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
  public readonly lang = inject(LanguageService);

  public code = '';
  public readonly isSubmitting = signal(false);

  public onClose() {
    this.modalRef.close(false);
  }

  public onConfirm() {
    if (this.isSubmitting()) return;
    if (!this.code) {
      this.state.showToast(this.lang.translate('toast.otp_required'), 'error');
      return;
    }

    if (this.code.length !== 6 || isNaN(Number(this.code))) {
      this.state.showToast(this.lang.translate('toast.otp_digits_required'), 'error');
      return;
    }

    this.isSubmitting.set(true);
    this.state.authStore.disable2Fa(this.code)
      .pipe(finalize(() => this.isSubmitting.set(false)))
      .subscribe((res) => {
        if (res) {
          this.modalRef.close(true);
        }
      });
  }
}
