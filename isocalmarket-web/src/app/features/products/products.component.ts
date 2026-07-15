import { Component, OnInit, inject, signal } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { StateService } from '@core/services/state.service';
import { SkeletonLoaderComponent } from '@shared/components/skeleton-loader/skeleton-loader.component';
import { CustomInputComponent } from '@shared/components/custom-input/custom-input.component';
import { CustomSelectComponent } from '@shared/components/custom-select/custom-select.component';
import { ButtonComponent } from '@shared/components/button/button.component';
import { ModalService } from '@core/services/modal.service';

@Component({
  selector: 'app-products',
  standalone: true,
  imports: [
    CommonModule,
    FormsModule,
    SkeletonLoaderComponent,
    CustomInputComponent,
    CustomSelectComponent,
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

  // Trạng thái Drawer
  public showDrawer = false;
  public isEditMode = false;

  // Form Fields
  public editingId: number | null = null;
  public name = '';
  public slug = '';
  public description = '';
  public price = 0;
  public status = 'active';

  // Lựa chọn trạng thái sản phẩm
  public statusOptions = [
    { label: 'Hoạt động (Active)', value: 'active' },
    { label: 'Tạm ngưng (Inactive)', value: 'inactive' }
  ];

  ngOnInit() {
    this.state.productStore.fetchProducts().subscribe();
  }

  public openCreateDrawer() {
    this.isEditMode = false;
    this.editingId = null;
    this.name = '';
    this.slug = '';
    this.description = '';
    this.price = 0;
    this.status = 'active';
    this.showDrawer = true;
  }

  public openEditDrawer(product: any) {
    this.isEditMode = true;
    this.editingId = product.id;
    this.name = product.name;
    this.slug = product.slug;
    this.description = product.description || '';
    this.price = product.price;
    this.status = product.status;
    this.showDrawer = true;
  }

  public closeDrawer() {
    this.showDrawer = false;
  }

  public onSubmit() {
    if (!this.name || this.price < 0) {
      this.state.showToast('Vui lòng điền thông tin hợp lệ!', 'error');
      return;
    }

    const data = {
      name: this.name,
      slug: this.slug || undefined,
      description: this.description,
      price: this.price,
      status: this.status
    };

    if (this.isEditMode && this.editingId) {
      this.state.productStore.updateProduct(this.editingId, data).subscribe((res) => {
        if (res !== null) this.closeDrawer();
      });
    } else {
      this.state.productStore.createProduct(data).subscribe((res) => {
        if (res !== null) this.closeDrawer();
      });
    }
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
