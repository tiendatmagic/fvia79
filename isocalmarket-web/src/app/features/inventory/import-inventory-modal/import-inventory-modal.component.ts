import { Component, inject, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { ModalRef, MODAL_DATA } from '@core/services/modal-ref';
import { StateService } from '@core/services/state.service';
import { CustomSelectComponent } from '@shared/components/custom-select/custom-select.component';
import { ButtonComponent } from '@shared/components/button/button.component';

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
    if (!this.importProductId || !this.rawData.trim()) {
      this.state.showToast('Vui lòng chọn sản phẩm và nhập dữ liệu tài nguyên!', 'error');
      return;
    }

    // Truyền trực tiếp importProductId dạng string/any để tránh lỗi NaN khi dùng UUID
    this.state.inventoryStore.importItems(
      this.importProductId as any,
      this.rawData
    ).subscribe((res) => {
      this.modalRef.close(true);
    });
  }
}
