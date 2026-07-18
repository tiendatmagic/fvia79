import { Component, OnInit, inject, signal } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { StateService } from '@core/services/state.service';
import { SkeletonLoaderComponent } from '@shared/components/skeleton-loader/skeleton-loader.component';
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
import { CustomSelectComponent } from '@shared/components/custom-select/custom-select.component';

@Component({
  selector: 'app-admin-users',
  standalone: true,
  imports: [
    CommonModule,
    FormsModule,
    SkeletonLoaderComponent,
    ButtonComponent,
    BadgeComponent,
    CardComponent,
    PageHeaderComponent,
    IconComponent,
    CustomSearchInputComponent,
    TooltipDirective,
    PaginationComponent,
    CustomSelectComponent
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

  public searchQuery = '';
  private readonly _searchQuery = signal('');

  public readonly selectedRoleFilter = signal<string>('');
  public readonly roleOptions = [
    { label: 'Tất cả vai trò', value: '' },
    { label: 'Quản trị viên (Admin)', value: 'admin' },
    { label: 'Người dùng (User)', value: 'user' }
  ];

  public readonly currentPage = signal<number>(1);
  public readonly limit = 10;

  ngOnInit() {
    this.fetchUsers(1);
  }

  public fetchUsers(page: number = 1) {
    this.currentPage.set(page);
    this.state.userStore.fetchUsers(
      page,
      this.limit,
      this._searchQuery().trim() || undefined,
      this.selectedRoleFilter() || undefined
    ).subscribe();
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
    this.fetchUsers(1);
    this.state.showToast('Đã làm mới danh sách!', 'success');
  }

  public openAddModal() {
    const ref = this.modalService.open(UserFormModalComponent, {
      title: 'Thêm thành viên mới',
      size: 'md',
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
      title: 'Sửa thông tin thành viên',
      size: 'md',
      closeOnBackdropClick: true,
      data: user
    });

    ref.afterClosed$.subscribe((success: any) => {
      if (success) {
        this.fetchUsers(this.currentPage());
      }
    });
  }

  public deleteUser(user: any) {
    const ref = this.modalService.confirm({
      title: 'Xác nhận xóa thành viên',
      description: `Bạn có chắc chắn muốn xóa thành viên "${user.name}"? Hành động này không thể hoàn tác!`
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

  public copyApiKey(key: string) {
    if (typeof navigator !== 'undefined' && navigator.clipboard) {
      navigator.clipboard.writeText(key).then(() => {
        this.state.showToast('Đã sao chép API Key!', 'success');
      });
    }
  }
}
