import { Component, inject, OnInit, signal } from '@angular/core';
import { CommonModule } from '@angular/common';
import { Router, RouterModule } from '@angular/router';
import { StateService } from '@core/services/state.service';
import { BadgeComponent } from '@shared/components/badge/badge.component';
import { IconComponent } from '@shared/components/icon/icon.component';
import { PaginationComponent } from '@shared/components/pagination/pagination.component';
import { PageHeaderComponent } from '@shared/components/page-header/page-header.component';
import { CardComponent } from '@shared/components/card/card.component';
import { CustomSearchInputComponent } from '@shared/components/custom-search-input/custom-search-input.component';
import { CustomSelectComponent } from '@shared/components/custom-select/custom-select.component';
import { CustomDateTimeRangeComponent, DateTimeRangeValue } from '@shared/components/custom-date-time-range/custom-date-time-range.component';
import { FormsModule } from '@angular/forms';
import { TableComponent, TableCellDirective, TableColumn } from '@shared/components/table/table.component';
import { ModalService } from '@core/services/modal.service';
import { DepositDetailModalComponent } from './deposit-detail-modal.component';
import { TooltipDirective } from '@shared/components/tooltip/tooltip.directive';

export interface DepositHistoryItem {
  id: string;
  code: string;
  amountUsdt: number;
  amountToPay: number;
  amountVnd: number;
  status: 'pending_payment' | 'completed' | 'failed' | 'expired';
  createdAt: string;
  adminWalletAddress?: string;
  txHash?: string;
  paymentMethod?: string;
  payerBinanceId?: string;
  adminBinanceId?: string;
}

@Component({
  selector: 'app-deposit-history',
  standalone: true,
  imports: [
    CommonModule,
    RouterModule,
    FormsModule,
    BadgeComponent,
    IconComponent,
    PaginationComponent,
    PageHeaderComponent,
    CardComponent,
    CustomSearchInputComponent,
    CustomSelectComponent,
    CustomDateTimeRangeComponent,
    TableComponent,
    TableCellDirective,
    TooltipDirective,
  ],
  templateUrl: './deposit-history.component.html',
  styles: [`
    :host {
      display: block;
    }
  `]
})
export class DepositHistoryComponent implements OnInit {
  public readonly state = inject(StateService);
  private readonly router = inject(Router);
  private readonly modalService = inject(ModalService);

  public readonly columns: TableColumn[] = [
    { key: 'code', label: 'Mã lệnh', sortable: true },
    { key: 'amountUsdt', label: 'Số lượng (USDT)', sortable: true, align: 'right' },
    { key: 'amountVnd', label: 'Quy đổi VND', sortable: true, align: 'right' },
    { key: 'status', label: 'Trạng thái', sortable: true, align: 'center' },
    { key: 'createdAt', label: 'Thời gian tạo', sortable: true },
    { key: 'actions', label: 'Thao tác', sortable: false, align: 'right' }
  ];

  public readonly currentPage = signal<number>(1);
  public readonly itemsPerPage = 10;
  
  public searchQuery = '';
  private readonly _searchQuery = signal('');

  public selectedStatusFilter = '';
  public selectedDateRange: DateTimeRangeValue = { startDate: '', endDate: '' };

  public readonly statusOptions = [
    { label: 'Tất cả trạng thái', value: '' },
    { label: 'Chờ thanh toán', value: 'pending_payment' },
    { label: 'Thành công', value: 'completed' },
    { label: 'Đã hết hạn', value: 'expired' },
    { label: 'Thất bại', value: 'failed' }
  ];

  ngOnInit() {
    this.fetchPage(1);
    this.state.depositStore.fetchRate().subscribe();
  }

  // Tải danh sách giao dịch từ API thật
  public fetchPage(page: number) {
    this.currentPage.set(page);
    this.state.depositStore.fetchHistory(
      page,
      this.itemsPerPage,
      this._searchQuery().trim() || undefined,
      this.selectedStatusFilter || undefined,
      this.selectedDateRange.startDate || undefined,
      this.selectedDateRange.endDate || undefined
    ).subscribe({
      error: () => this.state.showToast('Không thể tải lịch sử nạp tiền!', 'error')
    });
  }

  public onSearch(query: string) {
    this._searchQuery.set(query);
    this.fetchPage(1);
  }

  public viewInvoice(item: DepositHistoryItem) {
    if (item.status === 'pending_payment') {
      this.router.navigate(['/deposit/invoice', item.id]);
    }
  }

  public viewDetails(item: DepositHistoryItem) {
    this.modalService.open(DepositDetailModalComponent, {
      title: 'Chi tiết lệnh nạp tiền',
      size: 'md',
      data: { invoice: item }
    });
  }
}
