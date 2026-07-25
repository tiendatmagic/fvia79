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

  ngOnInit() {}

  public onClose() {
    this.modalRef.close(false);
  }

  public onSubmit() {
    if (this.isSubmitting()) return;
    if (this.cumulativeAmount === null || this.cumulativeAmount < 0) return;

    this.isSubmitting.set(true);
    const reasonText = this.cumulativeReason.trim() || this.lang.translate('admin.cumulative_deposit_reason');

    this.userStore.adjustCumulativeDeposit(this.data.user.id, this.cumulativeType, this.cumulativeAmount, reasonText).subscribe({
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
