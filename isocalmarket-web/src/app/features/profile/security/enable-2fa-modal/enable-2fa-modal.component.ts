import { Component, inject, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { ModalRef, MODAL_DATA } from '@core/services/modal-ref';
import { StateService } from '@core/services/state.service';
import { CustomInputComponent } from '@shared/components/custom-input/custom-input.component';
import { ButtonComponent } from '@shared/components/button/button.component';
import { QrCodeModule } from 'ng-qrcode';
import { finalize } from 'rxjs/operators';

@Component({
  selector: 'app-enable-2fa-modal',
  standalone: true,
  imports: [CommonModule, FormsModule, CustomInputComponent, ButtonComponent, QrCodeModule],
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

  public qrCodeUrl = '';
  public secretKey = '';
  public otpConfirmCode = '';
  public isSubmitting = false;

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
    if (this.isSubmitting) return;
    if (!this.otpConfirmCode || this.otpConfirmCode.length !== 6) {
      this.state.showToast('Vui lòng nhập mã xác thực OTP gồm 6 chữ số!', 'error');
      return;
    }

    this.isSubmitting = true;
    this.state.authStore.confirm2Fa(this.otpConfirmCode)
      .pipe(finalize(() => this.isSubmitting = false))
      .subscribe((res) => {
        if (res) {
          this.modalRef.close(true);
        }
      });
  }
}
