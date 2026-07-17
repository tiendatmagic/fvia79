import { Component, OnInit, inject, signal } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { Router } from '@angular/router';
import { ApiService } from '@core/services/api.service';
import { StateService } from '@core/services/state.service';
import { SkeletonLoaderComponent } from '@shared/components/skeleton-loader/skeleton-loader.component';
import { PaginationComponent } from '@shared/components/pagination/pagination.component';
import { BadgeComponent } from '@shared/components/badge/badge.component';
import { CardComponent } from '@shared/components/card/card.component';
import { PageHeaderComponent } from '@shared/components/page-header/page-header.component';
import { IconComponent } from '@shared/components/icon/icon.component';
import { HttpParams } from '@angular/common/http';
import { CustomSearchInputComponent } from '@shared/components/custom-search-input/custom-search-input.component';
import { TooltipDirective } from '@shared/components/tooltip/tooltip.directive';

@Component({
  selector: 'app-admin-orders',
  standalone: true,
  imports: [
    CommonModule,
    FormsModule,
    SkeletonLoaderComponent,
    PaginationComponent,
    BadgeComponent,
    CardComponent,
    PageHeaderComponent,
    IconComponent,
    CustomSearchInputComponent,
    TooltipDirective,
  ],
  templateUrl: './admin-orders.component.html',
  styles: [`
    :host {
      display: block;
    }
  `]
})
export class AdminOrdersComponent implements OnInit {
  private readonly apiService = inject(ApiService);
  public readonly stateService = inject(StateService);
  private readonly router = inject(Router);

  public readonly orders = signal<any[]>([]);
  public readonly total = signal<number>(0);
  public readonly totalPages = signal<number>(1);
  public readonly loading = signal<boolean>(true);

  public readonly currentPage = signal<number>(1);
  public readonly limit = 10;
  public readonly selectedStatus = signal<string>('');
  public searchQuery = '';
  private readonly _searchQuery = signal('');

  ngOnInit() {
    this.fetchOrders(1);
  }

  public fetchOrders(page: number) {
    this.currentPage.set(page);
    this.loading.set(true);

    let params = new HttpParams()
      .set('page', page.toString())
      .set('limit', this.limit.toString());

    if (this.selectedStatus()) {
      params = params.set('status', this.selectedStatus());
    }

    if (this._searchQuery().trim()) {
      params = params.set('search', this._searchQuery().trim());
    }

    this.apiService.get<any>('v1/orders', params).subscribe({
      next: (res) => {
        this.orders.set(res.data || []);
        const totalCount = res.meta?.total || 0;
        this.total.set(totalCount);
        this.totalPages.set(Math.ceil(totalCount / this.limit));
        this.loading.set(false);
      },
      error: () => {
        this.stateService.showToast('Không thể lấy danh sách đơn hàng!', 'error');
        this.loading.set(false);
      }
    });
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
      () => this.stateService.showToast('Không thể sao chép!', 'error')
    );
  }
}
