import { Component, OnInit, inject, signal, computed, ChangeDetectionStrategy } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { StateService } from '@core/services/state.service';
import { LanguageService } from '@core/services/language.service';
import { ButtonComponent } from '@shared/components/button/button.component';
import { BadgeComponent } from '@shared/components/badge/badge.component';
import { CardComponent } from '@shared/components/card/card.component';
import { PageHeaderComponent } from '@shared/components/page-header/page-header.component';
import { IconComponent } from '@shared/components/icon/icon.component';
import { CustomSearchInputComponent } from '@shared/components/custom-search-input/custom-search-input.component';
import { TooltipDirective } from '@shared/components/tooltip/tooltip.directive';
import { ModalService } from '@core/services/modal.service';
import { ProductFormModalComponent } from './product-form-modal/product-form-modal.component';
import { CustomSelectComponent } from '@shared/components/custom-select/custom-select.component';
import { PaginationComponent } from '@shared/components/pagination/pagination.component';
import { TableComponent, TableCellDirective, TableColumn } from '@shared/components/table/table.component';
import { TranslatePipe } from '@shared/pipes/translate.pipe';
import { CurrencyPipe } from '@shared/pipes/currency.pipe';

@Component({
  selector: 'app-products',
  standalone: true,
  changeDetection: ChangeDetectionStrategy.OnPush,
  imports: [
    CommonModule,
    FormsModule,
    ButtonComponent,
    BadgeComponent,
    CardComponent,
    PageHeaderComponent,
    IconComponent,
    CustomSearchInputComponent,
    TooltipDirective,
    CustomSelectComponent,
    PaginationComponent,
    TableComponent,
    TableCellDirective,
    TranslatePipe,
    CurrencyPipe
  ],
  templateUrl: './products.component.html',
  styles: [`
    :host {
      display: block;
    }
  `]
})
export class ProductsComponent implements OnInit {
  public readonly state = inject(StateService);
  private readonly modalService = inject(ModalService);
  public readonly lang = inject(LanguageService);

  public readonly columns = computed<TableColumn[]>(() => {
    this.lang.currentLang();
    return [
      { key: 'id', label: 'ID', sortable: true },
      { key: 'name', label: this.lang.translate('modal.product_name'), sortable: true },
      { key: 'slug', label: this.lang.translate('cat.slug'), sortable: true },
      { key: 'price', label: this.lang.translate('modal.product_price'), sortable: true },
      { key: 'available_count', label: this.lang.translate('inv.in_stock'), sortable: true, align: 'center' },
      { key: 'status', label: this.lang.translate('common.status'), sortable: true, align: 'center' },
      { key: 'actions', label: this.lang.translate('common.actions'), sortable: false, align: 'right' }
    ];
  });

  public searchQuery = '';
  private readonly _searchQuery = signal('');

  public readonly selectedCategoryFilter = signal<string>('');
  public readonly selectedStatusFilter = signal<string>('active');
  public readonly currentPage = signal<number>(1);
  public readonly limit = 10;

  public get categoryOptions() {
    const list = this.state.productStore.categories().map((c) => ({
      label: c.name,
      value: c.id,
    }));
    return [{ label: this.lang.translate('cat.all_categories'), value: '' }, ...list];
  }

  public get statusOptions() {
    return [
      { label: this.lang.translate('common.all_status'), value: '' },
      { label: this.lang.translate('common.active'), value: 'active' },
      { label: this.lang.translate('common.inactive'), value: 'inactive' },
      { label: this.lang.translate('common.deleted'), value: 'deleted' },
    ];
  }

  public readonly filteredProducts = computed(() => {
    return this.state.productStore.products();
  });

  ngOnInit() {
    this.fetchFilteredProducts(1);
    this.state.productStore.fetchCategories().subscribe();
  }

  public fetchFilteredProducts(page: number = 1) {
    this.currentPage.set(page);
    const statusVal = this.selectedStatusFilter();
    
    let filterDeleted = 'active';
    let actualStatus: string | undefined = undefined;

    if (statusVal === '') {
      filterDeleted = 'all';
    } else if (statusVal === 'deleted') {
      filterDeleted = 'deleted';
    } else {
      filterDeleted = 'active';
      actualStatus = statusVal;
    }

    this.state.productStore.fetchProducts(
      page,
      this.limit,
      this.selectedCategoryFilter() || undefined,
      this._searchQuery().trim() || undefined,
      actualStatus,
      filterDeleted
    ).subscribe();
  }

  public fetchPage(page: number) {
    this.fetchFilteredProducts(page);
  }

  public onSearch(query: string) {
    this._searchQuery.set(query);
    this.fetchFilteredProducts(1);
  }

  public onCategoryFilterChange(val: string) {
    this.selectedCategoryFilter.set(val);
    this.fetchFilteredProducts(1);
  }

  public onStatusFilterChange(val: string) {
    this.selectedStatusFilter.set(val);
    this.fetchFilteredProducts(1);
  }

  public openCreateDrawer() {
    this.modalService.open(ProductFormModalComponent, {
      title: this.lang.translate('modal.add_product_title'),
      size: 'lg',
      data: null
    }).afterClosed$.subscribe((success) => {
      if (success) {
        this.fetchFilteredProducts(1);
      }
    });
  }

  public openEditDrawer(product: any) {
    if (product.is_deleted) {
      this.state.showToast(this.lang.translate('toast.edit_deleted_product'), 'error');
      return;
    }
    this.modalService.open(ProductFormModalComponent, {
      title: this.lang.translate('modal.edit_product_title'),
      size: 'lg',
      data: product
    }).afterClosed$.subscribe((success) => {
      if (success) {
        this.fetchFilteredProducts(this.currentPage());
      }
    });
  }

  public confirmDelete(product: any) {
    this.modalService.confirm({
      title: this.lang.translate('modal.delete_product_title'),
      description: `${this.lang.translate('modal.delete_product_desc')} (${product.name})`,
      confirmText: this.lang.translate('action.delete'),
      cancelText: this.lang.translate('action.cancel'),
      confirmButtonClass: 'btn-danger'
    }).afterClosed$.subscribe((confirmed?: boolean) => {
      if (confirmed) {
        this.state.productStore.deleteProduct(product.id).subscribe((res) => {
          if (res !== null) {
            this.fetchFilteredProducts(this.currentPage());
          }
        });
      }
    });
  }

  public restoreProduct(product: any) {
    this.modalService.confirm({
      title: this.lang.translate('modal.restore_product_title'),
      description: `${this.lang.translate('modal.restore_product_desc')} (${product.name})`,
      confirmText: this.lang.translate('action.restore'),
      cancelText: this.lang.translate('action.cancel'),
      confirmButtonClass: 'btn-primary'
    }).afterClosed$.subscribe((confirmed?: boolean) => {
      if (confirmed) {
        this.state.productStore.restoreProduct(product.id).subscribe((res) => {
          if (res !== null) {
            this.fetchFilteredProducts(this.currentPage());
          }
        });
      }
    });
  }

  public copyToClipboard(text: string, message: string) {
    navigator.clipboard.writeText(text).then(
      () => this.state.showToast(message, 'success'),
      () => this.state.showToast(this.lang.translate('toast.copy_error'), 'error')
    );
  }
}
