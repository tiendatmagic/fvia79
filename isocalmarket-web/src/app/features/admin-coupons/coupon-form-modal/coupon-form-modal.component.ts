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

@Component({
  selector: 'app-coupon-form-modal',
  standalone: true,
  imports: [
    CommonModule,
    FormsModule,
    CustomInputComponent,
    CustomSelectComponent,
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
        this.startDate = this.modalData.startDate.substring(0, 10);
      }
      if (this.modalData.endDate) {
        this.endDate = this.modalData.endDate.substring(0, 10);
      }
      this.usageLimit = this.modalData.usageLimit || 1;
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
