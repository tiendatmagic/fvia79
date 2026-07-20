import { Component, OnInit, inject, signal, computed } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { RouterModule } from '@angular/router';
import { StateService } from '@core/services/state.service';
import { LanguageService } from '@core/services/language.service';
import { ModalService } from '@core/services/modal.service';
import { BadgeComponent } from '@shared/components/badge/badge.component';
import { CardComponent } from '@shared/components/card/card.component';
import { PageHeaderComponent } from '@shared/components/page-header/page-header.component';
import { IconComponent } from '@shared/components/icon/icon.component';
import { CustomSearchInputComponent } from '@shared/components/custom-search-input/custom-search-input.component';
import { TooltipDirective } from '@shared/components/tooltip/tooltip.directive';
import { PaginationComponent } from '@shared/components/pagination/pagination.component';
import { CustomSelectComponent } from '@shared/components/custom-select/custom-select.component';
import { CustomDateTimeRangeComponent, DateTimeRangeValue } from '@shared/components/custom-date-time-range/custom-date-time-range.component';
import { TableComponent, TableCellDirective, TableColumn } from '@shared/components/table/table.component';
import { CurrencyPipe } from '@shared/pipes/currency.pipe';
import { TranslatePipe } from '@shared/pipes/translate.pipe';
import { AdminDepositStore } from './admin-deposit.store';
import { ApproveDepositModalComponent } from './approve-deposit-modal/approve-deposit-modal.component';

@Component({
  selector: 'app-admin-deposits',
  standalone: true,
  imports: [
    CommonModule,
    FormsModule,
    RouterModule,
    BadgeComponent,
    CardComponent,
    PageHeaderComponent,
    IconComponent,
    CustomSearchInputComponent,
    TooltipDirective,
    PaginationComponent,
    CustomSelectComponent,
    CustomDateTimeRangeComponent,
    TableComponent,
    TableCellDirective,
    TranslatePipe,
    CurrencyPipe
  ],
  templateUrl: './admin-deposits.component.html',
  styles: [`
    :host {
      display: block;
    }
  `]
})
export class AdminDepositsComponent implements OnInit {
  public readonly state = inject(StateService);
  public readonly depositStore = inject(AdminDepositStore);
  private readonly modalService = inject(ModalService);
  public readonly lang = inject(LanguageService);

  public readonly columns = computed<TableColumn[]>(() => {
    this.lang.currentLang();
    return [
      { key: 'code', label: 'Code', sortable: true },
      { key: 'userName', label: this.lang.translate('usr.name'), sortable: true },
      { key: 'amountUsdt', label: 'USDT', sortable: true, align: 'right' },
      { key: 'amountVnd', label: 'Amount', sortable: true, align: 'right' },
      { key: 'paymentMethod', label: 'Method', sortable: true, align: 'center' },
      { key: 'status', label: this.lang.translate('common.status'), sortable: true, align: 'center' },
      { key: 'createdAt', label: 'Created At', sortable: true },
      { key: 'actions', label: this.lang.translate('common.actions'), sortable: false, align: 'right' }
    ];
  });

  public searchQuery = '';
  private readonly _searchQuery = signal('');

  public readonly selectedStatusFilter = signal<string>('');
  public readonly selectedMethodFilter = signal<string>('');
  public readonly selectedDateRange = signal<DateTimeRangeValue>({ startDate: '', endDate: '' });

  public get statusOptions() {
    return [
      { label: this.lang.translate('common.all_status'), value: '' },
      { label: this.lang.translate('status.pending_payment'), value: 'pending_payment' },
      { label: this.lang.translate('status.completed'), value: 'completed' },
      { label: this.lang.translate('status.failed'), value: 'failed' },
      { label: this.lang.translate('status.expired'), value: 'expired' }
    ];
  }

  public readonly methodOptions = [
    { label: 'Tất cả phương thức', value: '' },
    { label: 'USDT BEP20', value: 'bep20' },
    { label: 'Binance Pay', value: 'binance' }
  ];

  public readonly currentPage = signal<number>(1);
  public readonly limit = 10;

  ngOnInit() {
    this.fetchDeposits(1);
  }

  public fetchDeposits(page: number = 1) {
    this.currentPage.set(page);
    this.depositStore.fetchDeposits(
      page,
      this.limit,
      this._searchQuery().trim() || undefined,
      this.selectedStatusFilter() || undefined,
      this.selectedMethodFilter() || undefined,
      this.selectedDateRange().startDate || undefined,
      this.selectedDateRange().endDate || undefined
    ).subscribe();
  }

  public onRefresh() {
    this.depositStore.clearCache();
    this.fetchDeposits(this.currentPage());
  }

  public onSearch(val: string) {
    this._searchQuery.set(val);
    this.fetchDeposits(1);
  }

  public getBadgeVariant(status: string): 'success' | 'warning' | 'danger' | 'neutral' {
    switch (status) {
      case 'completed': return 'success';
      case 'pending_payment': return 'warning';
      case 'failed': return 'danger';
      case 'expired': return 'neutral';
      default: return 'neutral';
    }
  }

  public getStatusText(status: string): string {
    switch (status) {
      case 'completed': return 'Đã hoàn thành';
      case 'pending_payment': return 'Chờ thanh toán';
      case 'failed': return 'Thất bại';
      case 'expired': return 'Đã hết hạn';
      default: return status;
    }
  }

  public getMethodText(method: string): string {
    switch (method) {
      case 'bep20': return 'USDT BEP20';
      case 'binance': return 'Binance Pay';
      default: return method;
    }
  }

  public openApproveModal(deposit: any) {
    const ref = this.modalService.open(ApproveDepositModalComponent, {
      title: `Duyệt lệnh nạp tiền #${deposit.code}`,
      size: 'md',
      closeOnBackdropClick: true,
      data: {
        deposit: {
          id: deposit.id,
          code: deposit.code,
          userName: deposit.userName,
          userEmail: deposit.userEmail,
          amountUsdt: deposit.amountUsdt,
          amountVnd: deposit.amountVnd,
          paymentMethod: deposit.paymentMethod
        }
      }
    });

    ref.afterClosed$.subscribe((confirmed: any) => {
      if (confirmed) {
        this.fetchDeposits(this.currentPage());
      }
    });
  }

  public rejectDeposit(deposit: any) {
    const ref = this.modalService.confirm({
      title: this.lang.translate('modal.reject_deposit_title'),
      description: `${this.lang.translate('modal.reject_deposit_desc_prefix')} #${deposit.code} ${this.lang.translate('modal.reject_deposit_desc_middle')} "${deposit.userName || deposit.userEmail}"?`
    });

    ref.afterClosed$.subscribe((confirmed: any) => {
      if (confirmed) {
        this.depositStore.rejectDeposit(deposit.id).subscribe((res) => {
          if (res !== null) {
            this.fetchDeposits(this.currentPage());
          }
        });
      }
    });
  }
}
