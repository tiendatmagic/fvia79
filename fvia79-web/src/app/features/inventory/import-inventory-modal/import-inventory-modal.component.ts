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
  selector: 'app-import-inventory-modal',
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
  templateUrl: './import-inventory-modal.component.html',
  styles: [`
    :host {
      display: block;
    }
  `]
})
export class ImportInventoryModalComponent implements OnInit {
  private readonly modalRef = inject(ModalRef);
  private readonly modalData = inject(MODAL_DATA);
  public readonly state = inject(StateService);
  public readonly lang = inject(LanguageService);
  private readonly cdr = inject(ChangeDetectorRef);
  private readonly zone = inject(NgZone);

  public importProductId = '';
  public batchName = '';
  public costPrice: number | null = null;
  public rawData = '';
  public productOptions: any[] = [];
  public readonly isSubmitting = signal(false);
  public importedFileName = '';
  public importedLineCount = 0;

  public ngOnInit() {
    const targetProductId = this.modalData?.productId || this.modalData?.selectedProductId || '';
    if (targetProductId) {
      this.importProductId = String(targetProductId);
    }

    if (this.modalData?.productOptions && this.modalData.productOptions.length > 0) {
      this.productOptions = this.modalData.productOptions;
    } else {
      const options = this.state.productStore.products().map((p: any) => ({
        label: p.name,
        value: String(p.id)
      }));
      this.productOptions = [{ label: this.lang.translate('modal.select_product'), value: '' }, ...options];
    }

    if (this.productOptions.length <= 1) {
      this.state.productStore.fetchProducts().subscribe({
        next: (products: any[]) => {
          const list = (products || []).map((p: any) => ({
            label: p.name,
            value: String(p.id)
          }));
          this.productOptions = [{ label: this.lang.translate('modal.select_product'), value: '' }, ...list];
          if (this.importProductId) {
            this.onProductSelect(this.importProductId);
          }
        }
      });
    } else {
      if (this.importProductId) {
        this.onProductSelect(this.importProductId);
      }
    }
  }

  public onProductSelect(productId: any) {
    this.importProductId = String(productId || '');
    if (!this.importProductId) {
      return;
    }
    const selected = this.productOptions.find((p) => String(p.value) === String(this.importProductId));
    if (selected && selected.label && selected.value !== '') {
      this.batchName = this.generateDefaultBatchName(selected.label);
    }
  }

  private generateDefaultBatchName(productName: string): string {
    const now = new Date();
    const day = now.getDate();
    const month = now.getMonth() + 1;
    const year = now.getFullYear();
    const hours = String(now.getHours()).padStart(2, '0');
    const minutes = String(now.getMinutes()).padStart(2, '0');
    return `${productName} - ${day}/${month}/${year} ${hours}:${minutes}`;
  }

  public onClose() {
    this.modalRef.close(false);
  }

  public get rawDataCount(): number {
    if (!this.rawData) return 0;
    const lines = this.rawData.split('\n');
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

        if (this.rawData.trim().length > 0) {
          this.rawData = this.rawData.trimEnd() + '\n' + lines.join('\n');
        } else {
          this.rawData = lines.join('\n');
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
    if (!this.importProductId || !this.batchName.trim() || !this.rawData.trim()) {
      this.state.showToast(this.lang.translate('toast.import_validation'), 'error');
      return;
    }

    this.isSubmitting.set(true);
    this.state.inventoryStore.importItems(
      this.importProductId as any,
      this.batchName,
      this.rawData,
      this.costPrice !== null && this.costPrice !== undefined ? Number(this.costPrice) : null
    ).pipe(
      finalize(() => this.isSubmitting.set(false))
    ).subscribe((res) => {
      if (res) {
        this.modalRef.close(true);
      }
    });
  }
}
