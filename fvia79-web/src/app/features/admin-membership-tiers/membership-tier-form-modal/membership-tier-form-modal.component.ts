import { Component, inject, OnInit, signal } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { ModalRef, MODAL_DATA } from '@core/services/modal-ref';
import { MembershipTierService, MembershipTier } from '@core/services/membership-tier.service';
import { ToastService } from '@core/services/toast.service';
import { LanguageService } from '@core/services/language.service';
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
  private readonly lang = inject(LanguageService);

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
      this.toast.showToast(this.lang.translate('toast.tier_name_vi_required'), 'error');
      return;
    }

    if (this.minDeposit < 0) {
      this.toast.showToast(this.lang.translate('toast.tier_min_deposit_invalid'), 'error');
      return;
    }

    if (this.discountPercent < 0 || this.discountPercent > 100) {
      this.toast.showToast(this.lang.translate('toast.tier_discount_invalid'), 'error');
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
            this.toast.showToast(this.lang.translate('toast.tier_updated'), 'success');
            this.modalRef.close(true);
          }
        },
        error: (err) => {
          this.isSubmitting.set(false);
          this.toast.showToast(this.extractErrorMessage(err, this.lang.translate('toast.tier_update_failed')), 'error');
        },
      });
    } else {
      this.tierService.createTier(payload).subscribe({
        next: (res) => {
          this.isSubmitting.set(false);
          if (res) {
            this.toast.showToast(this.lang.translate('toast.tier_created'), 'success');
            this.modalRef.close(true);
          }
        },
        error: (err) => {
          this.isSubmitting.set(false);
          this.toast.showToast(this.extractErrorMessage(err, this.lang.translate('toast.tier_create_failed')), 'error');
        },
      });
    }
  }

  private extractErrorMessage(err: any, fallback: string): string {
    if (err?.error?.errors) {
      const firstErrorKey = Object.keys(err.error.errors)[0];
      if (firstErrorKey && err.error.errors[firstErrorKey]?.length > 0) {
        return err.error.errors[firstErrorKey][0];
      }
    }
    return err?.error?.message || fallback;
  }
}
