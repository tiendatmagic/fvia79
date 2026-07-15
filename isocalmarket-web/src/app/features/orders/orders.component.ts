import { Component, OnInit, inject, signal } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { StateService } from '@core/services/state.service';
import { SkeletonLoaderComponent } from '@shared/components/skeleton-loader/skeleton-loader.component';
import { ButtonComponent } from '@shared/components/button/button.component';
import { PaginationComponent } from '@shared/components/pagination/pagination.component';

@Component({
  selector: 'app-orders',
  standalone: true,
  imports: [
    CommonModule,
    FormsModule,
    SkeletonLoaderComponent,
    ButtonComponent,
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

  // Trạng thái Drawer chi tiết
  public showDetailDrawer = false;
  
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
    this.state.orderStore.fetchOrderDetail(order.id).subscribe((res) => {
      if (res) {
        this.showDetailDrawer = true;
      }
    });
  }

  public closeDetailDrawer() {
    this.showDetailDrawer = false;
  }

  public copyToClipboard(text: string) {
    if (typeof window !== 'undefined' && navigator.clipboard) {
      navigator.clipboard.writeText(text).then(() => {
        this.state.showToast('Đã copy thông tin tài khoản!', 'success');
      }).catch(() => {
        this.state.showToast('Không thể copy tự động!', 'error');
      });
    }
  }

  public downloadAccounts() {
    const detail = this.state.orderStore.currentOrderDetail();
    if (!detail || !detail.accounts || detail.accounts.length === 0) return;

    const content = detail.accounts.map((acc: any) => acc.data).join('\n');
    const blob = new Blob([content], { type: 'text/plain;charset=utf-8' });
    const url = URL.createObjectURL(blob);
    
    const a = document.createElement('a');
    a.href = url;
    a.download = `isocalmarket_${detail.order_code}_accounts.txt`;
    document.body.appendChild(a);
    a.click();
    
    document.body.removeChild(a);
    URL.revokeObjectURL(url);
    this.state.showToast('Đã tải xuống danh sách tài khoản dạng .txt!', 'success');
  }
}
