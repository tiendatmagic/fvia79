import { Component, OnInit, inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { StateService } from '@core/services/state.service';
import { SkeletonLoaderComponent } from '@shared/components/skeleton-loader/skeleton-loader.component';
import { ButtonComponent } from '@shared/components/button/button.component';
import { ModalService } from '@core/services/modal.service';
import { ProductFormModalComponent } from './product-form-modal/product-form-modal.component';

@Component({
  selector: 'app-products',
  standalone: true,
  imports: [
    CommonModule,
    SkeletonLoaderComponent,
    ButtonComponent
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

  ngOnInit() {
    this.state.productStore.fetchProducts().subscribe();
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
        this.state.productStore.deleteProduct(product.id).subscribe();
      }
    });
  }
}
