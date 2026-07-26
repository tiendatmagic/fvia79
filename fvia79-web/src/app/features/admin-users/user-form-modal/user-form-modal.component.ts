import { Component, inject, OnInit, ChangeDetectorRef, signal } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { ModalRef, MODAL_DATA } from '@core/services/modal-ref';
import { StateService } from '@core/services/state.service';
import { ApiService } from '@core/services/api.service';
import { LanguageService } from '@core/services/language.service';
import { CustomInputComponent } from '@shared/components/custom-input/custom-input.component';
import { CustomSelectComponent } from '@shared/components/custom-select/custom-select.component';
import { ButtonComponent } from '@shared/components/button/button.component';
import { CustomSwitchComponent } from '@shared/components/custom-switch/custom-switch.component';
import { TooltipDirective } from '@shared/components/tooltip/tooltip.directive';
import { IconComponent } from '@shared/components/icon/icon.component';
import { TranslatePipe } from '@shared/pipes/translate.pipe';
import { finalize } from 'rxjs/operators';
import { QrCodeModule } from 'ng-qrcode';
import { ModalService } from '@core/services/modal.service';
import { AdjustBalanceModalComponent } from '../adjust-balance-modal/adjust-balance-modal.component';
import { AdjustCumulativeDepositModalComponent } from '../adjust-cumulative-deposit-modal/adjust-cumulative-deposit-modal.component';



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
    TooltipDirective,
    IconComponent,
    QrCodeModule,
    TranslatePipe
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
  private readonly cdr = inject(ChangeDetectorRef);
  private readonly modalService = inject(ModalService);
  public readonly lang = inject(LanguageService);

  public readonly activeTabSignal = signal<TabId>('info');
  public get activeTab() { return this.activeTabSignal(); }
  public set activeTab(v: TabId) { this.activeTabSignal.set(v); }

  public isEditMode = false;
  public editingId: any = null;

  public username = '';
  public name = '';
  public email = '';
  public phone = '';
  public role = 'customer';
  public balance = 0;
  public total_deposited = 0;
  public membership_tier: any = null;
  public password = '';
  public api_key = '';
  public status = 'active';
  public google2fa_enabled = false;
  public google2fa_secret = '';
  public google2fa_qr_url = '';
  public isSecretVisible = false;

  public generate_api_key = false;
  public revoke_api_key = false;

  public readonly isSubmitting = signal(false);
  public isResetting2fa = false;
  public isEnabling2fa = false;
  public isConfirming = false;

  public adminGeneratedSecret = '';
  public adminGeneratedQrUrl = '';

  public get roleOptions() {
    return [
      { label: this.lang.translate('role.customer'), value: 'customer' },
      { label: this.lang.translate('role.admin'), value: 'admin' }
    ];
  }

  public get statusOptions() {
    return [
      { label: this.lang.translate('status.active'), value: 'active' },
      { label: this.lang.translate('status.blocked'), value: 'banned' }
    ];
  }

  public get autoApiKeyPlaceholder(): string {
    return this.lang.translate('user.auto_api_key');
  }

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
      this.total_deposited = this.modalData.total_deposited || 0;
      this.membership_tier = this.modalData.membership_tier || null;
      this.api_key = this.modalData.api_key || '';
      this.status = this.modalData.status || 'active';
      this.google2fa_enabled = this.modalData.google2fa_enabled || false;
      this.google2fa_secret = this.modalData.google2fa_secret || '';
      if (this.google2fa_enabled && this.google2fa_secret) {
        const issuer = 'Fvia79';
        this.google2fa_qr_url = `otpauth://totp/${encodeURIComponent(issuer)}:${encodeURIComponent(this.email)}?secret=${this.google2fa_secret}&issuer=${encodeURIComponent(issuer)}`;
      }
    }
  }

  public setTab(tab: TabId) {
    this.activeTab = tab;
  }

  public onGenerateKey() {
    this.generate_api_key = true;
    this.revoke_api_key = false;
    this.api_key = this.autoApiKeyPlaceholder;
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
      title: this.lang.translate('modal.disable_2fa_confirm_title'),
      descriptionHtml: `${this.lang.translate('modal.disable_2fa_confirm_desc_prefix')} <strong>${this.email}</strong>? ${this.lang.translate('modal.disable_2fa_confirm_desc_suffix')}`,
      confirmText: this.lang.translate('modal.disable_2fa_confirm_btn'),
      cancelText: this.lang.translate('common.cancel'),
      variant: 'danger'
    }).afterClosed$.subscribe((confirmed) => {
      this.isConfirming = false;
      if (!confirmed) {
        this.google2fa_enabled = true;
        this.cdr.detectChanges();
        return;
      }

      this.isResetting2fa = true;
      this.cdr.detectChanges();

      const url = `v1/admin/users/${this.editingId}/reset-2fa`;
      this.apiService.post<any>(url, {}).subscribe({
        next: (res) => {
          this.isResetting2fa = false;
          this.google2fa_enabled = false;
          this.google2fa_secret = '';
          this.google2fa_qr_url = '';
          this.state.showToast(this.lang.translate('toast.2fa_disabled'), 'success');
          this.cdr.detectChanges();
        },
        error: () => {
          this.isResetting2fa = false;
          this.google2fa_enabled = true;
          this.state.showToast(this.lang.translate('toast.2fa_disable_failed'), 'error');
          this.cdr.detectChanges();
        }
      });
    });
  }

  public onEnable2fa() {
    if (this.isEnabling2fa || this.isConfirming || !this.isEditMode || !this.editingId) return;

    this.isConfirming = true;
    this.modalService.confirm({
      title: this.lang.translate('modal.enable_2fa_title'),
      descriptionHtml: `${this.lang.translate('modal.enable_2fa_desc_prefix')} <strong>${this.email}</strong>? ${this.lang.translate('modal.enable_2fa_desc_suffix')}`,
      confirmText: this.lang.translate('modal.enable_2fa_confirm'),
      cancelText: this.lang.translate('common.cancel'),
      variant: 'info'
    }).afterClosed$.subscribe((confirmed) => {
      this.isConfirming = false;
      if (!confirmed) {
        this.google2fa_enabled = false;
        this.cdr.detectChanges();
        return;
      }

      this.isEnabling2fa = true;
      this.cdr.detectChanges();

      const url = `v1/admin/users/${this.editingId}/enable-2fa`;
      this.apiService.post<any>(url, {}).subscribe({
        next: (res) => {
          this.isEnabling2fa = false;
          this.google2fa_enabled = true;
          this.google2fa_secret = res.secret;
          this.google2fa_qr_url = res.qr_code_url;
          this.state.showToast(this.lang.translate('toast.2fa_enabled'), 'success');
          this.cdr.detectChanges();
        },
        error: () => {
          this.isEnabling2fa = false;
          this.google2fa_enabled = false;
          this.state.showToast(this.lang.translate('toast.2fa_enable_failed'), 'error');
          this.cdr.detectChanges();
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

  public onSwitchClick() {
    this.onToggle2Fa(!this.google2fa_enabled);
  }

  public onStatusSwitchClick() {
    if (this.isConfirming) return;

    const targetStatus = this.status === 'active' ? 'banned' : 'active';
    const isLocking = targetStatus === 'banned';

    this.isConfirming = true;
    this.modalService.confirm({
      title: isLocking
        ? this.lang.translate('modal.lock_user_title')
        : this.lang.translate('modal.unlock_user_title'),
      descriptionHtml: isLocking
        ? `${this.lang.translate('modal.lock_user_desc_prefix')} <strong>${this.email || this.username}</strong>${this.lang.translate('modal.lock_user_desc_suffix')}`
        : `${this.lang.translate('modal.unlock_user_desc_prefix')} <strong>${this.email || this.username}</strong>${this.lang.translate('modal.unlock_user_desc_suffix')}`,
      confirmText: isLocking
        ? this.lang.translate('modal.lock_user_confirm')
        : this.lang.translate('modal.unlock_user_confirm'),
      cancelText: this.lang.translate('common.cancel'),
      variant: isLocking ? 'danger' : 'info'
    }).afterClosed$.subscribe((confirmed) => {
      this.isConfirming = false;
      if (confirmed) {
        this.status = targetStatus;
        this.cdr.detectChanges();
      }
    });
  }

  public toggleSecretVisibility() {
    this.isSecretVisible = !this.isSecretVisible;
  }

  public copyToClipboard(text: string) {
    if (typeof navigator !== 'undefined' && navigator.clipboard) {
      navigator.clipboard.writeText(text).then(() => {
        this.state.showToast(this.lang.translate('toast.copied_secret'), 'success');
      });
    }
  }

  public openAdjustBalance() {
    if (!this.isEditMode || !this.editingId) return;

    this.modalService.open(AdjustBalanceModalComponent, {
      title: this.lang.translate('modal.adjust_balance_title'),
      size: 'md',
      data: {
        user: {
          id: this.editingId,
          name: this.name,
          email: this.email,
          balance: this.balance,
          total_deposited: this.total_deposited,
          membership_tier: this.membership_tier
        }
      }
    }).afterClosed$.subscribe((updated?: boolean) => {
      if (updated) {
        this.state.userStore.fetchUsers(this.state.userStore.currentPage(), 10).subscribe(() => {
          const u = this.state.userStore.users().find(x => x.id === this.editingId);
          if (u) {
            this.balance = u.balance;
            this.total_deposited = u.total_deposited || 0;
            this.membership_tier = u.membership_tier || null;
            this.cdr.detectChanges();
          }
        });
      }
    });
  }

  public openAdjustCumulativeDeposit() {
    if (!this.isEditMode || !this.editingId) return;

    this.modalService.open(AdjustCumulativeDepositModalComponent, {
      title: this.lang.translate('admin.adjust_cumulative_title'),
      size: 'md',
      data: {
        user: {
          id: this.editingId,
          name: this.name,
          email: this.email,
          total_deposited: this.total_deposited,
          membership_tier: this.membership_tier
        }
      }
    }).afterClosed$.subscribe((updated?: boolean) => {
      if (updated) {
        this.state.userStore.fetchUsers(this.state.userStore.currentPage(), 10).subscribe(() => {
          const u = this.state.userStore.users().find(x => x.id === this.editingId);
          if (u) {
            this.total_deposited = u.total_deposited || 0;
            this.membership_tier = u.membership_tier || null;
            this.cdr.detectChanges();
          }
        });
      }
    });
  }

  public onClose() {
    this.modalRef.close(false);
  }

  public onSubmit() {
    if (this.isSubmitting()) return;

    const usernameRegex = /^[a-zA-Z0-9_]{3,50}$/;
    if (!this.username) {
      this.state.showToast(this.lang.translate('toast.username_required'), 'error');
      this.activeTab = 'info';
      return;
    }
    if (!usernameRegex.test(this.username)) {
      this.state.showToast(this.lang.translate('toast.username_invalid_format'), 'error');
      this.activeTab = 'info';
      return;
    }
    if (!this.email) {
      this.state.showToast(this.lang.translate('toast.email_required'), 'error');
      this.activeTab = 'info';
      return;
    }

    if (!this.isEditMode) {
      if (!this.password) {
        this.state.showToast(this.lang.translate('toast.password_required'), 'error');
        this.activeTab = 'info';
        return;
      }
      if (this.password.length < 6) {
        this.state.showToast(this.lang.translate('toast.password_too_short'), 'error');
        this.activeTab = 'info';
        return;
      }
    } else {
      if (this.password && this.password.length < 6) {
        this.state.showToast(this.lang.translate('toast.password_too_short'), 'error');
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

    this.isSubmitting.set(true);

    if (this.isEditMode) {
      data.status = this.status;
      if (this.generate_api_key) {
        data.generate_api_key = true;
      } else if (this.revoke_api_key) {
        data.revoke_api_key = true;
      }

      this.state.userStore.updateUser(this.editingId, data).pipe(
        finalize(() => this.isSubmitting.set(false))
      ).subscribe((res) => {
        if (res !== null) {
          this.modalRef.close(true);
        }
      });
    } else {
      this.state.userStore.createUser(data).pipe(
        finalize(() => this.isSubmitting.set(false))
      ).subscribe((res) => {
        if (res !== null) {
          this.modalRef.close(true);
        }
      });
    }
  }
}
