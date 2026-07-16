import { Component, OnInit, inject, signal } from '@angular/core';
import { CommonModule } from '@angular/common';
import { Router } from '@angular/router';
import { StateService } from '@core/services/state.service';
import { SkeletonLoaderComponent } from '@shared/components/skeleton-loader/skeleton-loader.component';
import { PaginationComponent } from '@shared/components/pagination/pagination.component';
import { BadgeComponent } from '@shared/components/badge/badge.component';
import { CardComponent } from '@shared/components/card/card.component';
import { PageHeaderComponent } from '@shared/components/page-header/page-header.component';

@Component({
  selector: 'app-orders',
  standalone: true,
  imports: [
    CommonModule,
    SkeletonLoaderComponent,
    PaginationComponent,
    BadgeComponent,
    CardComponent,
    PageHeaderComponent,
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

  ngOnInit() {
    this.fetchPage(1);
  }

  public fetchPage(page: number) {
    this.currentPage.set(page);
    this.state.orderStore.fetchOrders(page, this.limit).subscribe();
  }

  public viewDetail(order: any) {
    this.router.navigate(['/orders', order.id]);
  }
}
