import { Component, inject, OnInit, signal } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { ModalRef, MODAL_DATA } from '@core/services/modal-ref';
import { StateService } from '@core/services/state.service';
import { CustomSelectComponent } from '@shared/components/custom-select/custom-select.component';
import { CustomInputComponent } from '@shared/components/custom-input/custom-input.component';
import { ButtonComponent } from '@shared/components/button/button.component';
import { TranslatePipe } from '@shared/pipes/translate.pipe';
import { LanguageService } from '@core/services/language.service';
import { finalize } from 'rxjs/operators';

@Component({
  selector: 'app-edit-inventory-modal',
  standalone: true,
  imports: [
    CommonModule,
    FormsModule,
    CustomSelectComponent,
    CustomInputComponent,
    ButtonComponent,
    TranslatePipe
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
  public readonly lang = inject(LanguageService);

  public itemId = '';
  public editProductId = '';
  public batchName = '';
  public keysPool = '';
  public status = 'active';
  public lastUpdatedAt = '';
  public productOptions: any[] = [];
  public readonly isSubmitting = signal(false);

  public get statusOptions() {
    return [
      { label: this.lang.translate('status.active'), value: 'active' },
      { label: this.lang.translate('inventory.status_completed'), value: 'completed' },
      { label: this.lang.translate('inventory.status_inactive'), value: 'inactive' }
    ];
  }

  ngOnInit() {
    if (this.modalData) {
      this.itemId = this.modalData.id;
      this.editProductId = this.modalData.product_id;
      this.batchName = this.modalData.batch_name || '';
      this.keysPool = this.modalData.keys_pool || '';
      this.status = this.modalData.status || 'active';
      this.lastUpdatedAt = this.modalData.last_updated_at || '';
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
    if (this.isSubmitting()) return;
    if (!this.editProductId || !this.batchName.trim()) {
      this.state.showToast(this.lang.translate('toast.edit_inventory_validation'), 'error');
      return;
    }

    this.isSubmitting.set(true);
    this.state.inventoryStore.updateItem(this.itemId, {
      product_id: this.editProductId,
      batch_name: this.batchName,
      keys_pool: this.keysPool,
      status: this.status,
      last_updated_at: this.lastUpdatedAt
    }).pipe(
      finalize(() => this.isSubmitting.set(false))
    ).subscribe((res) => {
      if (res) {
        this.modalRef.close(true);
      }
    });
  }
}
