import { Component, inject, OnInit, OnDestroy, signal } from '@angular/core';
import { CommonModule } from '@angular/common';
import { ActivatedRoute, Router, RouterModule } from '@angular/router';
import { StateService } from '@core/services/state.service';
import { ButtonComponent } from '@shared/components/button/button.component';
import { CardComponent } from '@shared/components/card/card.component';
import { IconComponent } from '@shared/components/icon/icon.component';
import { QrCodeModule } from 'ng-qrcode';
import { DepositHistoryItem } from '../deposit-history/deposit-history.component';
import { Subscription, interval } from 'rxjs';
import { finalize } from 'rxjs/operators';

@Component({
  selector: 'app-deposit-invoice',
  standalone: true,
  imports: [
    CommonModule,
    RouterModule,
    ButtonComponent,
    CardComponent,
    IconComponent,
    QrCodeModule
  ],
  templateUrl: './deposit-invoice.component.html',
  styles: [`
    :host {
      display: block;
    }
  `]
})
export class DepositInvoiceComponent implements OnInit, OnDestroy {
  private readonly route = inject(ActivatedRoute);
  private readonly router = inject(Router);
  public readonly state = inject(StateService);

  public readonly adminWalletAddress = signal('');

  public invoice: DepositHistoryItem | null = null;

  // Loading & Error states (dùng Signal để đảm bảo Angular change detection)
  public readonly isLoadingInvoice = signal(true);
  public readonly invoiceError = signal<string | null>(null);

  // Bộ đếm ngược (dùng Signal để Angular luôn detect khi interval() tick)
  public readonly timeLeft = signal(900);
  public readonly countdownText = signal('15:00');
  private timerSubscription: Subscription | null = null;

  public isCancelling = false;
  public isChecking = false;

  ngOnInit() {
    this.loadInvoice();
  }

  ngOnDestroy() {
    this.stopCountdown();
  }

  // Tải chi tiết hóa đơn từ Backend API
  private loadInvoice() {
    const invoiceId = this.route.snapshot.paramMap.get('id');
    if (!invoiceId) {
      this.invoiceError.set('Không tìm thấy mã hóa đơn trong URL!');
      this.isLoadingInvoice.set(false);
      return;
    }

    this.isLoadingInvoice.set(true);
    this.invoiceError.set(null);

    this.state.depositStore.fetchInvoice(invoiceId).subscribe({
      next: (res) => {
        this.isLoadingInvoice.set(false);
        this.invoice = res.invoice;

        const wallet = res.invoice?.adminWalletAddress || '';
        if (!wallet.trim()) {
          this.invoiceError.set('Quản trị viên hệ thống chưa cấu hình địa chỉ ví nhận USDT. Vui lòng liên hệ hỗ trợ!');
          return;
        }
        this.adminWalletAddress.set(wallet);
        
        if (this.invoice.status !== 'pending_payment') {
          this.state.showToast('Lệnh nạp tiền này đã được xử lý hoặc đã hết hạn!', 'warning');
          setTimeout(() => {
            this.router.navigate(['/deposit/history']);
          }, 50);
          return;
        }

        // Tính toán đếm ngược thực tế dựa trên thời gian tạo
        if (this.invoice.createdAt) {
          const createdTime = new Date(this.invoice.createdAt).getTime();
          const nowTime = Date.now();
          const diffSeconds = Math.floor((nowTime - createdTime) / 1000);
          const remaining = 900 - diffSeconds;

          if (remaining <= 0) {
            this.timeLeft.set(0);
            setTimeout(() => {
              this.onInvoiceExpired();
            }, 50);
          } else {
            this.timeLeft.set(remaining);
            this.startCountdown();
          }
        } else {
          this.startCountdown();
        }
      },
      error: (err) => {
        this.isLoadingInvoice.set(false);
        const status = err?.status;
        const errMsg = err?.error?.message || '';
        
        if (errMsg.includes('configured the receiving USDT wallet')) {
          this.invoiceError.set('Quản trị viên hệ thống chưa cấu hình địa chỉ ví nhận USDT nạp tiền. Vui lòng liên hệ Admin!');
        } else if (errMsg.includes('configured the scanner API key')) {
          this.invoiceError.set('Hệ thống đối soát tự động chưa được cấu hình Scanner API Key. Vui lòng liên hệ Admin!');
        } else if (status === 404) {
          this.invoiceError.set('Lệnh nạp tiền không tồn tại hoặc bạn không có quyền xem!');
        } else if (status === 401 || status === 403) {
          this.invoiceError.set('Bạn cần đăng nhập để xem hóa đơn nạp tiền!');
        } else {
          this.invoiceError.set('Không thể tải chi tiết hóa đơn. Vui lòng thử lại!');
        }
      }
    });
  }

  // Bắt đầu đếm ngược 15 phút
  private startCountdown() {
    this.stopCountdown();
    const t = this.timeLeft();
    const minutes = Math.floor(t / 60);
    const seconds = t % 60;
    this.countdownText.set(`${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`);

    this.timerSubscription = interval(1000).subscribe(() => {
      const current = this.timeLeft();
      if (current > 0) {
        const next = current - 1;
        this.timeLeft.set(next);
        const mins = Math.floor(next / 60);
        const secs = next % 60;
        this.countdownText.set(`${mins.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`);
      } else {
        this.onInvoiceExpired();
      }
    });
  }

  private stopCountdown() {
    if (this.timerSubscription) {
      this.timerSubscription.unsubscribe();
      this.timerSubscription = null;
    }
  }

  // Khi hóa đơn hết thời gian đếm ngược
  private onInvoiceExpired() {
    this.stopCountdown();
    if (this.invoice && !this.isCancelling) {
      this.isCancelling = true;
      this.state.depositStore.cancelInvoice(this.invoice.id).pipe(
        finalize(() => this.isCancelling = false)
      ).subscribe({
        next: () => {
          this.state.showToast('Lệnh nạp tiền đã hết thời gian thanh toán và tự động hủy!', 'error');
          setTimeout(() => {
            this.router.navigate(['/deposit/history']);
          }, 50);
        },
        error: () => {
          setTimeout(() => {
            this.router.navigate(['/deposit/history']);
          }, 50);
        }
      });
    }
  }

  // Sao chép văn bản (ví hoặc số tiền)
  public copyText(text: string, label: string) {
    navigator.clipboard.writeText(text).then(() => {
      this.state.showToast(`Đã sao chép ${label}!`, 'success');
    }).catch(() => {
      this.state.showToast(`Lỗi khi sao chép ${label}!`, 'error');
    });
  }

  // Hủy lệnh nạp tiền chủ động
  public cancelInvoice() {
    if (!this.invoice || this.isCancelling) return;

    this.isCancelling = true;
    this.state.depositStore.cancelInvoice(this.invoice.id).pipe(
      finalize(() => this.isCancelling = false)
    ).subscribe({
      next: () => {
        this.stopCountdown();
        this.state.showToast('Đã hủy lệnh nạp tiền thành công!', 'success');
        this.router.navigate(['/deposit/history']);
      },
      error: (err) => {
        const msg = err?.error?.message || 'Không thể hủy lệnh nạp tiền!';
        this.state.showToast(msg, 'error');
      }
    });
  }

  // Kiểm tra giao dịch tự động bằng BOT quét blockchain
  public checkTransaction() {
    if (this.isChecking || !this.invoice) return;
    this.isChecking = true;

    this.state.showToast('Hệ thống BOT đang quét giao dịch tự động trên chuỗi BNB Smart Chain...', 'warning');

    this.state.depositStore.checkTransaction(this.invoice.id).pipe(
      finalize(() => this.isChecking = false)
    ).subscribe({
      next: (res) => {
        if (res.success) {
          this.stopCountdown();
          this.state.showToast('Nạp tiền thành công! Số dư tài khoản đã được cập nhật.', 'success');
          this.state.authStore.checkMe().subscribe();
          this.router.navigate(['/deposit/history']);
        } else {
          this.state.showToast(res.message, 'error');
        }
      },
      error: (err) => {
        let msg = err?.error?.message || 'BOT chưa phát hiện giao dịch USDT hợp lệ của bạn!';
        if (msg.includes('configured the receiving USDT wallet')) {
          msg = 'Hệ thống đối soát bị lỗi do Quản trị viên chưa cấu hình địa chỉ ví nhận tiền!';
        } else if (msg.includes('configured the scanner API key')) {
          msg = 'Hệ thống đối soát bị lỗi do Quản trị viên chưa cấu hình Scanner API Key!';
        }
        this.state.showToast(msg, 'error');
      }
    });
  }
}
