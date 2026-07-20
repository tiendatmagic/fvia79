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
  selector: 'app-import-inventory-modal',
  standalone: true,
  imports: [
    CommonModule,
    FormsModule,
    CustomSelectComponent,
    CustomInputComponent,
    ButtonComponent,
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

  public importProductId = '';
  public batchName = '';
  public rawData = '';
  public productOptions: any[] = [];
  public readonly isSubmitting = signal(false);

  public ngOnInit() {
    this.state.productStore.getProductsRaw(1, 1000).subscribe((res: any) => {
      const list = (res?.data || res || []).map((p: any) => ({
        label: p.name,
        value: p.id
      }));
      this.productOptions = list;
    });

    if (this.modalData && this.modalData.productId) {
      this.importProductId = this.modalData.productId;
    }
  }

  public onClose() {
    this.modalRef.close(false);
  }

  public get rawDataCount(): number {
    if (!this.rawData) return 0;
    const lines = this.rawData.split('\n');
    return lines.filter(line => line.trim().length > 0).length;
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
      this.rawData
    ).pipe(
      finalize(() => this.isSubmitting.set(false))
    ).subscribe((res) => {
      if (res) {
        this.modalRef.close(true);
      }
    });
  }
}
