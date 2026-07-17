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
  public batchName = '';
  public keysPool = '';
  public status = 'active';
  public lastUpdatedAt = '';
  public productOptions: any[] = [];
  public isSubmitting = false;

  public statusOptions = [
    { label: 'Hoạt động', value: 'active' },
    { label: 'Hoàn thành (Hết hàng)', value: 'completed' },
    { label: 'Tạm khóa', value: 'inactive' }
  ];

  ngOnInit() {
    if (this.modalData && this.modalData.item) {
      const item = this.modalData.item;
      this.itemId = item.id;
      this.editProductId = String(item.product_id);
      this.batchName = item.batch_name || '';
      this.keysPool = item.keys_pool || '';
      this.status = item.status || 'active';
      this.lastUpdatedAt = item.updated_at || '';
      this.productOptions = this.modalData.productOptions || [];
    }
  }

  public onClose() {
    this.modalRef.close(false);
  }

  public get keysCount(): number {
    if (!this.keysPool) return 0;
    const lines = this.keysPool.split('\n');
    return lines.filter(line => line.trim().length > 0).length;
  }

  public onSubmit() {
    if (this.isSubmitting) return;
    if (!this.editProductId || !this.batchName.trim()) {
      this.state.showToast('Vui lòng chọn sản phẩm và nhập tên lô hàng!', 'error');
      return;
    }

    this.isSubmitting = true;
    this.state.inventoryStore.updateItem(this.itemId, {
      product_id: this.editProductId,
      batch_name: this.batchName,
      keys_pool: this.keysPool,
      status: this.status,
      last_updated_at: this.lastUpdatedAt
    }).pipe(
      finalize(() => this.isSubmitting = false)
    ).subscribe((res) => {
      if (res) {
        this.modalRef.close(true);
      }
    });
  }
}
