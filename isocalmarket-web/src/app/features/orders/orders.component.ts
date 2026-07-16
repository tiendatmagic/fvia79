import { Component, OnInit, inject, signal } from '@angular/core';
import { CommonModule } from '@angular/common';
import { Router } from '@angular/router';
import { StateService } from '@core/services/state.service';
import { SkeletonLoaderComponent } from '@shared/components/skeleton-loader/skeleton-loader.component';
import { PaginationComponent } from '@shared/components/pagination/pagination.component';
import { BadgeComponent } from '@shared/components/badge/badge.component';
import { CardComponent } from '@shared/components/card/card.component';
import { PageHeaderComponent } from '@shared/components/page-header/page-header.component';
import { IconComponent } from '@shared/components/icon/icon.component';
import { CustomSearchInputComponent } from '@shared/components/custom-search-input/custom-search-input.component';
import { FormsModule } from '@angular/forms';

@Component({
  selector: 'app-orders',
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
  private readonly router = inject(Router);

  public readonly currentPage = signal<number>(1);
  public readonly limit = 10;
  public searchQuery = '';
  private readonly _searchQuery = signal('');

  ngOnInit() {
    this.fetchPage(1);
  }

  public fetchPage(page: number) {
    this.currentPage.set(page);
    this.state.orderStore.fetchOrders(page, this.limit, this._searchQuery().trim() || undefined).subscribe();
  }

  public onSearch(query: string) {
    this._searchQuery.set(query);
    this.fetchPage(1);
  }

  public viewDetail(order: any) {
    this.router.navigate(['/orders', order.id]);
  }

  public copyToClipboard(text: string, message: string) {
    navigator.clipboard.writeText(text).then(
      () => this.state.showToast(message, 'success'),
      () => this.state.showToast('Không thể sao chép!', 'error')
    );
  }
}
