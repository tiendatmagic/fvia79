import { Component, OnInit, inject, signal, computed, ChangeDetectionStrategy } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { Router } from '@angular/router';
import { StateService } from '@core/services/state.service';
import { LanguageService } from '@core/services/language.service';
import { PaginationComponent } from '@shared/components/pagination/pagination.component';
import { BadgeComponent } from '@shared/components/badge/badge.component';
import { CardComponent } from '@shared/components/card/card.component';
import { PageHeaderComponent } from '@shared/components/page-header/page-header.component';
import { IconComponent } from '@shared/components/icon/icon.component';
import { CustomSearchInputComponent } from '@shared/components/custom-search-input/custom-search-input.component';
import { TooltipDirective } from '@shared/components/tooltip/tooltip.directive';
import { CustomSelectComponent } from '@shared/components/custom-select/custom-select.component';
import { CustomDateTimeRangeComponent, DateTimeRangeValue } from '@shared/components/custom-date-time-range/custom-date-time-range.component';
import { TableComponent, TableCellDirective, TableColumn } from '@shared/components/table/table.component';
import { TranslatePipe } from '@shared/pipes/translate.pipe';
import { CurrencyPipe } from '@shared/pipes/currency.pipe';

@Component({
  selector: 'app-admin-orders',
  standalone: true,
  changeDetection: ChangeDetectionStrategy.OnPush,
  imports: [
    CommonModule,
    FormsModule,
    PaginationComponent,
    BadgeComponent,
    CardComponent,
    PageHeaderComponent,
    IconComponent,
    CustomSearchInputComponent,
    TooltipDirective,
    CustomSelectComponent,
    CustomDateTimeRangeComponent,
    TableComponent,
    TableCellDirective,
    TranslatePipe,
    CurrencyPipe
  ],
  templateUrl: './admin-orders.component.html',
  styles: [`
    :host {
      display: block;
    }
  `]
})
export class AdminOrdersComponent implements OnInit {
  public readonly stateService = inject(StateService);
  private readonly router = inject(Router);
  public readonly lang = inject(LanguageService);

  public readonly columns = computed<TableColumn[]>(() => {
    this.lang.currentLang();
    return [
      { key: 'order_code', label: this.lang.translate('ord.code'), sortable: true },
      { key: 'customer_email', label: this.lang.translate('usr.contact'), sortable: true },
      { key: 'total_price', label: this.lang.translate('ord.total'), sortable: true, align: 'right' },
      { key: 'items_count', label: this.lang.translate('ord.quantity'), sortable: true, align: 'center' },
      { key: 'status', label: this.lang.translate('common.status'), sortable: true, align: 'center' },
      { key: 'created_at', label: this.lang.translate('ord.date'), sortable: true },
      { key: 'actions', label: this.lang.translate('common.actions'), sortable: false, align: 'right' }
    ];
  });

  public readonly orders = signal<any[]>([]);
  public readonly total = signal<number>(0);
  public readonly totalPages = signal<number>(1);
  public readonly loading = signal<boolean>(true);

  public readonly currentPage = signal<number>(1);
  public readonly limit = 10;
  public readonly selectedStatus = signal<string>('');
  public selectedDateRange: DateTimeRangeValue = { startDate: '', endDate: '' };
  public searchQuery = '';
  private readonly _searchQuery = signal('');

  public get statusOptions() {
    return [
      { label: this.lang.translate('common.all_status'), value: '' },
      { label: this.lang.translate('status.completed'), value: 'paid' },
      { label: this.lang.translate('status.pending_payment'), value: 'pending' },
      { label: this.lang.translate('status.cancelled'), value: 'cancelled' }
    ];
  }

  ngOnInit() {
    this.fetchOrders(1);
  }

  public fetchOrders(page: number) {
    this.currentPage.set(page);
    this.loading.set(true);

    this.stateService.orderStore.fetchOrders(
      page,
      this.limit,
      this._searchQuery().trim() || undefined,
      this.selectedStatus() || undefined,
      this.selectedDateRange.startDate || undefined,
      this.selectedDateRange.endDate || undefined
    ).subscribe({
      next: (res) => {
        this.orders.set(res.data || []);
        const totalCount = res.meta?.total || 0;
        this.total.set(totalCount);
        this.totalPages.set(Math.ceil(totalCount / this.limit));
        this.loading.set(false);
      },
      error: () => {
        this.stateService.showToast(this.lang.translate('toast.error_load_orders'), 'error');
        this.loading.set(false);
      }
    });
  }

  public onRefresh(): void {
    this.stateService.orderStore.clearCache();
    this.fetchOrders(this.currentPage());
  }

  public onSearch(query: string) {
    this._searchQuery.set(query);
    this.fetchOrders(1);
  }

  public onStatusChange(status: string) {
    this.selectedStatus.set(status);
    this.fetchOrders(1);
  }

  public viewDetail(orderId: string) {
    this.router.navigate(['/orders', orderId]);
  }

  public copyToClipboard(text: string, message: string) {
    navigator.clipboard.writeText(text).then(
      () => this.stateService.showToast(message, 'success'),
      () => this.stateService.showToast(this.lang.translate('toast.copy_error'), 'error')
    );
  }
}
