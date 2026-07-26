import { Component, inject, OnInit, signal } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { ModalRef, MODAL_DATA } from '@core/services/modal-ref';
import { UserStore } from '../user.store';
import { CustomInputComponent } from '@shared/components/custom-input/custom-input.component';
import { ButtonComponent } from '@shared/components/button/button.component';
import { TranslatePipe } from '@shared/pipes/translate.pipe';
import { LanguageService } from '@core/services/language.service';

export interface AdjustCumulativeDepositModalData {
  user: {
    id: string;
    name: string;
    email: string;
    total_deposited?: number;
    membership_tier?: any;
  };
}

@Component({
  selector: 'app-adjust-cumulative-deposit-modal',
  standalone: true,
  imports: [
    CommonModule,
    FormsModule,
    CustomInputComponent,
    ButtonComponent,
    TranslatePipe,
  ],
  templateUrl: './adjust-cumulative-deposit-modal.component.html',
  styles: [`
    :host {
      display: block;
    }
  `],
})
export class AdjustCumulativeDepositModalComponent implements OnInit {
  public readonly modalRef = inject(ModalRef);
  public readonly data = inject<AdjustCumulativeDepositModalData>(MODAL_DATA);
  private readonly userStore = inject(UserStore);
  private readonly lang = inject(LanguageService);

  public cumulativeType: 'add' | 'subtract' | 'set' = 'add';
  public cumulativeAmount: number | null = null;
  public cumulativeReason = '';

  public readonly isSubmitting = signal(false);

  public get currentTotalDeposited(): number {
    return this.data?.user?.total_deposited || 0;
  }

  public get isInvalidAmount(): boolean {
    if (this.cumulativeAmount === null || this.cumulativeAmount === undefined) {
      return true;
    }
    // FE validation: For 'add' and 'subtract', amount MUST be > 0 (cannot be 0)
    if (this.cumulativeType === 'add' || this.cumulativeType === 'subtract') {
      return this.cumulativeAmount <= 0;
    }
    // For 'set', amount can be >= 0 (e.g. set total_deposited to 0)
    return this.cumulativeAmount < 0;
  }

  ngOnInit() {}

  public onClose() {
    this.modalRef.close(false);
  }

  public onSubmit() {
    if (this.isSubmitting() || this.isInvalidAmount) return;

    this.isSubmitting.set(true);
    const reasonText = this.cumulativeReason.trim() || this.lang.translate('admin.cumulative_deposit_reason');

    this.userStore.adjustCumulativeDeposit(this.data.user.id, this.cumulativeType, this.cumulativeAmount!, reasonText).subscribe({
      next: (res) => {
        this.isSubmitting.set(false);
        if (res) {
          this.modalRef.close(true);
        }
      },
      error: () => {
        this.isSubmitting.set(false);
      },
    });
  }
}
