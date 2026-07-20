import { Component, inject, OnInit, signal } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { Router, RouterModule } from '@angular/router';
import { StateService } from '@core/services/state.service';
import { ModalService } from '@core/services/modal.service';
import { CustomInputComponent } from '@shared/components/custom-input/custom-input.component';
import { ButtonComponent } from '@shared/components/button/button.component';
import { PageHeaderComponent } from '@shared/components/page-header/page-header.component';
import { CardComponent } from '@shared/components/card/card.component';
import { IconComponent } from '@shared/components/icon/icon.component';
import { SkeletonLoaderComponent } from '@shared/components/skeleton-loader/skeleton-loader.component';

@Component({
  selector: 'app-deposit',
  standalone: true,
  imports: [
    CommonModule,
    FormsModule,
    RouterModule,
    CustomInputComponent,
    ButtonComponent,
    PageHeaderComponent,
    CardComponent,
    IconComponent,
    SkeletonLoaderComponent
  ],
  templateUrl: './deposit.component.html',
  styles: [`
    :host {
      display: block;
    }
  `]
})
export class DepositComponent implements OnInit {
  public readonly state = inject(StateService);
  private readonly router = inject(Router);
  private readonly modalService = inject(ModalService);

  public amountUsdt: number | null = null;
  public vndEquivalent = 0;
  public selectedNetwork = 'BEP20';
  public paymentMethod = 'bep20';
  public payerBinanceId = '';
  public readonly isSubmitting = signal(false);

  ngOnInit() {
    this.state.authStore.checkMe(true).subscribe();
    this.state.depositStore.fetchRate().subscribe({
      next: () => this.onAmountChange(),
      error: () => this.state.showToast('Không thể kết nối lấy tỷ giá từ CoinGecko!', 'error')
    });
  }

  // Tự động tính VND tương đương
  public onAmountChange() {
    const rate = this.state.depositStore.rate();
    if (this.amountUsdt && this.amountUsdt > 0) {
      this.vndEquivalent = this.amountUsdt * rate;
    } else {
      this.vndEquivalent = 0;
    }
  }

  // Xác nhận tạo lệnh nạp tiền
  public onCreateInvoice() {
    if (this.isSubmitting()) return;

    if (this.amountUsdt === null || this.amountUsdt === undefined || this.amountUsdt < 0.1 || this.amountUsdt > 100000) {
      if (this.amountUsdt !== null && this.amountUsdt !== undefined && this.amountUsdt > 100000) {
        this.state.showToast('Số lượng USDT nạp tối đa là 100,000 USDT!', 'error');
      } else {
        this.state.showToast('Số lượng USDT nạp tối thiểu là 0.1 USDT!', 'error');
      }
      return;
    }

    this.isSubmitting.set(true);

    this.state.depositStore.createDeposit(
      this.amountUsdt, 
      this.paymentMethod
    ).subscribe({
      next: (res) => {
        this.state.showToast('Tạo lệnh nạp tiền thành công! Đang chuyển đến hóa đơn thanh toán.', 'success');
        setTimeout(() => {
          this.isSubmitting.set(false);
          this.router.navigate(['/deposit/invoice', res.invoice.id]);
        }, 800);
      },
      error: (err) => {
        this.isSubmitting.set(false);
        const rawMsg = err?.error?.message;
        let msg = typeof rawMsg === 'string' ? rawMsg : 'Có lỗi xảy ra khi tạo lệnh nạp tiền!';

        if (msg.toLowerCase().includes('at least 0.1')) {
          msg = 'Số lượng USDT nạp tối thiểu là 0.1 USDT!';
          this.state.showToast(msg, 'error');
        } else if (msg.toLowerCase().includes('greater than 100000') || msg.includes('100000')) {
          msg = 'Số lượng USDT nạp tối đa là 100,000 USDT!';
          this.state.showToast(msg, 'error');
        } else if (msg.includes('configured the receiving USDT wallet')) {
          msg = 'Quản trị viên hệ thống chưa cấu hình địa chỉ ví nhận USDT nạp tiền! Vui lòng quay lại sau.';
          this.state.showToast(msg, 'error');
        } else if (msg.includes('configured the scanner API key')) {
          msg = 'Hệ thống đối soát tự động chưa được cấu hình Scanner API Key! Vui lòng quay lại sau.';
          this.state.showToast(msg, 'error');
        } else if (msg.includes('already have an active deposit order')) {
          const activeInvoiceId = err?.error?.active_invoice_id;

          this.modalService.confirm({
            title: 'Lệnh nạp tiền chưa hoàn tất',
            description: 'Bạn đang có một lệnh nạp tiền chưa thanh toán. Vui lòng kiểm tra hoặc hủy lệnh nạp cũ trước khi tạo lệnh mới',
            confirmText: 'Xem lệnh nạp tiền',
            cancelText: 'Đóng modal',
            confirmButtonClass: 'btn-primary',
            size: 'md'
          }).afterClosed$.subscribe((confirmed) => {
            if (confirmed) {
              if (activeInvoiceId) {
                this.router.navigate(['/deposit/invoice', activeInvoiceId]);
              } else {
                this.router.navigate(['/deposit/history']);
              }
            }
          });
        } else {
          this.state.showToast(msg, 'error');
        }
      }
    });
  }
}
