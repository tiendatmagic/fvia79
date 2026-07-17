import { Component, inject, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { ModalRef, MODAL_DATA } from '@core/services/modal-ref';
import { StateService } from '@core/services/state.service';
import { CustomSelectComponent } from '@shared/components/custom-select/custom-select.component';
import { ButtonComponent } from '@shared/components/button/button.component';
import { finalize } from 'rxjs/operators';

@Component({
  selector: 'app-edit-inventory-modal',
  standalone: true,
  imports: [
    CommonModule,
    FormsModule,
    CustomSelectComponent,
    ButtonComponent
  ],
  templateUrl: './edit-inventory-modal.component.html',
  styles: [`
    :host {
      display: block;
    }
  `]
})
export class EditInventoryModalComponent implements OnInit {
  private readonly modalRef = inject(ModalRef);
  private readonly modalData = inject(MODAL_DATA);
  public readonly state = inject(StateService);

  public itemId = '';
  public editProductId = '';
  public rawData = '';
  public status = 'available';
  public productOptions: any[] = [];
  public isSubmitting = false;

  public statusOptions = [
    { label: 'Sẵn có', value: 'available' },
    { label: 'Tạm giữ', value: 'reserved' },
    { label: 'Đã bán', value: 'sold' }
  ];

  ngOnInit() {
    if (this.modalData && this.modalData.item) {
      const item = this.modalData.item;
      this.itemId = item.id;
      this.editProductId = String(item.product_id);
      this.rawData = item.data || '';
      this.status = item.status || 'available';
      this.productOptions = this.modalData.productOptions || [];
    }
  }

  public onClose() {
    this.modalRef.close(false);
  }

  public onSubmit() {
    if (this.isSubmitting) return;
    if (!this.editProductId || !this.rawData.trim()) {
      this.state.showToast('Vui lòng chọn sản phẩm và nhập dữ liệu tài nguyên!', 'error');
      return;
    }

    this.isSubmitting = true;
    this.state.inventoryStore.updateItem(this.itemId, {
      product_id: this.editProductId,
      data: this.rawData,
      status: this.status
    }).pipe(
      finalize(() => this.isSubmitting = false)
    ).subscribe((res) => {
      if (res) {
        this.modalRef.close(true);
      }
    });
  }
}
