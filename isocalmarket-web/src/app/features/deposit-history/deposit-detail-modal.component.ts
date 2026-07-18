import { Component, Inject, OnInit, Optional, inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { MODAL_DATA, ModalRef } from '@core/services/modal-ref';
import { IconComponent } from '@shared/components/icon/icon.component';
import { ButtonComponent } from '@shared/components/button/button.component';
import { BadgeComponent } from '@shared/components/badge/badge.component';
import { ToastService } from '@core/services/toast.service';

@Component({
  selector: 'app-deposit-detail-modal',
  standalone: true,
  imports: [CommonModule, IconComponent, ButtonComponent],
  templateUrl: './deposit-detail-modal.component.html',
  styles: [`
    :host {
      display: block;
    }
  `]
})
export class DepositDetailModalComponent implements OnInit {
  private readonly toast = inject(ToastService);
  public invoice: any;

  constructor(
    @Optional() @Inject(MODAL_DATA) private modalData: any,
    @Optional() private modalRef: ModalRef
  ) {}

  ngOnInit(): void {
    if (this.modalData?.invoice) {
      this.invoice = this.modalData.invoice;
    }
  }

  public close(): void {
    if (this.modalRef) {
      this.modalRef.close();
    }
  }

  public copyText(text: string, label: string): void {
    if (!text) return;
    navigator.clipboard.writeText(text).then(() => {
      this.toast.showToast(`Đã sao chép ${label}!`, 'success');
    }).catch(() => {
      this.toast.showToast(`Không thể sao chép ${label}!`, 'error');
    });
  }
}
