import { Component, inject, OnInit, signal } from '@angular/core';
import { CommonModule } from '@angular/common';
import { ModalRef, MODAL_DATA } from '@core/services/modal-ref';
import { StateService } from '@core/services/state.service';
import { ApiService } from '@core/services/api.service';
import { SkeletonLoaderComponent } from '@shared/components/skeleton-loader/skeleton-loader.component';
import { ButtonComponent } from '@shared/components/button/button.component';

@Component({
  selector: 'app-order-detail-modal',
  standalone: true,
  imports: [
    CommonModule,
    SkeletonLoaderComponent,
    ButtonComponent
  ],
  templateUrl: './order-detail-modal.component.html',
  styles: [`
    :host {
      display: block;
    }
  `]
})
export class OrderDetailModalComponent implements OnInit {
  private readonly modalRef = inject(ModalRef);
  private readonly modalData = inject(MODAL_DATA);
  private readonly apiService = inject(ApiService);
  public readonly state = inject(StateService);

  public orderId = '';
  public orderDetails = signal<any | null>(null);
  public loadingDetails = signal<boolean>(true);

  ngOnInit() {
    if (this.modalData) {
      this.orderId = this.modalData.orderId;
      this.fetchOrderDetails();
    }
  }

  public fetchOrderDetails() {
    this.loadingDetails.set(true);
    this.apiService.get<any>(`v1/orders/${this.orderId}`).subscribe({
      next: (res) => {
        this.orderDetails.set(res);
        this.loadingDetails.set(false);
      },
      error: () => {
        this.state.showToast('Không thể tải chi tiết đơn hàng!', 'error');
        this.loadingDetails.set(false);
        this.modalRef.close(false);
      }
    });
  }

  public onClose() {
    this.modalRef.close(false);
  }

  public copyToClipboard(text: string) {
    if (typeof window !== 'undefined' && navigator.clipboard) {
      navigator.clipboard.writeText(text).then(() => {
        this.state.showToast('Đã copy thông tin tài khoản!', 'success');
      }).catch(() => {
        this.state.showToast('Không thể copy tự động!', 'error');
      });
    }
  }

  public downloadAccounts() {
    const detail = this.orderDetails();
    if (!detail || !detail.accounts || detail.accounts.length === 0) return;

    const content = detail.accounts.map((acc: any) => acc.data).join('\n');
    const blob = new Blob([content], { type: 'text/plain;charset=utf-8' });
    const url = URL.createObjectURL(blob);
    
    const a = document.createElement('a');
    a.href = url;
    a.download = `isocalmarket_${detail.order_code}_accounts.txt`;
    document.body.appendChild(a);
    a.click();
    
    document.body.removeChild(a);
    URL.revokeObjectURL(url);
    this.state.showToast('Đã tải xuống danh sách tài khoản dạng .txt!', 'success');
  }
}
