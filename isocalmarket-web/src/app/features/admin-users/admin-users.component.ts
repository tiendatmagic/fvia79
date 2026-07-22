import { Component, OnInit, inject, signal, computed } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { StateService } from '@core/services/state.service';
import { LanguageService } from '@core/services/language.service';
import { ButtonComponent } from '@shared/components/button/button.component';
import { BadgeComponent } from '@shared/components/badge/badge.component';
import { CardComponent } from '@shared/components/card/card.component';
import { PageHeaderComponent } from '@shared/components/page-header/page-header.component';
import { IconComponent } from '@shared/components/icon/icon.component';
import { CustomSearchInputComponent } from '@shared/components/custom-search-input/custom-search-input.component';
import { TooltipDirective } from '@shared/components/tooltip/tooltip.directive';
import { ModalService } from '@core/services/modal.service';
import { PaginationComponent } from '@shared/components/pagination/pagination.component';
import { UserFormModalComponent } from './user-form-modal/user-form-modal.component';
import { AdjustBalanceModalComponent } from './adjust-balance-modal/adjust-balance-modal.component';
import { CustomSelectComponent } from '@shared/components/custom-select/custom-select.component';
import { CustomCheckboxComponent } from '@shared/components/custom-checkbox/custom-checkbox.component';
import { CustomDateTimeRangeComponent, DateTimeRangeValue } from '@shared/components/custom-date-time-range/custom-date-time-range.component';
import { TableComponent, TableCellDirective, TableColumn } from '@shared/components/table/table.component';
import { TranslatePipe } from '@shared/pipes/translate.pipe';

@Component({
  selector: 'app-admin-users',
  standalone: true,
  imports: [
    CommonModule,
    FormsModule,
    ButtonComponent,
    BadgeComponent,
    CardComponent,
    PageHeaderComponent,
    IconComponent,
    CustomSearchInputComponent,
    TooltipDirective,
    PaginationComponent,
    CustomSelectComponent,
    CustomCheckboxComponent,
    CustomDateTimeRangeComponent,
    TableComponent,
    TableCellDirective,
    TranslatePipe
  ],
  templateUrl: './admin-users.component.html',
  styles: [`
    :host {
      display: block;
    }
  `]
})
export class AdminUsersComponent implements OnInit {
  public readonly state = inject(StateService);
  private readonly modalService = inject(ModalService);
  public readonly lang = inject(LanguageService);

  public readonly selectedUserIds = signal<Set<string>>(new Set<string>());

  public get currentAdminId(): string | null {
    return this.state.authStore.currentUser()?.id || null;
  }

  public get selectedActiveUsers(): any[] {
    const selectedIds = this.selectedUserIds();
    return this.state.userStore.users().filter(u => selectedIds.has(u.id) && u.status === 'active' && u.id !== this.currentAdminId);
  }

  public get selectedBannedUsers(): any[] {
    const selectedIds = this.selectedUserIds();
    return this.state.userStore.users().filter(u => selectedIds.has(u.id) && u.status === 'banned' && u.id !== this.currentAdminId);
  }

  public isSelectedUser(id: string): boolean {
    return this.selectedUserIds().has(id);
  }

  public toggleSelectUser(id: string) {
    if (id === this.currentAdminId) return;
    const current = new Set(this.selectedUserIds());
    if (current.has(id)) {
      current.delete(id);
    } else {
      current.add(id);
    }
    this.selectedUserIds.set(current);
  }

  public isAllUsersSelected(): boolean {
    const validUsers = this.state.userStore.users().filter(u => u.id !== this.currentAdminId);
    if (validUsers.length === 0) return false;
    const selectedIds = this.selectedUserIds();
    return validUsers.every(u => selectedIds.has(u.id));
  }

  public toggleSelectAllUsers() {
    const validUsers = this.state.userStore.users().filter(u => u.id !== this.currentAdminId);
    if (this.isAllUsersSelected()) {
      this.selectedUserIds.set(new Set<string>());
    } else {
      const allIds = new Set<string>(validUsers.map(u => u.id));
      this.selectedUserIds.set(allIds);
    }
  }

  public clearSelection() {
    this.selectedUserIds.set(new Set<string>());
  }

  public readonly columns = computed<TableColumn[]>(() => {
    this.lang.currentLang();
    return [
      { key: 'select', label: '', sortable: false, width: '40px' },
      { key: 'id', label: this.lang.translate('common.id'), sortable: true },
      { key: 'name', label: this.lang.translate('usr.name'), sortable: true },
      { key: 'email', label: this.lang.translate('usr.contact'), sortable: true },
      { key: 'role', label: this.lang.translate('usr.role'), sortable: true, align: 'center' },
      { key: 'balance', label: this.lang.translate('nav.balance'), sortable: true, align: 'right' },
      { key: 'api_key', label: this.lang.translate('api.b2b_api_key'), sortable: true },
      { key: 'status', label: this.lang.translate('common.status'), sortable: true, align: 'center' },
      { key: 'actions', label: this.lang.translate('common.actions'), sortable: false, align: 'right' }
    ];
  });

  public searchQuery = '';
  private readonly _searchQuery = signal('');

  public readonly visibleApiKeys = new Set<string>();

  public toggleApiKeyVisibility(userId: string) {
    if (this.visibleApiKeys.has(userId)) {
      this.visibleApiKeys.delete(userId);
    } else {
      this.visibleApiKeys.add(userId);
    }
  }

  public readonly selectedRoleFilter = signal<string>('');
  public readonly selectedDateRange = signal<DateTimeRangeValue>({ startDate: '', endDate: '' });
  public get roleOptions() {
    return [
      { label: this.lang.translate('usr.all_roles'), value: '' },
      { label: this.lang.translate('role.admin'), value: 'admin' },
      { label: this.lang.translate('role.customer'), value: 'customer' }
    ];
  }

  public readonly currentPage = signal<number>(1);
  public readonly limit = 10;

  ngOnInit() {
    this.fetchUsers(1);
  }

  public fetchUsers(page: number = 1) {
    this.currentPage.set(page);
    this.clearSelection();
    this.state.userStore.fetchUsers(
      page,
      this.limit,
      this._searchQuery().trim() || undefined,
      this.selectedRoleFilter() || undefined,
      this.selectedDateRange().startDate || undefined,
      this.selectedDateRange().endDate || undefined
    ).subscribe();
  }

  public bulkLockUsers() {
    const activeUsers = this.selectedActiveUsers;
    if (activeUsers.length === 0) return;

    const ids = activeUsers.map(u => u.id);
    const ref = this.modalService.confirm({
      title: this.lang.translate('modal.bulk_lock_title'),
      descriptionHtml: `${this.lang.translate('modal.bulk_lock_desc')} (<strong>${ids.length}</strong> ${this.lang.translate('admin.user_item')})`,
      confirmText: this.lang.translate('modal.bulk_lock_confirm'),
      cancelText: this.lang.translate('common.cancel'),
      variant: 'danger'
    });

    ref.afterClosed$.subscribe((confirmed: any) => {
      if (confirmed) {
        this.state.userStore.bulkUpdateStatus(ids, 'banned').subscribe((res) => {
          if (res !== null) {
            this.clearSelection();
            this.fetchUsers(this.currentPage());
          }
        });
      }
    });
  }

  public bulkUnlockUsers() {
    const bannedUsers = this.selectedBannedUsers;
    if (bannedUsers.length === 0) return;

    const ids = bannedUsers.map(u => u.id);
    const ref = this.modalService.confirm({
      title: this.lang.translate('modal.bulk_unlock_title'),
      descriptionHtml: `${this.lang.translate('modal.bulk_unlock_desc')} (<strong>${ids.length}</strong> ${this.lang.translate('admin.user_item')})`,
      confirmText: this.lang.translate('modal.bulk_unlock_confirm'),
      cancelText: this.lang.translate('common.cancel'),
      variant: 'info'
    });

    ref.afterClosed$.subscribe((confirmed: any) => {
      if (confirmed) {
        this.state.userStore.bulkUpdateStatus(ids, 'active').subscribe((res) => {
          if (res !== null) {
            this.clearSelection();
            this.fetchUsers(this.currentPage());
          }
        });
      }
    });
  }

  public bulkDeleteUsers() {
    const validUsers = this.state.userStore.users().filter(u => this.selectedUserIds().has(u.id) && u.id !== this.currentAdminId);
    if (validUsers.length === 0) return;

    const ids = validUsers.map(u => u.id);
    const ref = this.modalService.confirm({
      title: this.lang.translate('modal.bulk_delete_user_title'),
      descriptionHtml: `${this.lang.translate('modal.bulk_delete_user_desc')} (<strong>${ids.length}</strong> ${this.lang.translate('admin.user_item')})`,
      confirmText: this.lang.translate('modal.bulk_delete_user_confirm'),
      cancelText: this.lang.translate('common.cancel'),
      variant: 'danger'
    });

    ref.afterClosed$.subscribe((confirmed: any) => {
      if (confirmed) {
        this.state.userStore.bulkDeleteUsers(ids).subscribe((res) => {
          if (res !== null) {
            this.clearSelection();
            this.fetchUsers(this.currentPage());
          }
        });
      }
    });
  }

  public fetchPage(page: number) {
    this.fetchUsers(page);
  }

  public onSearch(query: string) {
    this._searchQuery.set(query);
    this.fetchUsers(1);
  }

  public onRefresh() {
    this.searchQuery = '';
    this._searchQuery.set('');
    this.selectedRoleFilter.set('');
    this.selectedDateRange.set({ startDate: '', endDate: '' });
    this.fetchUsers(1);
    this.state.showToast(this.lang.translate('toast.list_refreshed'), 'success');
  }

  public openAddModal() {
    const ref = this.modalService.open(UserFormModalComponent, {
      title: this.lang.translate('modal.add_user_title'),
      size: '3xl',
      closeOnBackdropClick: true
    });

    ref.afterClosed$.subscribe((success: any) => {
      if (success) {
        this.fetchUsers(this.currentPage());
      }
    });
  }

  public openEditModal(user: any) {
    const ref = this.modalService.open(UserFormModalComponent, {
      title: this.lang.translate('modal.edit_user_title'),
      size: '3xl',
      closeOnBackdropClick: true,
      data: user
    });

    ref.afterClosed$.subscribe((success: any) => {
      if (success) {
        this.fetchUsers(this.currentPage());
      }
    });
  }

  public openAdjustBalanceModal(user: any) {
    const ref = this.modalService.open(AdjustBalanceModalComponent, {
      title: this.lang.translate('modal.adjust_balance_title'),
      size: 'md',
      closeOnBackdropClick: true,
      data: { user }
    });

    ref.afterClosed$.subscribe((success: any) => {
      if (success) {
        this.fetchUsers(this.currentPage());
      }
    });
  }

  public toggleUserStatus(user: any) {
    const targetStatus = user.status === 'active' ? 'banned' : 'active';
    const isLocking = targetStatus === 'banned';

    const ref = this.modalService.confirm({
      title: isLocking
        ? this.lang.translate('modal.lock_user_title')
        : this.lang.translate('modal.unlock_user_title'),
      descriptionHtml: isLocking
        ? `${this.lang.translate('modal.lock_user_desc_prefix')} <strong>${user.name || user.username}</strong> (@${user.username})${this.lang.translate('modal.lock_user_desc_suffix')}`
        : `${this.lang.translate('modal.unlock_user_desc_prefix')} <strong>${user.name || user.username}</strong> (@${user.username})${this.lang.translate('modal.unlock_user_desc_suffix')}`,
      confirmText: isLocking
        ? this.lang.translate('modal.lock_user_confirm')
        : this.lang.translate('modal.unlock_user_confirm'),
      cancelText: this.lang.translate('common.cancel'),
      variant: isLocking ? 'danger' : 'info'
    });

    ref.afterClosed$.subscribe((confirmed: any) => {
      if (confirmed) {
        const updateData = {
          username: user.username,
          email: user.email,
          role: user.role,
          name: user.name,
          phone: user.phone,
          status: targetStatus
        };
        this.state.userStore.updateUser(user.id, updateData).subscribe((res) => {
          if (res !== null) {
            this.fetchUsers(this.currentPage());
          }
        });
      }
    });
  }

  public deleteUser(user: any) {
    const ref = this.modalService.confirm({
      title: this.lang.translate('modal.delete_user_title'),
      description: `${this.lang.translate('modal.delete_user_desc')} "${user.name}"?`
    });

    ref.afterClosed$.subscribe((confirmed: any) => {
      if (confirmed) {
        this.state.userStore.deleteUser(user.id).subscribe((res) => {
          if (res !== null) {
            this.fetchUsers(this.currentPage());
          }
        });
      }
    });
  }

  public copyUid(uid: string) {
    if (typeof navigator !== 'undefined' && navigator.clipboard) {
      navigator.clipboard.writeText(uid).then(() => {
        this.state.showToast(this.lang.translate('toast.uid_copied'), 'success');
      });
    }
  }

  public copyApiKey(key: string) {
    if (typeof navigator !== 'undefined' && navigator.clipboard) {
      navigator.clipboard.writeText(key).then(() => {
        this.state.showToast(this.lang.translate('toast.apikey_copied'), 'success');
      });
    }
  }
}
