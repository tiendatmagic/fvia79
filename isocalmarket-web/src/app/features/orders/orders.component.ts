import { Component, OnInit, inject, signal } from '@angular/core';
import { CommonModule } from '@angular/common';
import { StateService } from '@core/services/state.service';
import { SkeletonLoaderComponent } from '@shared/components/skeleton-loader/skeleton-loader.component';
import { ButtonComponent } from '@shared/components/button/button.component';
import { PaginationComponent } from '@shared/components/pagination/pagination.component';
import { ModalService } from '@core/services/modal.service';
import { OrderDetailModalComponent } from './order-detail-modal/order-detail-modal.component';

@Component({
  selector: 'app-orders',
  standalone: true,
  imports: [
    CommonModule,
    SkeletonLoaderComponent,
    PaginationComponent
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
  private readonly modalService = inject(ModalService);
  
  public readonly currentPage = signal<number>(1);
  public readonly limit = 10;

  ngOnInit() {
    this.fetchPage(1);
  }

  public fetchPage(page: number) {
    this.currentPage.set(page);
    this.state.orderStore.fetchOrders(page, this.limit).subscribe();
  }

  public openDetailDrawer(order: any) {
    this.modalService.open(OrderDetailModalComponent, {
      title: 'Chi Tiết Đơn Hàng',
      size: '2xl', // Đặt size 2xl cho rộng rãi dễ nhìn danh sách accounts
      data: {
        orderId: order.id
      }
    });
  }
}
