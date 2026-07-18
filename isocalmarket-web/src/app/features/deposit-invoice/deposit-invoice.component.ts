import { Component, inject, OnInit, OnDestroy, signal } from '@angular/core';
import { CommonModule } from '@angular/common';
import { ActivatedRoute, Router, RouterModule } from '@angular/router';
import { StateService } from '@core/services/state.service';
import { ModalService } from '@core/services/modal.service';
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
  private readonly modalService = inject(ModalService);

  public readonly adminWalletAddress = signal('');

  public invoice: DepositHistoryItem | null = null;

  // Loading & Error states (dùng Signal để đảm bảo Angular change detection)
  public readonly isLoadingInvoice = signal(true);
  public readonly invoiceError = signal<string | null>(null);

  // Bộ đếm ngược (dùng Signal để Angular luôn detect khi interval() tick)
  public readonly timeLeft = signal(900);
  public readonly countdownText = signal('15:00');
  private timerSubscription: Subscription | null = null;
  private pollingSubscription: Subscription | null = null;
  private expirationTime = 0;

  public isCancelling = false;
  public isChecking = false;

  ngOnInit() {
    this.loadInvoice();
  }

  ngOnDestroy() {
    this.stopCountdown();
    this.stopStatusPolling();
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

        // Tính toán đếm ngược thực tế dựa trên thời gian tạo tuyệt đối
        if (this.invoice.createdAt) {
          const createdTime = new Date(this.invoice.createdAt).getTime();
          this.expirationTime = createdTime + (900 * 1000); // 15 phút từ lúc tạo

          const nowTime = Date.now();
          const remaining = Math.max(0, Math.floor((this.expirationTime - nowTime) / 1000));

          if (remaining <= 0) {
            this.timeLeft.set(0);
            setTimeout(() => {
              this.onInvoiceExpired();
            }, 50);
          } else {
            this.timeLeft.set(remaining);
            this.startCountdown();
            this.startStatusPolling(this.invoice.id);
          }
        } else {
          this.expirationTime = Date.now() + (900 * 1000);
          this.startCountdown();
          if (this.invoice.id) {
            this.startStatusPolling(this.invoice.id);
          }
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

  // Bắt đầu đếm ngược 15 phút (tự động hiệu chỉnh theo đồng hồ hệ thống)
  private startCountdown() {
    this.stopCountdown();
    this.updateTimerDisplay();

    this.timerSubscription = interval(1000).subscribe(() => {
      this.updateTimerDisplay();
    });
  }

  private updateTimerDisplay() {
    const nowTime = Date.now();
    const remaining = Math.max(0, Math.floor((this.expirationTime - nowTime) / 1000));
    
    this.timeLeft.set(remaining);
    const mins = Math.floor(remaining / 60);
    const secs = remaining % 60;
    this.countdownText.set(`${mins.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`);

    if (remaining <= 0) {
      this.onInvoiceExpired();
    }
  }

  private stopCountdown() {
    if (this.timerSubscription) {
      this.timerSubscription.unsubscribe();
      this.timerSubscription = null;
    }
  }

  // Polling tự động quét trạng thái hóa đơn ngầm mỗi 10 giây
  private startStatusPolling(invoiceId: string) {
    this.stopStatusPolling();
    this.pollingSubscription = interval(10000).subscribe(() => {
      this.state.depositStore.fetchInvoice(invoiceId, true).subscribe({
        next: (res) => {
          if (res && res.invoice) {
            if (res.invoice.status === 'completed') {
              this.stopCountdown();
              this.stopStatusPolling();
              this.state.showToast('Nạp tiền thành công! Số dư tài khoản đã được cập nhật.', 'success');
              this.state.authStore.checkMe().subscribe();
              this.router.navigate(['/deposit/history']);
            } else if (res.invoice.status === 'failed' || res.invoice.status === 'expired') {
              this.stopCountdown();
              this.stopStatusPolling();
              this.state.showToast('Lệnh nạp tiền đã bị hủy hoặc hết hạn.', 'error');
              this.router.navigate(['/deposit/history']);
            }
          }
        }
      });
    });
  }

  private stopStatusPolling() {
    if (this.pollingSubscription) {
      this.pollingSubscription.unsubscribe();
      this.pollingSubscription = null;
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

    this.modalService.confirm({
      title: 'Xác nhận hủy lệnh nạp tiền',
      descriptionHtml: `
        <div class="space-y-3">
          <p class="text-sm text-slate-600 dark:text-slate-400">Bạn có chắc chắn muốn hủy lệnh nạp tiền này không? Hành động này không thể hoàn tác.</p>
          <div class="p-3 bg-amber-50 dark:bg-amber-950/20 border border-amber-200/50 dark:border-amber-800/40 rounded-xl flex gap-2">
            <svg class="w-5 h-5 text-amber-500 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
            <p class="text-xs text-amber-700 dark:text-amber-400 font-bold leading-normal">
              Lưu ý quan trọng: Nếu bạn đã thực hiện chuyển khoản rồi, vui lòng KHÔNG hủy lệnh nạp này! BOT sẽ không thể tự động đối soát cộng tiền khi lệnh đã bị hủy.
            </p>
          </div>
        </div>
      `,
      confirmText: 'Xác nhận hủy',
      cancelText: 'Quay lại',
      confirmButtonClass: 'btn-danger'
    }).afterClosed$.subscribe((confirmed?: boolean) => {
      if (confirmed) {
        this.isCancelling = true;
        this.state.depositStore.cancelInvoice(this.invoice!.id).pipe(
          finalize(() => this.isCancelling = false)
        ).subscribe({
          next: () => {
            this.stopCountdown();
            this.stopStatusPolling();
            this.state.showToast('Đã hủy lệnh nạp tiền thành công!', 'success');
            this.router.navigate(['/deposit/history']);
          },
          error: (err) => {
            const msg = err?.error?.message || 'Không thể hủy lệnh nạp tiền!';
            this.state.showToast(msg, 'error');
          }
        });
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
