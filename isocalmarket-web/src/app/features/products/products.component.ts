import { Component, OnInit, inject, signal, computed, ChangeDetectionStrategy } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { StateService } from '@core/services/state.service';
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

  public readonly columns: TableColumn[] = [
    { key: 'id', label: 'ID', sortable: true },
    { key: 'name', label: 'Tên sản phẩm', sortable: true },
    { key: 'slug', label: 'Slug', sortable: true },
    { key: 'price', label: 'Đơn giá (VND)', sortable: true },
    { key: 'available_count', label: 'Tồn kho khả dụng', sortable: true, align: 'center' },
    { key: 'status', label: 'Trạng thái', sortable: true, align: 'center' },
    { key: 'actions', label: 'Thao tác', sortable: false, align: 'right' }
  ];

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
    return [{ label: 'Tất cả danh mục', value: '' }, ...list];
  }

  public readonly statusOptions = [
    { label: 'Tất cả trạng thái', value: '' },
    { label: 'Đang hoạt động', value: 'active' },
    { label: 'Ngừng hoạt động', value: 'inactive' },
    { label: 'Đã xóa', value: 'deleted' },
  ];

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
      title: 'Thêm Sản Phẩm Mới',
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
      this.state.showToast('Không thể chỉnh sửa sản phẩm đã bị xóa mềm!', 'error');
      return;
    }
    this.modalService.open(ProductFormModalComponent, {
      title: 'Cập Nhật Sản Phẩm',
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
      title: 'Xác nhận xóa sản phẩm',
      description: `Bạn có chắc chắn muốn xóa sản phẩm '${product.name}'? Sản phẩm bị xóa sẽ được chuyển vào mục lưu trữ đã xóa.`,
      confirmText: 'Xóa sản phẩm',
      cancelText: 'Hủy',
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
      title: 'Xác nhận khôi phục sản phẩm',
      description: `Bạn có chắc chắn muốn khôi phục sản phẩm '${product.name}'?`,
      confirmText: 'Khôi phục',
      cancelText: 'Hủy',
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
      () => this.state.showToast('Không thể sao chép!', 'error')
    );
  }
}
