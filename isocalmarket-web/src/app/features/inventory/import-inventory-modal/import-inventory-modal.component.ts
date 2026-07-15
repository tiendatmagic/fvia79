import { Component, inject, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { ModalRef, MODAL_DATA } from '@core/services/modal-ref';
import { StateService } from '@core/services/state.service';
import { CustomSelectComponent } from '@shared/components/custom-select/custom-select.component';
import { ButtonComponent } from '@shared/components/button/button.component';
import { finalize } from 'rxjs/operators';

@Component({
  selector: 'app-import-inventory-modal',
  standalone: true,
  imports: [
    CommonModule,
    FormsModule,
    CustomSelectComponent,
    ButtonComponent
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

  public importProductId = '';
  public rawData = '';
  public productOptions: any[] = [];
  public isSubmitting = false;

  ngOnInit() {
    if (this.modalData) {
      this.importProductId = this.modalData.selectedProductId || '';
      this.productOptions = this.modalData.productOptions || [];
    }
  }

  public onClose() {
    this.modalRef.close(false);
  }

  public onSubmit() {
    if (this.isSubmitting) return;
    if (!this.importProductId || !this.rawData.trim()) {
      this.state.showToast('Vui lòng chọn sản phẩm và nhập dữ liệu tài nguyên!', 'error');
      return;
    }

    this.isSubmitting = true;
    this.state.inventoryStore.importItems(
      this.importProductId as any,
      this.rawData
    ).pipe(
      finalize(() => this.isSubmitting = false)
    ).subscribe((res) => {
      this.modalRef.close(true);
    });
  }
}
