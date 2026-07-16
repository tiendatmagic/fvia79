import { Component, inject, OnInit, computed } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { ModalRef, MODAL_DATA } from '@core/services/modal-ref';
import { StateService } from '@core/services/state.service';
import { CustomInputComponent } from '@shared/components/custom-input/custom-input.component';
import { CustomSelectComponent } from '@shared/components/custom-select/custom-select.component';
import { ButtonComponent } from '@shared/components/button/button.component';
import { IconComponent } from '@shared/components/icon/icon.component';
import { finalize } from 'rxjs/operators';

@Component({
  selector: 'app-product-form-modal',
  standalone: true,
  imports: [
    CommonModule,
    FormsModule,
    CustomInputComponent,
    CustomSelectComponent,
    ButtonComponent,
    IconComponent
  ],
  templateUrl: './product-form-modal.component.html',
  styles: [`
    :host {
      display: block;
    }
  `]
})
export class ProductFormModalComponent implements OnInit {
  private readonly modalRef = inject(ModalRef);
  private readonly modalData = inject(MODAL_DATA);
  public readonly state = inject(StateService);

  public isEditMode = false;
  public editingId: any = null; // Hỗ trợ cả string UUID và number
  public categoryId = '';
  public name = '';
  public slug = '';
  public description = '';
  public price = 0;
  public status = 'active';
  public isSubmitting = false;
  public attributes: { label: string, value: string }[] = [];

  public statusOptions = [
    { label: 'Hoạt động', value: 'active' },
    { label: 'Ngưng hoạt động', value: 'inactive' }
  ];

  public categoryOptions = computed(() => {
    const list = this.state.productStore.categories();
    return [
      { label: '-- Không thuộc danh mục nào --', value: '' },
      ...list.map(cat => ({ label: cat.name, value: cat.id }))
    ];
  });

  ngOnInit() {
    this.state.productStore.fetchCategories().subscribe();
    if (this.modalData) {
      this.isEditMode = true;
      this.editingId = this.modalData.id;
      this.categoryId = this.modalData.category_id || '';
      this.name = this.modalData.name;
      this.slug = this.modalData.slug;
      this.description = this.modalData.description || '';
      this.price = this.modalData.price;
      this.status = this.modalData.status;
      this.attributes = this.modalData.attributes ? JSON.parse(JSON.stringify(this.modalData.attributes)) : [];
    }
  }

  public addAttribute() {
    this.attributes.push({ label: '', value: '' });
  }

  public removeAttribute(index: number) {
    this.attributes.splice(index, 1);
  }

  public onClose() {
    this.modalRef.close(false);
  }

  public onSubmit() {
    if (this.isSubmitting) return;
    if (!this.name || this.price < 0) {
      this.state.showToast('Vui lòng điền thông tin hợp lệ!', 'error');
      return;
    }

    const data = {
      name: this.name,
      slug: this.slug || undefined,
      description: this.description,
      price: this.price,
      status: this.status,
      category_id: this.categoryId || null,
      attributes: this.attributes.filter(attr => attr.label.trim() !== '' && attr.value.trim() !== '')
    };

    this.isSubmitting = true;
    if (this.isEditMode && this.editingId) {
      this.state.productStore.updateProduct(this.editingId, data).pipe(
        finalize(() => this.isSubmitting = false)
      ).subscribe((res) => {
        if (res !== null) {
          this.modalRef.close(true);
        }
      });
    } else {
      this.state.productStore.createProduct(data).pipe(
        finalize(() => this.isSubmitting = false)
      ).subscribe((res) => {
        if (res !== null) {
          this.modalRef.close(true);
        }
      });
    }
  }
}
