import { Component, OnInit, inject, signal, computed } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { StateService } from '@core/services/state.service';
import { SkeletonLoaderComponent } from '@shared/components/skeleton-loader/skeleton-loader.component';
import { ButtonComponent } from '@shared/components/button/button.component';
import { BadgeComponent } from '@shared/components/badge/badge.component';
import { CardComponent } from '@shared/components/card/card.component';
import { PageHeaderComponent } from '@shared/components/page-header/page-header.component';
import { IconComponent } from '@shared/components/icon/icon.component';
import { CustomSearchInputComponent } from '@shared/components/custom-search-input/custom-search-input.component';
import { TooltipDirective } from '@shared/components/tooltip/tooltip.directive';
import { ModalService } from '@core/services/modal.service';
import { ProductFormModalComponent } from './product-form-modal/product-form-modal.component';

@Component({
  selector: 'app-products',
  standalone: true,
  imports: [
    CommonModule,
    FormsModule,
    SkeletonLoaderComponent,
    ButtonComponent,
    BadgeComponent,
    CardComponent,
    PageHeaderComponent,
    IconComponent,
    CustomSearchInputComponent,
    TooltipDirective,
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

  public searchQuery = '';
  private readonly _searchQuery = signal('');

  public readonly filteredProducts = computed(() => {
    const q = this._searchQuery().trim().toLowerCase();
    const products = this.state.productStore.products();
    if (!q) return products;
    return products.filter(p =>
      p.name?.toLowerCase().includes(q) ||
      p.slug?.toLowerCase().includes(q)
    );
  });

  ngOnInit() {
    this.state.productStore.fetchProducts().subscribe();
  }

  public onSearch(query: string) {
    this._searchQuery.set(query);
  }

  public openCreateDrawer() {
    this.modalService.open(ProductFormModalComponent, {
      title: 'Thêm Sản Phẩm Mới',
      size: 'md',
      data: null
    }).afterClosed$.subscribe((success) => {
      if (success) {
        this.state.productStore.fetchProducts().subscribe();
      }
    });
  }

  public openEditDrawer(product: any) {
    this.modalService.open(ProductFormModalComponent, {
      title: 'Cập Nhật Sản Phẩm',
      size: 'md',
      data: product
    }).afterClosed$.subscribe((success) => {
      if (success) {
        this.state.productStore.fetchProducts().subscribe();
      }
    });
  }

  public confirmDelete(product: any) {
    this.modalService.confirm({
      title: 'Xác nhận xóa sản phẩm',
      description: `Bạn có chắc chắn muốn xóa sản phẩm '${product.name}'? Hành động này không thể hoàn tác.`,
      confirmText: 'Xóa sản phẩm',
      cancelText: 'Hủy',
      confirmButtonClass: 'btn-danger'
    }).afterClosed$.subscribe((confirmed?: boolean) => {
      if (confirmed) {
        this.state.productStore.deleteProduct(product.id).subscribe((res) => {
          if (res !== null) {
            this.state.productStore.fetchProducts().subscribe();
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
