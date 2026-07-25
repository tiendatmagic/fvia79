import { Component, inject, OnInit, signal, ChangeDetectorRef, NgZone } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { ModalRef, MODAL_DATA } from '@core/services/modal-ref';
import { StateService } from '@core/services/state.service';
import { CustomSelectComponent } from '@shared/components/custom-select/custom-select.component';
import { CustomInputComponent } from '@shared/components/custom-input/custom-input.component';
import { ButtonComponent } from '@shared/components/button/button.component';
import { IconComponent } from '@shared/components/icon/icon.component';
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
    IconComponent,
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
  private readonly cdr = inject(ChangeDetectorRef);
  private readonly zone = inject(NgZone);

  public itemId = '';
  public editProductId = '';
  public batchName = '';
  public costPrice: number | null = null;
  public keysPool = '';
  public status = 'active';
  public lastUpdatedAt = '';
  public productOptions: any[] = [];
  public readonly isSubmitting = signal(false);
  public importedFileName = '';
  public importedLineCount = 0;

  public get statusOptions() {
    return [
      { label: this.lang.translate('status.active'), value: 'active' },
      { label: this.lang.translate('inventory.status_completed'), value: 'completed' },
      { label: this.lang.translate('inventory.status_inactive'), value: 'inactive' }
    ];
  }

  ngOnInit() {
    if (this.modalData) {
      const item = this.modalData.item ?? this.modalData;

      this.itemId = String(item.id ?? '');
      this.editProductId = String(item.product_id ?? '');
      this.batchName = item.batch_name || '';
      this.costPrice = item.cost_price !== null && item.cost_price !== undefined ? Number(item.cost_price) : null;
      this.keysPool = item.keys_pool || '';
      this.status = item.status || 'active';
      this.lastUpdatedAt = item.updated_at || item.last_updated_at || '';
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

  public onTxtFileSelected(event: Event) {
    const input = event.target as HTMLInputElement;
    const file = input?.files?.[0];
    if (!file) return;

    if (!file.name.toLowerCase().endsWith('.txt')) {
      this.state.showToast(this.lang.translate('inventory.txt_invalid_format'), 'error');
      input.value = '';
      return;
    }

    const reader = new FileReader();
    reader.onload = () => {
      this.zone.run(() => {
        const content = (reader.result as string) || '';
        const lines = content.split(/\r?\n/).filter(line => line.trim().length > 0);

        if (lines.length === 0) {
          this.state.showToast(this.lang.translate('inventory.txt_empty'), 'error');
          input.value = '';
          return;
        }

        if (this.keysPool.trim().length > 0) {
          this.keysPool = this.keysPool.trimEnd() + '\n' + lines.join('\n');
        } else {
          this.keysPool = lines.join('\n');
        }

        this.importedFileName = file.name;
        this.importedLineCount = lines.length;
        this.state.showToast(
          this.lang.translate('inventory.txt_import_success').replace('{{count}}', String(lines.length)),
          'success'
        );

        input.value = '';
        this.cdr.markForCheck();
      });
    };

    reader.onerror = () => {
      this.zone.run(() => {
        this.state.showToast(this.lang.translate('inventory.txt_read_error'), 'error');
        input.value = '';
        this.cdr.markForCheck();
      });
    };

    reader.readAsText(file, 'UTF-8');
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
      cost_price: this.costPrice !== null && this.costPrice !== undefined ? Number(this.costPrice) : null,
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
