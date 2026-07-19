import { Component, inject, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { ModalRef, MODAL_DATA } from '@core/services/modal-ref';
import { StateService } from '@core/services/state.service';
import { ApiService } from '@core/services/api.service';
import { CustomInputComponent } from '@shared/components/custom-input/custom-input.component';
import { CustomSelectComponent } from '@shared/components/custom-select/custom-select.component';
import { ButtonComponent } from '@shared/components/button/button.component';
import { CustomSwitchComponent } from '@shared/components/custom-switch/custom-switch.component';
import { finalize } from 'rxjs/operators';
import { QrCodeModule } from 'ng-qrcode';

import { ModalService } from '@core/services/modal.service';
import { AdjustBalanceModalComponent } from '../adjust-balance-modal/adjust-balance-modal.component';

type TabId = 'info' | 'permissions' | 'security';

@Component({
  selector: 'app-user-form-modal',
  standalone: true,
  imports: [
    CommonModule,
    FormsModule,
    CustomInputComponent,
    CustomSelectComponent,
    ButtonComponent,
    CustomSwitchComponent,
    QrCodeModule
  ],
  templateUrl: './user-form-modal.component.html',
  styles: [`
    :host {
      display: block;
    }
  `]
})
export class UserFormModalComponent implements OnInit {
  private readonly modalRef = inject(ModalRef);
  private readonly modalData = inject(MODAL_DATA);
  public readonly state = inject(StateService);
  private readonly apiService = inject(ApiService);
  private readonly modalService = inject(ModalService);

  public isEditMode = false;
  public editingId: any = null;

  // Tab state
  public activeTab: TabId = 'info';

  // Form fields
  public username = '';
  public name = '';
  public email = '';
  public phone = '';
  public role = 'customer';
  public balance = 0;
  public password = '';
  public api_key = '';
  public status = 'active';
  public google2fa_enabled = false;

  public generate_api_key = false;
  public revoke_api_key = false;

  public isSubmitting = false;
  public isResetting2fa = false;
  public isEnabling2fa = false;
  public isConfirming = false;

  public adminGeneratedSecret = '';
  public adminGeneratedQrUrl = '';

  public roleOptions = [
    { label: 'Khách hàng', value: 'customer' },
    { label: 'Quản trị viên', value: 'admin' }
  ];

  public statusOptions = [
    { label: 'Đang hoạt động', value: 'active' },
    { label: 'Bị khóa', value: 'banned' }
  ];

  ngOnInit() {
    if (this.modalData) {
      this.isEditMode = true;
      this.editingId = this.modalData.id;
      this.username = this.modalData.username || '';
      this.name = this.modalData.name || '';
      this.email = this.modalData.email;
      this.phone = this.modalData.phone || '';
      this.role = this.modalData.role;
      this.balance = this.modalData.balance;
      this.api_key = this.modalData.api_key || '';
      this.status = this.modalData.status || 'active';
      this.google2fa_enabled = this.modalData.google2fa_enabled || false;
    }
  }

  public setTab(tab: TabId) {
    this.activeTab = tab;
  }

  public onGenerateKey() {
    this.generate_api_key = true;
    this.revoke_api_key = false;
    this.api_key = 'Sẽ sinh tự động sau khi lưu...';
  }

  public onRevokeKey() {
    this.revoke_api_key = true;
    this.generate_api_key = false;
    this.api_key = '';
  }

  public onReset2fa() {
    if (this.isResetting2fa || this.isConfirming || !this.isEditMode || !this.editingId) return;

    this.isConfirming = true;
    this.modalService.confirm({
      title: 'Xác nhận tắt 2FA',
      descriptionHtml: `Bạn có chắc muốn tắt xác thực 2 lớp (2FA) cho tài khoản <strong>${this.email}</strong>? User sẽ cần tự bật lại từ trang cài đặt bảo mật của họ.`,
      confirmText: 'Tắt 2FA',
      cancelText: 'Hủy',
      variant: 'danger'
    }).afterClosed$.subscribe((confirmed) => {
      this.isConfirming = false;
      if (!confirmed) {
        this.google2fa_enabled = true;
        return;
      }

      this.isResetting2fa = true;
      this.apiService.post(`v1/admin/users/${this.editingId}/reset-2fa`, {}).pipe(
        finalize(() => this.isResetting2fa = false)
      ).subscribe({
        next: () => {
          this.google2fa_enabled = false;
          this.adminGeneratedSecret = '';
          this.adminGeneratedQrUrl = '';
          this.state.showToast('Đã tắt 2FA cho tài khoản này!', 'success');
        },
        error: () => {
          this.google2fa_enabled = true;
          this.state.showToast('Không thể tắt 2FA, vui lòng thử lại!', 'error');
        }
      });
    });
  }

  public onEnable2fa() {
    if (this.isEnabling2fa || this.isConfirming || !this.isEditMode || !this.editingId) return;

    this.isConfirming = true;
    this.modalService.confirm({
      title: 'Kích hoạt 2FA giùm thành viên',
      descriptionHtml: `Hệ thống sẽ tự động kích hoạt 2FA và sinh khóa bí mật cho tài khoản <strong>${this.email}</strong>. Bạn cần cung cấp khóa bí mật này hoặc gửi ảnh mã QR cho thành viên cấu hình trên ứng dụng Google Authenticator của họ.`,
      confirmText: 'Kích hoạt 2FA',
      cancelText: 'Hủy',
      variant: 'info'
    }).afterClosed$.subscribe((confirmed) => {
      this.isConfirming = false;
      if (!confirmed) {
        this.google2fa_enabled = false;
        return;
      }

      this.isEnabling2fa = true;
      this.apiService.post<any>(`v1/admin/users/${this.editingId}/enable-2fa`, {}).pipe(
        finalize(() => this.isEnabling2fa = false)
      ).subscribe({
        next: (res) => {
          this.google2fa_enabled = true;
          this.adminGeneratedSecret = res.secret;
          this.adminGeneratedQrUrl = res.qr_code_url;
          this.state.showToast('Đã kích hoạt 2FA thành công!', 'success');
        },
        error: () => {
          this.google2fa_enabled = false;
          this.state.showToast('Không thể kích hoạt 2FA, vui lòng thử lại!', 'error');
        }
      });
    });
  }

  public onToggle2Fa(checked: boolean) {
    if (checked) {
      this.onEnable2fa();
    } else {
      this.onReset2fa();
    }
  }

  public openAdjustBalance() {
    if (!this.isEditMode || !this.editingId) return;

    this.modalService.open(AdjustBalanceModalComponent, {
      title: 'Điều chỉnh số dư thành viên',
      size: 'md',
      data: {
        user: {
          id: this.editingId,
          name: this.name,
          email: this.email,
          balance: this.balance
        }
      }
    }).afterClosed$.subscribe((updated?: boolean) => {
      if (updated) {
        this.state.userStore.fetchUsers(this.state.userStore.currentPage(), 10).subscribe(() => {
          const u = this.state.userStore.users().find(x => x.id === this.editingId);
          if (u) {
            this.balance = u.balance;
          }
        });
      }
    });
  }

  public onClose() {
    this.modalRef.close(false);
  }

  public onSubmit() {
    if (this.isSubmitting) return;

    const usernameRegex = /^[a-zA-Z0-9_]{3,50}$/;
    if (!this.username) {
      this.state.showToast('Vui lòng điền Tên đăng nhập!', 'error');
      this.activeTab = 'info';
      return;
    }
    if (!usernameRegex.test(this.username)) {
      this.state.showToast('Tên đăng nhập không đúng định dạng (3-50 ký tự, không dấu, không cách)!', 'error');
      this.activeTab = 'info';
      return;
    }
    if (!this.email) {
      this.state.showToast('Vui lòng điền địa chỉ Email!', 'error');
      this.activeTab = 'info';
      return;
    }

    if (!this.isEditMode) {
      if (!this.password) {
        this.state.showToast('Vui lòng điền Mật khẩu cho tài khoản mới!', 'error');
        this.activeTab = 'info';
        return;
      }
      if (this.password.length < 6) {
        this.state.showToast('Mật khẩu phải có ít nhất 6 ký tự!', 'error');
        this.activeTab = 'info';
        return;
      }
    } else {
      if (this.password && this.password.length < 6) {
        this.state.showToast('Mật khẩu mới phải có ít nhất 6 ký tự!', 'error');
        this.activeTab = 'info';
        return;
      }
    }

    const data: any = {
      username: this.username.toLowerCase(),
      name: this.name || null,
      email: this.email,
      phone: this.phone || null,
      role: this.role,
      balance: this.balance
    };

    if (this.password) {
      data.password = this.password;
    }

    this.isSubmitting = true;

    if (this.isEditMode) {
      data.status = this.status;
      if (this.generate_api_key) {
        data.generate_api_key = true;
      } else if (this.revoke_api_key) {
        data.revoke_api_key = true;
      }

      this.state.userStore.updateUser(this.editingId, data).pipe(
        finalize(() => this.isSubmitting = false)
      ).subscribe((res) => {
        if (res !== null) {
          this.modalRef.close(true);
        }
      });
    } else {
      this.state.userStore.createUser(data).pipe(
        finalize(() => this.isSubmitting = false)
      ).subscribe((res) => {
        if (res !== null) {
          this.modalRef.close(true);
        }
      });
    }
  }
}
