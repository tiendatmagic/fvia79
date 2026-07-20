import { Component, OnInit, inject, signal, computed, ChangeDetectionStrategy } from '@angular/core';
import { CommonModule } from '@angular/common';
import { Router } from '@angular/router';
import { StateService } from '@core/services/state.service';
import { LanguageService } from '@core/services/language.service';
import { PaginationComponent } from '@shared/components/pagination/pagination.component';
import { BadgeComponent } from '@shared/components/badge/badge.component';
import { CardComponent } from '@shared/components/card/card.component';
import { PageHeaderComponent } from '@shared/components/page-header/page-header.component';
import { IconComponent } from '@shared/components/icon/icon.component';
import { CustomSearchInputComponent } from '@shared/components/custom-search-input/custom-search-input.component';
import { CustomSelectComponent } from '@shared/components/custom-select/custom-select.component';
import { CustomDateTimeRangeComponent, DateTimeRangeValue } from '@shared/components/custom-date-time-range/custom-date-time-range.component';
import { TooltipDirective } from '@shared/components/tooltip/tooltip.directive';
import { FormsModule } from '@angular/forms';
import { TableComponent, TableCellDirective, TableColumn } from '@shared/components/table/table.component';
import { TranslatePipe } from '@shared/pipes/translate.pipe';
import { CurrencyPipe } from '@shared/pipes/currency.pipe';

@Component({
  selector: 'app-orders',
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
  templateUrl: './orders.component.html',
  styles: [`
    :host {
      display: block;
    }
  `]
})
export class OrdersComponent implements OnInit {
  public readonly state = inject(StateService);
  public readonly lang = inject(LanguageService);
  private readonly router = inject(Router);

  public readonly columns = computed<TableColumn[]>(() => {
    this.lang.currentLang();
    return [
      { key: 'order_code', label: this.lang.translate('orders.order_code'), sortable: true },
      { key: 'created_at', label: this.lang.translate('orders.created_at'), sortable: true },
      { key: 'total_price', label: this.lang.translate('orders.total_price'), sortable: true },
      { key: 'status', label: this.lang.translate('orders.status'), sortable: true, align: 'center' },
      { key: 'actions', label: this.lang.translate('common.actions'), sortable: false, align: 'right' }
    ];
  });

  public readonly currentPage = signal<number>(1);
  public readonly limit = 10;
  public searchQuery = '';
  private readonly _searchQuery = signal('');

  public selectedStatusFilter = '';
  public selectedDateRange: DateTimeRangeValue = { startDate: '', endDate: '' };

  public get statusOptions() {
    return [
      { label: this.lang.translate('common.all_status'), value: '' },
      { label: this.lang.translate('orders.status_completed'), value: 'paid' },
      { label: this.lang.translate('orders.status_pending'), value: 'pending' },
      { label: this.lang.translate('orders.status_cancelled'), value: 'cancelled' }
    ];
  }

  ngOnInit() {
    this.fetchPage(1);
  }

  public fetchPage(page: number) {
    this.currentPage.set(page);
    this.state.orderStore.fetchOrders(
      page,
      this.limit,
      this._searchQuery().trim() || undefined,
      this.selectedStatusFilter || undefined,
      this.selectedDateRange.startDate || undefined,
      this.selectedDateRange.endDate || undefined
    ).subscribe();
  }

  public onSearch(query: string) {
    this._searchQuery.set(query);
    this.fetchPage(1);
  }

  public viewDetail(order: any) {
    this.router.navigate(['/orders', order.id]);
  }

  public copyOrderCode(code: string) {
    this.copyToClipboard(code, this.lang.translate('orders.copied'));
  }

  public copyToClipboard(text: string, message: string) {
    navigator.clipboard.writeText(text).then(
      () => this.state.showToast(message, 'success'),
      () => this.state.showToast(this.lang.translate('toast.copy_error'), 'error')
    );
  }
}
