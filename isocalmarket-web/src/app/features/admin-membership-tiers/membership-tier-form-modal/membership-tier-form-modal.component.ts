import { Component, inject, OnInit, signal } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { ModalRef, MODAL_DATA } from '@core/services/modal-ref';
import { MembershipTierService, MembershipTier } from '@core/services/membership-tier.service';
import { ToastService } from '@core/services/toast.service';
import { CustomInputComponent } from '@shared/components/custom-input/custom-input.component';
import { CustomSwitchComponent } from '@shared/components/custom-switch/custom-switch.component';
import { ButtonComponent } from '@shared/components/button/button.component';
import { TranslatePipe } from '@shared/pipes/translate.pipe';

export interface MembershipTierFormModalData {
  tier?: MembershipTier | null;
  maxLevel?: number;
}

@Component({
  selector: 'app-membership-tier-form-modal',
  standalone: true,
  imports: [
    CommonModule,
    FormsModule,
    CustomInputComponent,
    CustomSwitchComponent,
    ButtonComponent,
    TranslatePipe,
  ],
  templateUrl: './membership-tier-form-modal.component.html',
  styles: [`
    :host {
      display: block;
    }
  `],
})
export class MembershipTierFormModalComponent implements OnInit {
  public readonly modalRef = inject(ModalRef);
  public readonly data = inject<MembershipTierFormModalData>(MODAL_DATA);
  private readonly tierService = inject(MembershipTierService);
  private readonly toast = inject(ToastService);

  public readonly isSubmitting = signal(false);

  public level = 1;
  public nameVi = '';
  public nameEn = '';
  public minDeposit = 0;
  public discountPercent = 0;
  public icon = 'shield';
  public color = '#3b82f6';
  public isActive = true;

  public get isEditing(): boolean {
    return !!this.data?.tier;
  }

  ngOnInit(): void {
    if (this.data?.tier) {
      const t = this.data.tier;
      this.level = t.level;
      this.nameVi = t.name_vi;
      this.nameEn = t.name_en;
      this.minDeposit = t.min_deposit;
      this.discountPercent = t.discount_percent;
      this.icon = t.icon || 'shield';
      this.color = t.color || '#3b82f6';
      this.isActive = t.is_active ?? true;
    } else {
      this.level = (this.data?.maxLevel || 0) + 1;
    }
  }

  public onClose(): void {
    this.modalRef.close(false);
  }

  public onSubmit(): void {
    if (this.isSubmitting()) return;

    if (!this.nameVi.trim()) {
      this.toast.showToast('Vui lòng nhập tên cấp bậc Tiếng Việt.', 'error');
      return;
    }

    if (this.minDeposit < 0) {
      this.toast.showToast('Mức nạp tối thiểu không được nhỏ hơn 0.', 'error');
      return;
    }

    if (this.discountPercent < 0 || this.discountPercent > 100) {
      this.toast.showToast('% Chiết khấu phải từ 0 đến 100%.', 'error');
      return;
    }

    this.isSubmitting.set(true);

    const payload: Partial<MembershipTier> = {
      level: Number(this.level),
      name_vi: this.nameVi.trim(),
      name_en: this.nameEn.trim() || this.nameVi.trim(),
      min_deposit: Number(this.minDeposit),
      discount_percent: Number(this.discountPercent),
      icon: this.icon,
      color: this.color,
      is_active: this.isActive,
    };

    if (this.isEditing && this.data.tier) {
      this.tierService.updateTier(this.data.tier.id, payload).subscribe({
        next: (res) => {
          this.isSubmitting.set(false);
          if (res) {
            this.toast.showToast('Cập nhật cấp bậc thành công.', 'success');
            this.modalRef.close(true);
          }
        },
        error: (err) => {
          this.isSubmitting.set(false);
          this.toast.showToast(err.error?.message || 'Cập nhật thất bại.', 'error');
        },
      });
    } else {
      this.tierService.createTier(payload).subscribe({
        next: (res) => {
          this.isSubmitting.set(false);
          if (res) {
            this.toast.showToast('Thêm cấp bậc mới thành công.', 'success');
            this.modalRef.close(true);
          }
        },
        error: (err) => {
          this.isSubmitting.set(false);
          this.toast.showToast(err.error?.message || 'Thêm cấp bậc thất bại.', 'error');
        },
      });
    }
  }
}
