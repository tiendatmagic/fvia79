import { Component, OnInit, inject, signal } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { ButtonComponent } from '@shared/components/button/button.component';
import { CardComponent } from '@shared/components/card/card.component';
import { PageHeaderComponent } from '@shared/components/page-header/page-header.component';
import { IconComponent } from '@shared/components/icon/icon.component';
import { CustomSearchInputComponent } from '@shared/components/custom-search-input/custom-search-input.component';
import { TooltipDirective } from '@shared/components/tooltip/tooltip.directive';
import { ModalService } from '@core/services/modal.service';
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

  public readonly columns: TableColumn[] = [
    { key: 'code', label: 'Mã giảm giá', sortable: false },
    { key: 'type', label: 'Loại giảm giá', sortable: false },
    { key: 'value', label: 'Giá trị', sortable: false, align: 'right' },
    { key: 'min_spend', label: 'Đơn tối thiểu', sortable: false, align: 'right' },
    { key: 'usage', label: 'Lượt sử dụng', sortable: false, align: 'center' },
    { key: 'date_range', label: 'Thời gian áp dụng', sortable: false },
    { key: 'actions', label: 'Thao tác', sortable: false, align: 'right' }
  ];

  public searchQuery = '';
  private readonly _searchQuery = signal('');

  public readonly currentPage = signal<number>(1);
  public readonly limit = 10;

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
