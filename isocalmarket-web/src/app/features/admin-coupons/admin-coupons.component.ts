import { Component, OnInit, inject, signal, computed } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { ButtonComponent } from '@shared/components/button/button.component';
import { CardComponent } from '@shared/components/card/card.component';
import { PageHeaderComponent } from '@shared/components/page-header/page-header.component';
import { IconComponent } from '@shared/components/icon/icon.component';
import { CustomSearchInputComponent } from '@shared/components/custom-search-input/custom-search-input.component';
import { CustomSelectComponent } from '@shared/components/custom-select/custom-select.component';
import { TooltipDirective } from '@shared/components/tooltip/tooltip.directive';
import { ModalService } from '@core/services/modal.service';
import { ToastService } from '@core/services/toast.service';
import { CouponFormModalComponent } from './coupon-form-modal/coupon-form-modal.component';
import { PaginationComponent } from '@shared/components/pagination/pagination.component';
import { BadgeComponent } from '@shared/components/badge/badge.component';
import { TableComponent, TableCellDirective, TableColumn } from '@shared/components/table/table.component';
import { CouponStore } from './coupon.store';

@Component({
  selector: 'app-admin-coupons',
  standalone: true,
  imports: [
    CommonModule,
    FormsModule,
    ButtonComponent,
    CardComponent,
    PageHeaderComponent,
    IconComponent,
    CustomSearchInputComponent,
    CustomSelectComponent,
    TooltipDirective,
    PaginationComponent,
    BadgeComponent,
    TableComponent,
    TableCellDirective,
  ],
  templateUrl: './admin-coupons.component.html',
  styles: [`
    :host {
      display: block;
    }
  `]
})
export class AdminCouponsComponent implements OnInit {
  public readonly couponStore = inject(CouponStore);
  private readonly modalService = inject(ModalService);
  private readonly toast = inject(ToastService);

  public readonly columns: TableColumn[] = [
    { key: 'code', label: 'Mã giảm giá', sortable: false },
    { key: 'type', label: 'Loại giảm giá', sortable: false },
    { key: 'value', label: 'Mức giảm', sortable: false, align: 'right' },
    { key: 'min_spend', label: 'Đơn tối thiểu', sortable: false, align: 'right' },
    { key: 'usage', label: 'Lượt sử dụng', sortable: false, align: 'center' },
    { key: 'date_range', label: 'Thời gian áp dụng', sortable: false },
    { key: 'status', label: 'Trạng thái', sortable: false, align: 'center' },
    { key: 'actions', label: 'Thao tác', sortable: false, align: 'right' }
  ];

  public searchQuery = '';
  private readonly _searchQuery = signal('');

  public readonly statusFilter = signal<string>('all');
  public readonly typeFilter = signal<string>('all');
  public readonly sortFilter = signal<string>('newest');

  public readonly currentPage = signal<number>(1);
  public readonly limit = 10;

  public readonly statusOptions = [
    { label: 'Tất cả trạng thái', value: 'all' },
    { label: 'Đang diễn ra', value: 'active' },
    { label: 'Sắp diễn ra', value: 'upcoming' },
    { label: 'Hết hạn / Hết lượt', value: 'expired' }
  ];

  public readonly typeOptions = [
    { label: 'Tất cả loại', value: 'all' },
    { label: 'Phần trăm (%)', value: 'percentage' },
    { label: 'Cố định (VND)', value: 'fixed' }
  ];

  public readonly sortOptions = [
    { label: 'Mới nhất', value: 'newest' },
    { label: 'Cũ nhất', value: 'oldest' },
    { label: 'Giá trị giảm cao nhất', value: 'value_desc' },
    { label: 'Lượt dùng nhiều nhất', value: 'usage_desc' }
  ];

  public readonly filteredCoupons = computed(() => {
    let list = [...this.couponStore.coupons()];
    const status = this.statusFilter();
    const type = this.typeFilter();
    const sort = this.sortFilter();
    const now = new Date();

    if (status !== 'all') {
      list = list.filter(c => {
        const start = new Date(c.start_date);
        const end = new Date(c.end_date);
        end.setHours(23, 59, 59, 999);
        const isUsedUp = c.usage_limit > 0 && c.used_count >= c.usage_limit;
        const isExpired = now > end || isUsedUp;
        const isUpcoming = now < start;
        const isActive = now >= start && now <= end && !isUsedUp;

        if (status === 'active') return isActive;
        if (status === 'upcoming') return isUpcoming;
        if (status === 'expired') return isExpired;
        return true;
      });
    }

    if (type !== 'all') {
      list = list.filter(c => c.type === type);
    }

    if (sort === 'newest') {
      list.sort((a, b) => new Date(b.created_at || b.start_date).getTime() - new Date(a.created_at || a.start_date).getTime());
    } else if (sort === 'oldest') {
      list.sort((a, b) => new Date(a.created_at || a.start_date).getTime() - new Date(b.created_at || b.start_date).getTime());
    } else if (sort === 'value_desc') {
      list.sort((a, b) => Number(b.value) - Number(a.value));
    } else if (sort === 'usage_desc') {
      list.sort((a, b) => Number(b.used_count) - Number(a.used_count));
    }

    return list;
  });

  ngOnInit() {
    this.fetchCoupons();
  }

  public fetchCoupons() {
    this.couponStore.fetchCoupons(
      this.currentPage(),
      this.limit,
      this._searchQuery().trim() || undefined
    ).subscribe();
  }

  public fetchPage(page: number) {
    this.currentPage.set(page);
    this.fetchCoupons();
  }

  public onSearch(query: string) {
    this._searchQuery.set(query);
    this.currentPage.set(1);
    this.fetchCoupons();
  }

  public getCouponStatus(coupon: any): { label: string; variant: 'success' | 'info' | 'danger' | 'warning' } {
    const now = new Date();
    const start = new Date(coupon.start_date);
    const end = new Date(coupon.end_date);
    end.setHours(23, 59, 59, 999);

    const isUsedUp = coupon.usage_limit > 0 && coupon.used_count >= coupon.usage_limit;

    if (isUsedUp) {
      return { label: 'Hết lượt dùng', variant: 'danger' };
    }
    if (now > end) {
      return { label: 'Đã hết hạn', variant: 'danger' };
    }
    if (now < start) {
      return { label: 'Sắp diễn ra', variant: 'info' };
    }
    return { label: 'Đang diễn ra', variant: 'success' };
  }

  public copyToClipboard(text: string, message: string = 'Đã sao chép mã giảm giá!') {
    if (navigator.clipboard && text) {
      navigator.clipboard.writeText(text).then(() => {
        this.toast.showToast(message, 'success');
      }).catch(() => {
        this.toast.showToast('Không thể sao chép!', 'error');
      });
    }
  }

  public openCreateModal() {
    this.modalService.open(CouponFormModalComponent, {
      title: 'Thêm Mã Giảm Giá Mới',
      size: 'lg',
      data: null
    }).afterClosed$.subscribe((success) => {
      if (success) {
        this.fetchCoupons();
      }
    });
  }

  public openEditModal(coupon: any) {
    this.modalService.open(CouponFormModalComponent, {
      title: 'Cập Nhật Mã Giảm Giá',
      size: 'lg',
      data: coupon
    }).afterClosed$.subscribe((success) => {
      if (success) {
        this.fetchCoupons();
      }
    });
  }

  public confirmDelete(coupon: any) {
    this.modalService.confirm({
      title: 'Xác nhận xóa mã giảm giá',
      description: `Bạn có chắc chắn muốn xóa mã giảm giá '${coupon.code}'? Hành động này không thể hoàn tác và các đơn hàng cũ đã sử dụng mã này vẫn được lưu vết bình thường.`,
      confirmText: 'Xóa mã giảm giá',
      cancelText: 'Hủy',
      confirmButtonClass: 'btn-danger'
    }).afterClosed$.subscribe((confirmed?: boolean) => {
      if (confirmed) {
        this.couponStore.deleteCoupon(coupon.id).subscribe((res) => {
          if (res !== null) {
            this.fetchCoupons();
          }
        });
      }
    });
  }
}
