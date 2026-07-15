import { Component, OnInit, inject, signal, computed } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { StateService } from '@core/services/state.service';
import { SkeletonLoaderComponent } from '@shared/components/skeleton-loader/skeleton-loader.component';
import { CustomSelectComponent } from '@shared/components/custom-select/custom-select.component';
import { ButtonComponent } from '@shared/components/button/button.component';
import { ModalService } from '@core/services/modal.service';
import { PaginationComponent } from '@shared/components/pagination/pagination.component';
import { ImportInventoryModalComponent } from './import-inventory-modal/import-inventory-modal.component';

@Component({
  selector: 'app-inventory',
  standalone: true,
  imports: [
    CommonModule,
    FormsModule,
    SkeletonLoaderComponent,
    CustomSelectComponent,
    ButtonComponent,
    PaginationComponent
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

  // Bộ lọc
  public selectedProductFilter = '';
  public selectedStatusFilter = '';

  public readonly currentPage = signal<number>(1);
  public readonly limit = 15;

  // Lựa chọn trạng thái để lọc
  public statusFilterOptions = [
    { label: 'Tất cả trạng thái', value: '' },
    { label: 'Sẵn sàng (Available)', value: 'available' },
    { label: 'Tạm giữ (Reserved)', value: 'reserved' },
    { label: 'Đã bán (Sold)', value: 'sold' }
  ];

  // Map danh sách sản phẩm cho Select
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
    // Tải sản phẩm trước
    this.state.productStore.fetchProducts().subscribe(() => {
      // Sau đó tải tất cả items kho
      this.fetchFilteredItems(1);
    });
  }

  public fetchFilteredItems(page: number = 1) {
    this.currentPage.set(page);
    this.state.inventoryStore.fetchItems({
      product_id: this.selectedProductFilter || undefined,
      status: this.selectedStatusFilter || undefined
    }, page, this.limit).subscribe();
  }

  public fetchPage(page: number) {
    this.fetchFilteredItems(page);
  }


  public getProductName(productId: number): string {
    const product = this.state.productStore.products().find(p => p.id === productId);
    return product ? product.name : `Sản phẩm #${productId}`;
  }

  public openImportDrawer() {
    this.modalService.open(ImportInventoryModalComponent, {
      title: 'Import Tài Nguyên Số',
      size: 'md',
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
}
