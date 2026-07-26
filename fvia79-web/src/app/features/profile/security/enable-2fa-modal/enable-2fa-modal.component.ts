import { Component, inject, OnInit, signal } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { ModalRef, MODAL_DATA } from '@core/services/modal-ref';
import { StateService } from '@core/services/state.service';
import { CustomInputComponent } from '@shared/components/custom-input/custom-input.component';
import { ButtonComponent } from '@shared/components/button/button.component';
import { QrCodeModule } from 'ng-qrcode';
import { TranslatePipe } from '@shared/pipes/translate.pipe';
import { LanguageService } from '@core/services/language.service';
import { finalize } from 'rxjs/operators';

@Component({
  selector: 'app-enable-2fa-modal',
  standalone: true,
  imports: [CommonModule, FormsModule, CustomInputComponent, ButtonComponent, QrCodeModule, TranslatePipe],
  templateUrl: './enable-2fa-modal.component.html',
  styles: [`
    :host {
      display: block;
    }
  `]
})
export class Enable2faModalComponent implements OnInit {
  private readonly modalRef = inject(ModalRef);
  private readonly modalData = inject(MODAL_DATA);
  public readonly state = inject(StateService);
  public readonly lang = inject(LanguageService);

  public qrCodeUrl = '';
  public secretKey = '';
  public otpConfirmCode = '';
  public readonly isSubmitting = signal(false);

  ngOnInit() {
    if (this.modalData) {
      this.qrCodeUrl = this.modalData.qr_code_url;
      this.secretKey = this.modalData.secret;
    }
  }

  public onClose() {
    this.modalRef.close(false);
  }

  public onConfirm() {
    if (this.isSubmitting()) return;
    if (!this.otpConfirmCode || this.otpConfirmCode.length !== 6) {
      this.state.showToast(this.lang.translate('toast.otp_digits_required'), 'error');
      return;
    }

    this.isSubmitting.set(true);
    this.state.authStore.confirm2Fa(this.otpConfirmCode)
      .pipe(finalize(() => this.isSubmitting.set(false)))
      .subscribe((res) => {
        if (res) {
          this.modalRef.close(true);
        }
      });
  }
}
