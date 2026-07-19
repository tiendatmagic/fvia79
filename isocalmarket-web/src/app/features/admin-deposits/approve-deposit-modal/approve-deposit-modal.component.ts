import { Component, inject, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { ModalRef, MODAL_DATA } from '@core/services/modal-ref';
import { AdminDepositStore } from '../admin-deposit.store';
import { CustomInputComponent } from '@shared/components/custom-input/custom-input.component';
import { ButtonComponent } from '@shared/components/button/button.component';

export interface ApproveDepositModalData {
  deposit: {
    id: string;
    code: string;
    userName: string;
    userEmail: string;
    amountUsdt: number;
    amountVnd: number;
    paymentMethod: string;
  };
}

@Component({
  selector: 'app-approve-deposit-modal',
  standalone: true,
  imports: [
    CommonModule,
    FormsModule,
    CustomInputComponent,
    ButtonComponent
  ],
  templateUrl: './approve-deposit-modal.component.html',
  styles: [`
    :host {
      display: block;
    }
  `]
})
export class ApproveDepositModalComponent implements OnInit {
  public readonly modalRef = inject(ModalRef);
  public readonly data = inject<ApproveDepositModalData>(MODAL_DATA);
  private readonly depositStore = inject(AdminDepositStore);

  public txHash = '';
  public isSubmitting = false;

  ngOnInit() {}

  public onClose() {
    this.modalRef.close(false);
  }

  public onSubmit() {
    if (this.isSubmitting) return;

    this.isSubmitting = true;

    this.depositStore.approveDeposit(this.data.deposit.id, this.txHash.trim() || undefined).subscribe({
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
