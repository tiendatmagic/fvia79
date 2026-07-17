import { Component, OnInit, inject, signal, computed } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { StateService } from '@core/services/state.service';
import { SkeletonLoaderComponent } from '@shared/components/skeleton-loader/skeleton-loader.component';
import { CustomSelectComponent } from '@shared/components/custom-select/custom-select.component';
import { ButtonComponent } from '@shared/components/button/button.component';
import { BadgeComponent } from '@shared/components/badge/badge.component';
import { CardComponent } from '@shared/components/card/card.component';
import { PageHeaderComponent } from '@shared/components/page-header/page-header.component';
import { IconComponent } from '@shared/components/icon/icon.component';
import { ModalService } from '@core/services/modal.service';
import { PaginationComponent } from '@shared/components/pagination/pagination.component';
import { ImportInventoryModalComponent } from './import-inventory-modal/import-inventory-modal.component';
import { EditInventoryModalComponent } from './edit-inventory-modal/edit-inventory-modal.component';
import { CustomSearchInputComponent } from '@shared/components/custom-search-input/custom-search-input.component';
import { TooltipDirective } from '@shared/components/tooltip/tooltip.directive';

@Component({
  selector: 'app-inventory',
  standalone: true,
  imports: [
    CommonModule,
    FormsModule,
    SkeletonLoaderComponent,
    CustomSelectComponent,
    ButtonComponent,
    BadgeComponent,
    CardComponent,
    PageHeaderComponent,
    IconComponent,
    PaginationComponent,
    CustomSearchInputComponent,
    TooltipDirective,
  ],
  templateUrl: './inventory.component.html',
  styles: [`
    :host {
      display: block;
    }
  `]
})
export class InventoryComponent implements OnInit {
  public readonly state = inject(StateService);
  private readonly modalService = inject(ModalService);

  public selectedProductFilter = '';
  public selectedStatusFilter = '';
  public searchQuery = '';
  private readonly _searchQuery = signal('');

  public readonly currentPage = signal<number>(1);
  public readonly limit = 10;
  public readonly loadingItemId = signal<string | null>(null);

  public statusFilterOptions = [
    { label: 'Tất cả trạng thái', value: '' },
    { label: 'Hoạt động', value: 'active' },
    { label: 'Hoàn thành', value: 'completed' },
    { label: 'Tạm khóa', value: 'inactive' }
  ];

  public get productOptions() {
    const options = this.state.productStore.products().map(p => ({
      label: p.name,
      value: String(p.id)
    }));
    return [{ label: 'Chọn sản phẩm', value: '' }, ...options];
  }

  public get productFilterOptions() {
    const options = this.state.productStore.products().map(p => ({
      label: p.name,
      value: String(p.id)
    }));
    return [{ label: 'Tất cả sản phẩm', value: '' }, ...options];
  }

  ngOnInit() {
    this.state.productStore.fetchProducts().subscribe();
    this.fetchFilteredItems(1);
  }

  public fetchFilteredItems(page: number = 1) {
    this.currentPage.set(page);
    this.state.inventoryStore.fetchItems({
      product_id: this.selectedProductFilter || undefined,
      status: this.selectedStatusFilter || undefined,
      search: this._searchQuery().trim() || undefined
    }, page, this.limit).subscribe();
  }

  public onSearch(query: string) {
    this._searchQuery.set(query);
    this.fetchFilteredItems(1);
  }

  public fetchPage(page: number) {
    this.fetchFilteredItems(page);
  }

  public getProductName(productId: string): string {
    const product = this.state.productStore.products().find(p => p.id === productId);
    return product ? product.name : `Sản phẩm #${productId}`;
  }

  public openImportDrawer() {
    this.modalService.open(ImportInventoryModalComponent, {
      title: 'Import Tài Nguyên Số',
      size: '2xl',
      data: {
        selectedProductId: this.selectedProductFilter || '',
        productOptions: this.productOptions
      }
    }).afterClosed$.subscribe((success) => {
      if (success) {
        this.state.productStore.fetchProducts().subscribe();
        this.fetchFilteredItems();
      }
    });
  }

  public openEditModal(item: any) {
    if (this.loadingItemId()) return;
    this.loadingItemId.set(item.id);

    this.state.inventoryStore.fetchItemDetail(item.id).subscribe({
      next: (freshItem) => {
        this.loadingItemId.set(null);
        if (!freshItem) return;

        const cleanProductOptions = this.state.productStore.products().map(p => ({
          label: p.name,
          value: String(p.id)
        }));

        this.modalService.open(EditInventoryModalComponent, {
          title: 'Chỉnh sửa tài nguyên',
          size: '2xl',
          data: {
            item: freshItem,
            productOptions: cleanProductOptions
          }
        }).afterClosed$.subscribe((success) => {
          if (success) {
            this.state.productStore.fetchProducts().subscribe();
            this.fetchFilteredItems(this.currentPage());
          }
        });
      },
      error: () => {
        this.loadingItemId.set(null);
      }
    });
  }

  public confirmDeleteItem(item: any) {
    this.modalService.confirm({
      title: 'Xác nhận xóa tài nguyên',
      description: `Bạn có chắc chắn muốn xóa tài nguyên này ra khỏi kho? Hành động này không thể hoàn tác.`,
      confirmText: 'Xóa tài nguyên',
      cancelText: 'Hủy',
      confirmButtonClass: 'btn-danger'
    }).afterClosed$.subscribe((confirmed?: boolean) => {
      if (confirmed) {
        this.state.inventoryStore.deleteItem(item.id, this.selectedProductFilter).subscribe((res) => {
          if (res !== null) {
            this.state.productStore.fetchProducts().subscribe();
            this.fetchFilteredItems(this.currentPage());
          }
        });
      }
    });
  }

  public copyToClipboard(text: string, message: string = 'Đã sao chép!') {
    navigator.clipboard.writeText(text).then(
      () => this.state.showToast(message, 'success'),
      () => this.state.showToast('Không thể sao chép!', 'error')
    );
  }
}
