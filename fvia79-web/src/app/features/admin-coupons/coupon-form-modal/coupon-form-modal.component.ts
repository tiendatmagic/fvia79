import { Component, inject, OnInit, signal } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { ModalRef, MODAL_DATA } from '@core/services/modal-ref';
import { CouponStore } from '../coupon.store';
import { CustomInputComponent } from '@shared/components/custom-input/custom-input.component';
import { CustomSelectComponent } from '@shared/components/custom-select/custom-select.component';
import { ButtonComponent } from '@shared/components/button/button.component';
import { ToastService } from '@core/services/toast.service';
import { LanguageService } from '@core/services/language.service';
import { TranslatePipe } from '@shared/pipes/translate.pipe';
import { finalize } from 'rxjs/operators';

import { CustomDatePickerComponent } from '@shared/components/custom-date-picker/custom-date-picker.component';

@Component({
  selector: 'app-coupon-form-modal',
  standalone: true,
  imports: [
    CommonModule,
    FormsModule,
    CustomInputComponent,
    CustomSelectComponent,
    CustomDatePickerComponent,
    ButtonComponent,
    TranslatePipe
  ],
  templateUrl: './coupon-form-modal.component.html',
  styles: [`
    :host {
      display: block;
    }
  `]
})
export class CouponFormModalComponent implements OnInit {
  private readonly modalRef = inject(ModalRef);
  private readonly modalData = inject(MODAL_DATA);
  private readonly couponStore = inject(CouponStore);
  private readonly toast = inject(ToastService);
  public readonly lang = inject(LanguageService);

  public isEditMode = false;
  public editingId: any = null;

  public code = '';
  public type = 'percentage';
  public value = 10;
  public minSpend = 0;
  public startDate = '';
  public endDate = '';
  public usageLimit = 1;

  public readonly isSubmitting = signal(false);

  public onGenerateCode(): void {
    if (this.isEditMode) return;
    const chars = '0123456789abcdefghijklmnopqrstuvwxyz';
    const length = Math.floor(Math.random() * 3) + 8;
    const array = new Uint32Array(length);
    crypto.getRandomValues(array);
    let randomCode = '';
    for (let i = 0; i < length; i++) {
      randomCode += chars[array[i] % chars.length];
    }
    this.code = randomCode;
    this.toast.showToast(this.lang.translate('toast.coupon_code_generated'), 'success');
  }

  public get typeOptions() {
    return [
      { label: this.lang.translate('admin.coupon_percentage'), value: 'percentage' },
      { label: `${this.lang.translate('admin.coupon_fixed')} ($ / USD)`, value: 'fixed' }
    ];
  }

  public ngOnInit() {
    if (this.modalData) {
      this.isEditMode = true;
      this.editingId = this.modalData.id;
      this.code = this.modalData.code;
      this.type = this.modalData.type;
      this.value = this.modalData.value;
      this.minSpend = this.modalData.minSpend || 0;
      if (this.modalData.startDate) {
        this.startDate = this.modalData.startDate.substring(0, 16).replace('T', ' ');
      }
      if (this.modalData.endDate) {
        this.endDate = this.modalData.endDate.substring(0, 16).replace('T', ' ');
      }
      this.usageLimit = this.modalData.usageLimit || 1;
    } else {
      const now = new Date();
      const year = now.getFullYear();
      const month = String(now.getMonth() + 1).padStart(2, '0');
      const day = String(now.getDate()).padStart(2, '0');
      const hour = String(now.getHours()).padStart(2, '0');
      const min = String(now.getMinutes()).padStart(2, '0');
      this.startDate = `${year}-${month}-${day} ${hour}:${min}`;

      const future = new Date(now.getTime() + 30 * 86400000);
      const fYear = future.getFullYear();
      const fMonth = String(future.getMonth() + 1).padStart(2, '0');
      const fDay = String(future.getDate()).padStart(2, '0');
      this.endDate = `${fYear}-${fMonth}-${fDay} 23:59`;
    }
  }

  public onClose() {
    this.modalRef.close(false);
  }

  public onSubmit() {
    if (this.isSubmitting()) return;

    if (!this.code) {
      this.toast.showToast(this.lang.translate('toast.coupon_code_required'), 'error');
      return;
    }

    if (this.value <= 0) {
      this.toast.showToast(this.lang.translate('toast.coupon_value_min'), 'error');
      return;
    }

    if (this.type === 'percentage' && this.value > 100) {
      this.toast.showToast(this.lang.translate('toast.coupon_percentage_limit'), 'error');
      return;
    }

    if (!this.startDate) {
      this.toast.showToast(this.lang.translate('toast.start_date_required'), 'error');
      return;
    }

    if (!this.endDate) {
      this.toast.showToast(this.lang.translate('toast.end_date_required'), 'error');
      return;
    }

    const start = new Date(this.startDate);
    const end = new Date(this.endDate);
    if (end <= start) {
      this.toast.showToast(this.lang.translate('toast.coupon_date_invalid'), 'error');
      return;
    }

    const data = {
      code: this.code.toUpperCase().trim(),
      type: this.type,
      value: this.value,
      min_spend: this.minSpend,
      start_date: this.startDate,
      end_date: this.endDate,
      usage_limit: this.usageLimit
    };

    this.isSubmitting.set(true);
    if (this.isEditMode && this.editingId) {
      this.couponStore.updateCoupon(this.editingId, data).pipe(
        finalize(() => this.isSubmitting.set(false))
      ).subscribe((res) => {
        if (res !== null) {
          this.modalRef.close(true);
        }
      });
    } else {
      this.couponStore.createCoupon(data).pipe(
        finalize(() => this.isSubmitting.set(false))
      ).subscribe((res) => {
        if (res !== null) {
          this.modalRef.close(true);
        }
      });
    }
  }
}
