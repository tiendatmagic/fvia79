import { Component, OnInit, inject, signal } from '@angular/core';
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

@Component({
  selector: 'app-admin-dashboard',
  standalone: true,
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
    CustomSearchInputComponent
  ],
  templateUrl: './admin-dashboard.component.html',
  styles: [`
    :host {
      display: block;
    }
  `]
})
export class AdminDashboardComponent implements OnInit {
  private readonly apiService = inject(ApiService);
  public readonly stateService = inject(StateService);

  public readonly stats = signal<any>(null);
  public readonly loading = signal<boolean>(true);
  public selectedDateRange: DateTimeRangeValue = { startDate: '', endDate: '' };

  // low stock filter/search signals
  public readonly selectedCategory = signal<string>('');
  public readonly lowStockSearch = signal<string>('');
  public rawSearchQuery = '';

  public get categoryOptions() {
    const list = this.stateService.productStore.categories().map((c) => ({
      label: c.name,
      value: c.id,
    }));
    return [{ label: 'Tất cả danh mục', value: '' }, ...list];
  }

  ngOnInit() {
    this.stateService.productStore.fetchCategories().subscribe();
    this.loadStats();
  }

  public loadStats() {
    this.loading.set(true);
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

    this.apiService.get<any>('v1/admin/dashboard-stats', params).subscribe({
      next: (res) => {
        this.stats.set(res);
        this.loading.set(false);
      },
      error: (err) => {
        this.stateService.showToast('Không thể tải số liệu thống kê!', 'error');
        this.loading.set(false);
      }
    });
  }

  public onSearchLowStock(query: string) {
    this.lowStockSearch.set(query);
    this.loadStats();
  }
}
