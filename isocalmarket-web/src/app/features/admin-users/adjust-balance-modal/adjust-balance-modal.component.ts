import { Component, inject, OnInit, signal } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { ModalRef, MODAL_DATA } from '@core/services/modal-ref';
import { UserStore } from '../user.store';
import { CustomInputComponent } from '@shared/components/custom-input/custom-input.component';
import { CustomCheckboxComponent } from '@shared/components/custom-checkbox/custom-checkbox.component';
import { ButtonComponent } from '@shared/components/button/button.component';
import { TranslatePipe } from '@shared/pipes/translate.pipe';
import { LanguageService } from '@core/services/language.service';

export interface AdjustBalanceModalData {
  user: {
    id: string;
    name: string;
    email: string;
    balance: number;
    total_deposited?: number;
    membership_tier?: any;
  };
}

@Component({
  selector: 'app-adjust-balance-modal',
  standalone: true,
  imports: [
    CommonModule,
    FormsModule,
    CustomInputComponent,
    CustomCheckboxComponent,
    ButtonComponent,
    TranslatePipe,
  ],
  templateUrl: './adjust-balance-modal.component.html',
  styles: [`
    :host {
      display: block;
    }
  `],
})
export class AdjustBalanceModalComponent implements OnInit {
  public readonly modalRef = inject(ModalRef);
  public readonly data = inject<AdjustBalanceModalData>(MODAL_DATA);
  private readonly userStore = inject(UserStore);
  private readonly lang = inject(LanguageService);

  // Balance Form
  public type: 'add' | 'subtract' = 'add';
  public amount: number | null = null;
  public affectCumulative = true;
  public reason = '';

  public readonly isSubmitting = signal(false);

  public get currentBalance(): number {
    return this.data?.user?.balance || 0;
  }

  public get isSubtractExceeded(): boolean {
    if (this.type === 'subtract' && this.amount) {
      return this.amount > this.currentBalance;
    }
    return false;
  }

  ngOnInit() {}

  public onClose() {
    this.modalRef.close(false);
  }

  public onSubmit() {
    if (this.isSubmitting()) return;
    if (!this.amount || this.amount <= 0 || this.isSubtractExceeded) return;

    this.isSubmitting.set(true);
    const reasonText = this.reason.trim()
      || this.lang.translate(this.type === 'add' ? 'balance.admin_add_reason' : 'balance.admin_subtract_reason');

    this.userStore.adjustBalance(this.data.user.id, this.type, this.amount, reasonText, this.affectCumulative).subscribe({
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
