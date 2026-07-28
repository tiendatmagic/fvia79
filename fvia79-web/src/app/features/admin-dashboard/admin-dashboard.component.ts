import { Component, OnInit, inject, signal, computed, ChangeDetectionStrategy } from '@angular/core';
import { CommonModule } from '@angular/common';
import { ApiService } from '@core/services/api.service';
import { StateService } from '@core/services/state.service';
import { RouterModule } from '@angular/router';
import { BadgeComponent } from '@shared/components/badge/badge.component';
import { CardComponent } from '@shared/components/card/card.component';
import { PageHeaderComponent } from '@shared/components/page-header/page-header.component';
import { IconComponent } from '@shared/components/icon/icon.component';
import { TooltipDirective } from '@shared/components/tooltip/tooltip.directive';
import { CustomDateTimeRangeComponent, DateTimeRangeValue } from '@shared/components/custom-date-time-range/custom-date-time-range.component';
import { FormsModule } from '@angular/forms';
import { HttpParams } from '@angular/common/http';
import { CustomSelectComponent } from '@shared/components/custom-select/custom-select.component';
import { CustomSearchInputComponent } from '@shared/components/custom-search-input/custom-search-input.component';
import { TableComponent, TableCellDirective, TableColumn } from '@shared/components/table/table.component';
import { LanguageService } from '@core/services/language.service';
import { TranslatePipe } from '@shared/pipes/translate.pipe';
import { CurrencyPipe } from '@shared/pipes/currency.pipe';
import { PaginationComponent } from '@shared/components/pagination/pagination.component';

@Component({
  selector: 'app-admin-dashboard',
  standalone: true,
  changeDetection: ChangeDetectionStrategy.OnPush,
  imports: [
    CommonModule,
    RouterModule,
    BadgeComponent,
    CardComponent,
    PageHeaderComponent,
    IconComponent,
    TooltipDirective,
    FormsModule,
    CustomDateTimeRangeComponent,
    CustomSelectComponent,
    CustomSearchInputComponent,
    TableComponent,
    TableCellDirective,
    TranslatePipe,
    CurrencyPipe,
    PaginationComponent
  ],
  templateUrl: './admin-dashboard.component.html',
  styles: [`
    :host {
      display: block;
    }
  `]
})
export class AdminDashboardComponent implements OnInit {
  public readonly apiService = inject(ApiService);
  public readonly stateService = inject(StateService);
  public readonly langService = inject(LanguageService);

  public readonly lowStockColumns = computed<TableColumn[]>(() => [
    { key: 'name', label: this.langService.translate('prod.name'), sortable: true },
    { key: 'price', label: this.langService.translate('prod.unit_price'), sortable: true, align: 'right' },
    { key: 'available_count', label: this.langService.translate('dash.available_inventory'), sortable: true, align: 'center' },
    { key: 'actions', label: this.langService.translate('common.actions'), sortable: false, align: 'right' }
  ]);

  public readonly Math = Math;
  public readonly stats = signal<any>(null);
  public readonly loading = signal<boolean>(true);
  public readonly lowStockLoading = signal<boolean>(true);
  public readonly visibleDailyCount = signal<number>(6);
  public selectedDateRange: DateTimeRangeValue = { startDate: '', endDate: '' };

  public readonly dailyProfitList = computed(() => this.stats()?.financial_chart || []);

  public readonly displayedDailyProfit = computed(() => {
    return this.dailyProfitList().slice(0, this.visibleDailyCount());
  });

  public loadMoreDailyProfit() {
    this.visibleDailyCount.update((c) => Math.min(c + 12, this.dailyProfitList().length));
  }

  public resetDailyProfitCount() {
    this.visibleDailyCount.set(6);
  }

  public readonly selectedCategory = signal<string>('');
  public readonly lowStockSearch = signal<string>('');
  public readonly selectedThreshold = signal<string>('5');
  public readonly lowStockCurrentPage = signal<number>(1);
  public readonly lowStockLimit = 10;
  public rawSearchQuery = '';

  public get thresholdOptions() {
    return [
      { label: this.langService.translate('dash.less_than_items', { count: 5 }), value: '5' },
      { label: this.langService.translate('dash.at_least_items_simple', { count: 10 }), value: 'gte_10' },
      { label: this.langService.translate('dash.at_least_items_simple', { count: 50 }), value: 'gte_50' },
      { label: this.langService.translate('dash.at_least_items_simple', { count: 100 }), value: 'gte_100' },
    ];
  }

  public get categoryOptions() {
    const list = this.stateService.productStore.categories().map((c) => ({
      label: c.name,
      value: c.id,
    }));
    return [{ label: this.langService.translate('cat.all'), value: '' }, ...list];
  }

  ngOnInit() {
    this.stateService.productStore.fetchCategories().subscribe();
    this.loadStats();
  }

  public loadStats(isLowStockFilter: boolean = false) {
    if (isLowStockFilter) {
      this.lowStockLoading.set(true);
    } else {
      this.loading.set(true);
      this.lowStockLoading.set(true);
    }

    let params = new HttpParams();
    if (this.selectedDateRange.startDate) {
      params = params.set('start_date', this.selectedDateRange.startDate);
    }
    if (this.selectedDateRange.endDate) {
      params = params.set('end_date', this.selectedDateRange.endDate);
    }
    if (this.selectedCategory()) {
      params = params.set('low_stock_category_id', this.selectedCategory());
    }
    if (this.lowStockSearch().trim()) {
      params = params.set('low_stock_search', this.lowStockSearch().trim());
    }
    params = params.set('low_stock_threshold', this.selectedThreshold());
    params = params.set('low_stock_page', this.lowStockCurrentPage().toString());
    params = params.set('low_stock_limit', this.lowStockLimit.toString());

    this.apiService.get<any>('v1/admin/dashboard-stats', params).subscribe({
      next: (res) => {
        this.stats.set(res);
        this.loading.set(false);
        this.lowStockLoading.set(false);
      },
      error: (err) => {
        this.stateService.showToast(this.langService.translate('toast.error_load_stats'), 'error');
        this.loading.set(false);
        this.lowStockLoading.set(false);
      }
    });
  }

  public onSearchLowStock(query: string) {
    this.lowStockSearch.set(query);
    this.lowStockCurrentPage.set(1);
    this.loadStats(true);
  }

  public fetchLowStockPage(page: number) {
    this.lowStockCurrentPage.set(page);
    this.loadStats(true);
  }
}
