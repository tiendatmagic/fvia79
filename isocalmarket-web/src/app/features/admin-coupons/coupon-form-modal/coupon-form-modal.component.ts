import { Component, inject, OnInit, signal } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { ModalRef, MODAL_DATA } from '@core/services/modal-ref';
import { CouponStore } from '../coupon.store';
import { CustomInputComponent } from '@shared/components/custom-input/custom-input.component';
import { CustomSelectComponent } from '@shared/components/custom-select/custom-select.component';
import { ButtonComponent } from '@shared/components/button/button.component';
import { ToastService } from '@core/services/toast.service';
import { finalize } from 'rxjs/operators';

@Component({
  selector: 'app-coupon-form-modal',
  standalone: true,
  imports: [
    CommonModule,
    FormsModule,
    CustomInputComponent,
    CustomSelectComponent,
    ButtonComponent
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

  public readonly typeOptions = [
    { label: 'Phần trăm (%)', value: 'percentage' },
    { label: 'Số tiền cố định (VND)', value: 'fixed' }
  ];

  ngOnInit() {
    const today = new Date();
    const nextWeek = new Date();
    nextWeek.setDate(today.getDate() + 7);

    this.startDate = this.formatDate(today);
    this.endDate = this.formatDate(nextWeek);

    if (this.modalData) {
      this.isEditMode = true;
      this.editingId = this.modalData.id;
      this.code = this.modalData.code;
      this.type = this.modalData.type;
      this.value = this.modalData.value;
      this.minSpend = this.modalData.min_spend;
      this.startDate = this.modalData.start_date ? this.modalData.start_date.substring(0, 10) : this.formatDate(today);
      this.endDate = this.modalData.end_date ? this.modalData.end_date.substring(0, 10) : this.formatDate(nextWeek);
      this.usageLimit = this.modalData.usage_limit;
    }
  }

  private formatDate(date: Date): string {
    const yyyy = date.getFullYear();
    const mm = String(date.getMonth() + 1).padStart(2, '0');
    const dd = String(date.getDate()).padStart(2, '0');
    return `${yyyy}-${mm}-${dd}`;
  }

  public onClose() {
    this.modalRef.close(false);
  }

  public onSubmit() {
    if (this.isSubmitting()) return;

    if (!this.code) {
      this.toast.showToast('Vui lòng nhập mã giảm giá!', 'error');
      return;
    }

    if (this.value <= 0) {
      this.toast.showToast('Giá trị giảm giá phải lớn hơn 0!', 'error');
      return;
    }

    if (this.type === 'percentage' && this.value > 100) {
      this.toast.showToast('Phần trăm giảm giá tối đa là 100%!', 'error');
      return;
    }

    if (!this.startDate) {
      this.toast.showToast('Vui lòng chọn ngày bắt đầu!', 'error');
      return;
    }

    if (!this.endDate) {
      this.toast.showToast('Vui lòng chọn ngày kết thúc!', 'error');
      return;
    }

    const start = new Date(this.startDate);
    const end = new Date(this.endDate);
    if (end <= start) {
      this.toast.showToast('Ngày kết thúc phải sau ngày bắt đầu!', 'error');
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
