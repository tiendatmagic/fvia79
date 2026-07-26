import { Component, OnInit, inject, signal, computed, ChangeDetectionStrategy } from '@angular/core';
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
import { LanguageService } from '@core/services/language.service';
import { CouponFormModalComponent } from './coupon-form-modal/coupon-form-modal.component';
import { PaginationComponent } from '@shared/components/pagination/pagination.component';
import { BadgeComponent } from '@shared/components/badge/badge.component';
import { TableComponent, TableCellDirective, TableColumn } from '@shared/components/table/table.component';
import { TranslatePipe } from '@shared/pipes/translate.pipe';
import { CurrencyPipe } from '@shared/pipes/currency.pipe';
import { CouponStore } from './coupon.store';

@Component({
  selector: 'app-admin-coupons',
  standalone: true,
  changeDetection: ChangeDetectionStrategy.OnPush,
  imports: [
    CommonModule,
    FormsModule,
    BadgeComponent,
    CardComponent,
    PageHeaderComponent,
    IconComponent,
    CustomSearchInputComponent,
    TooltipDirective,
    CustomSelectComponent,
    PaginationComponent,
    TableComponent,
    TableCellDirective,
    TranslatePipe,
    CurrencyPipe
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
  public readonly lang = inject(LanguageService);

  public readonly columns = computed<TableColumn[]>(() => {
    this.lang.currentLang();
    return [
      { key: 'code', label: this.lang.translate('coupon.code'), sortable: true },
      { key: 'type', label: this.lang.translate('coupon.type'), sortable: true },
      { key: 'value', label: this.lang.translate('coupon.value'), sortable: true, align: 'right' },
      { key: 'min_spend', label: this.lang.translate('coupon.min_order'), sortable: true, align: 'right' },
      { key: 'used_count', label: this.lang.translate('coupon.usage'), sortable: true, align: 'center' },
      { key: 'start_date', label: this.lang.translate('coupon.valid_period'), sortable: true },
      { key: 'status', label: this.lang.translate('common.status'), sortable: false, align: 'center' },
      { key: 'actions', label: this.lang.translate('common.actions'), sortable: false, align: 'right' }
    ];
  });

  public searchQuery = '';
  private readonly _searchQuery = signal('');

  public readonly statusFilter = signal<string>('active');
  public readonly typeFilter = signal<string>('all');

  public readonly currentPage = signal<number>(1);
  public readonly limit = 10;

  public get statusOptions() {
    return [
      { label: this.lang.translate('common.all_status'), value: 'all' },
      { label: this.lang.translate('common.active'), value: 'active' },
      { label: this.lang.translate('coupon.upcoming'), value: 'upcoming' },
      { label: this.lang.translate('status.expired'), value: 'expired' },
      { label: this.lang.translate('common.deleted'), value: 'deleted' }
    ];
  }

  public get typeOptions() {
    return [
      { label: this.lang.translate('coupon.all_types'), value: 'all' },
      { label: this.lang.translate('admin.coupon_percentage'), value: 'percentage' },
      { label: this.lang.translate('admin.coupon_fixed'), value: 'fixed' }
    ];
  }

  public readonly filteredCoupons = computed(() => {
    return this.couponStore.coupons();
  });

  ngOnInit() {
    this.fetchCoupons();
  }

  public fetchCoupons() {
    this.couponStore.fetchCoupons(
      this.currentPage(),
      this.limit,
      this._searchQuery().trim() || undefined,
      this.statusFilter(),
      this.typeFilter()
    ).subscribe();
  }

  public onStatusChange(status: string) {
    this.statusFilter.set(status);
    this.currentPage.set(1);
    this.fetchCoupons();
  }

  public onTypeChange(type: string) {
    this.typeFilter.set(type);
    this.currentPage.set(1);
    this.fetchCoupons();
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
    if (coupon.is_deleted) {
      return { label: this.lang.translate('common.deleted'), variant: 'danger' };
    }

    const now = new Date();
    const start = new Date(coupon.start_date);
    const end = new Date(coupon.end_date);

    const isUsedUp = coupon.usage_limit > 0 && coupon.used_count >= coupon.usage_limit;

    if (isUsedUp) {
      return { label: this.lang.translate('coupon.used_up'), variant: 'danger' };
    }
    if (now > end) {
      return { label: this.lang.translate('status.expired'), variant: 'danger' };
    }
    if (now < start) {
      return { label: this.lang.translate('coupon.upcoming'), variant: 'info' };
    }
    return { label: this.lang.translate('coupon.ongoing'), variant: 'success' };
  }

  public copyToClipboard(text: string, message: string = 'toast.coupon_copied') {
    if (navigator.clipboard && text) {
      navigator.clipboard.writeText(text).then(() => {
        this.toast.showToast(this.lang.translate(message), 'success');
      }).catch(() => {
        this.toast.showToast(this.lang.translate('toast.copy_error'), 'error');
      });
    }
  }

  public restoreCoupon(coupon: any) {
    this.modalService.confirm({
      title: this.lang.translate('modal.restore_coupon_title'),
      description: `${this.lang.translate('modal.restore_coupon_desc')} (${coupon.code})`,
      confirmText: this.lang.translate('action.restore'),
      cancelText: this.lang.translate('action.cancel'),
      confirmButtonClass: 'btn-primary'
    }).afterClosed$.subscribe((confirmed?: boolean) => {
      if (confirmed) {
        this.couponStore.restoreCoupon(coupon.id).subscribe((res) => {
          if (res !== null) {
            this.fetchCoupons();
          }
        });
      }
    });
  }

  public openCreateModal() {
    this.modalService.open(CouponFormModalComponent, {
      title: this.lang.translate('modal.add_coupon_title'),
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
      title: this.lang.translate('modal.edit_coupon_title'),
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
      title: this.lang.translate('modal.delete_coupon_title'),
      description: `${this.lang.translate('modal.delete_coupon_desc')} (${coupon.code})`,
      confirmText: this.lang.translate('action.delete'),
      cancelText: this.lang.translate('action.cancel'),
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
