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
import { TranslatePipe } from '@shared/pipes/translate.pipe';
import { LanguageService } from '@core/services/language.service';
import { computed } from '@angular/core';

export interface DepositHistoryItem {
  id: string;
  userId?: string;
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
    BadgeComponent,
    IconComponent,
    PaginationComponent,
    PageHeaderComponent,
    CardComponent,
    CustomSearchInputComponent,
    CustomSelectComponent,
    CustomDateTimeRangeComponent,
    FormsModule,
    TableComponent,
    TableCellDirective,
    TooltipDirective,
    TranslatePipe
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
  public readonly lang = inject(LanguageService);

  public readonly columns = computed<TableColumn[]>(() => {
    const language = this.lang.currentLang();
    const columns: TableColumn[] = [
      { key: 'code', label: this.lang.translate('deposit.code'), sortable: true },
      { key: 'amountUsdt', label: this.lang.translate('deposit.amount_usdt'), sortable: true, align: 'right' },
      { key: 'status', label: this.lang.translate('common.status'), sortable: true, align: 'center' },
      { key: 'createdAt', label: this.lang.translate('common.created_at'), sortable: true },
      { key: 'actions', label: this.lang.translate('common.actions'), sortable: false, align: 'right' }
    ];

    if (language === 'vi') {
      columns.splice(2, 0, {
        key: 'amountVnd',
        label: this.lang.translate('deposit.equivalent_vnd'),
        sortable: true,
        align: 'right'
      });
    }

    return columns;
  });

  public readonly currentPage = signal<number>(1);
  public readonly itemsPerPage = 10;
  
  public searchQuery = '';
  private readonly _searchQuery = signal('');

  public selectedStatusFilter = '';
  public selectedDateRange: DateTimeRangeValue = { startDate: '', endDate: '' };

  public get statusOptions() {
    return [
      { label: this.lang.translate('common.all_status'), value: '' },
      { label: this.lang.translate('status.pending_payment'), value: 'pending_payment' },
      { label: this.lang.translate('status.completed'), value: 'completed' },
      { label: this.lang.translate('status.expired'), value: 'expired' },
      { label: this.lang.translate('status.failed'), value: 'failed' }
    ];
  }

  ngOnInit() {
    this.state.authStore.checkMe(true).subscribe();
    this.fetchPage(1);
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
      error: () => this.state.showToast(this.lang.translate('toast.deposit_history_failed'), 'error')
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
      title: this.lang.translate('modal.deposit_detail_title'),
      size: 'md',
      data: { invoice: item }
    });
  }
}
