import { Component, inject, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { ModalRef, MODAL_DATA } from '@core/services/modal-ref';
import { UserStore } from '../user.store';
import { CustomInputComponent } from '@shared/components/custom-input/custom-input.component';
import { ButtonComponent } from '@shared/components/button/button.component';

export interface AdjustBalanceModalData {
  user: {
    id: string;
    name: string;
    email: string;
    balance: number;
  };
}

@Component({
  selector: 'app-adjust-balance-modal',
  standalone: true,
  imports: [
    CommonModule,
    FormsModule,
    CustomInputComponent,
    ButtonComponent
  ],
  templateUrl: './adjust-balance-modal.component.html',
  styles: [`
    :host {
      display: block;
    }
  `]
})
export class AdjustBalanceModalComponent implements OnInit {
  public readonly modalRef = inject(ModalRef);
  public readonly data = inject<AdjustBalanceModalData>(MODAL_DATA);
  private readonly userStore = inject(UserStore);

  public type: 'add' | 'subtract' = 'add';
  public amount: number | null = null;
  public reason = '';
  public isSubmitting = false;

  public get currentBalance(): number {
    return this.data?.user?.balance || 0;
  }

  public get newBalance(): number {
    const amt = this.amount || 0;
    if (this.type === 'add') {
      return this.currentBalance + amt;
    } else {
      return Math.max(0, this.currentBalance - amt);
    }
  }

  public get isSubtractExceeded(): boolean {
    return this.type === 'subtract' && (this.amount || 0) > this.currentBalance;
  }

  ngOnInit() {}

  public onClose() {
    this.modalRef.close(false);
  }

  public onSubmit() {
    if (this.isSubmitting) return;

    if (!this.amount || this.amount <= 0) {
      return;
    }

    if (this.isSubtractExceeded) {
      return;
    }

    this.isSubmitting = true;

    const reasonText = this.reason.trim() || (this.type === 'add' ? 'Admin cộng tiền' : 'Admin trừ tiền');

    this.userStore.adjustBalance(this.data.user.id, this.type, this.amount, reasonText).subscribe({
      next: (res) => {
        this.isSubmitting = false;
        if (res) {
          this.modalRef.close(true);
        }
      },
      error: () => {
        this.isSubmitting = false;
      }
    });
  }
}
