import { Component, inject, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { ModalRef, MODAL_DATA } from '@core/services/modal-ref';
import { StateService } from '@core/services/state.service';
import { CustomInputComponent } from '@shared/components/custom-input/custom-input.component';
import { CustomSelectComponent } from '@shared/components/custom-select/custom-select.component';
import { ButtonComponent } from '@shared/components/button/button.component';
import { finalize } from 'rxjs/operators';

import { ModalService } from '@core/services/modal.service';
import { AdjustBalanceModalComponent } from '../adjust-balance-modal/adjust-balance-modal.component';

@Component({
  selector: 'app-user-form-modal',
  standalone: true,
  imports: [
    CommonModule,
    FormsModule,
    CustomInputComponent,
    CustomSelectComponent,
    ButtonComponent
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
  private readonly modalService = inject(ModalService);

  public isEditMode = false;
  public editingId: any = null;

  public name = '';
  public email = '';
  public phone = '';
  public role = 'customer';
  public balance = 0;
  public password = '';
  public api_key = '';
  public status = 'active';

  public generate_api_key = false;
  public revoke_api_key = false;

  public isSubmitting = false;

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
      this.name = this.modalData.name;
      this.email = this.modalData.email;
      this.phone = this.modalData.phone || '';
      this.role = this.modalData.role;
      this.balance = this.modalData.balance;
      this.api_key = this.modalData.api_key || '';
      this.status = this.modalData.status || 'active';
    }
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

    if (!this.name || !this.email) {
      this.state.showToast('Vui lòng điền Tên và Email hợp lệ!', 'error');
      return;
    }

    if (!this.isEditMode && !this.password) {
      this.state.showToast('Vui lòng điền Mật khẩu cho tài khoản mới!', 'error');
      return;
    }

    const data: any = {
      name: this.name,
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
