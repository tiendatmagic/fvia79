import { Component, inject, OnInit, computed, signal } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { ModalRef, MODAL_DATA } from '@core/services/modal-ref';
import { StateService } from '@core/services/state.service';
import { CustomInputComponent } from '@shared/components/custom-input/custom-input.component';
import { CustomSelectComponent } from '@shared/components/custom-select/custom-select.component';
import { CustomSwitchComponent } from '@shared/components/custom-switch/custom-switch.component';
import { ButtonComponent } from '@shared/components/button/button.component';
import { IconComponent } from '@shared/components/icon/icon.component';
import { TranslatePipe } from '@shared/pipes/translate.pipe';
import { LanguageService } from '@core/services/language.service';
import { finalize } from 'rxjs/operators';

@Component({
  selector: 'app-product-form-modal',
  standalone: true,
  imports: [
    CommonModule,
    FormsModule,
    CustomInputComponent,
    CustomSelectComponent,
    CustomSwitchComponent,
    ButtonComponent,
    IconComponent,
    TranslatePipe
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
  public readonly lang = inject(LanguageService);

  public isEditMode = false;
  public editingId: any = null;
  public categoryId = '';
  public name = '';
  public nameVi = '';
  public nameEn = '';
  public slug = '';
  public description = '';
  public descriptionVi = '';
  public descriptionEn = '';
  public price = 0;
  public isManualPrice = false;
  public manualPrice: number | null = null;
  public manualPriceCurrency = 'VND';
  public status = 'active';
  public readonly isSubmitting = signal(false);
  public attributes: { label: string, value: string }[] = [];

  public get currencyUnitOptions() {
    return [
      { label: 'VND (Đồng)', value: 'VND' },
      { label: 'USDT ($)', value: 'USDT' }
    ];
  }

  public get statusOptions() {
    return [
      { label: this.lang.translate('common.active'), value: 'active' },
      { label: this.lang.translate('common.inactive'), value: 'inactive' }
    ];
  }

  public categoryOptions = computed(() => {
    const list = this.state.productStore.categories();
    return [
      { label: `-- ${this.lang.translate('common.all_categories')} --`, value: '' },
      ...list.map(cat => ({ label: cat.name, value: cat.id }))
    ];
  });

  ngOnInit() {
    this.state.productStore.fetchCategories().subscribe();
    if (this.modalData) {
      if (this.modalData.is_deleted) {
        this.state.showToast(this.lang.translate('toast.copy_error'), 'error');
        this.modalRef.close(false);
        return;
      }
      this.isEditMode = true;
      this.editingId = this.modalData.id;
      this.categoryId = this.modalData.category_id || '';
      this.name = this.modalData.name || '';
      this.nameVi = this.modalData.name_vi || this.modalData.name || '';
      this.nameEn = this.modalData.name_en || this.modalData.name || '';
      this.slug = this.modalData.slug || '';
      this.description = this.modalData.description || '';
      this.descriptionVi = this.modalData.description_vi || this.modalData.description || '';
      this.descriptionEn = this.modalData.description_en || this.modalData.description || '';
      this.price = this.modalData.price || 0;
      this.isManualPrice = !!this.modalData.is_manual_price;
      this.manualPrice = this.modalData.manual_price !== null && this.modalData.manual_price !== undefined
        ? Number(this.modalData.manual_price)
        : (this.isManualPrice ? Number(this.modalData.price || 0) : null);
      this.manualPriceCurrency = this.modalData.manual_price_currency || 'VND';
      this.status = this.modalData.status || 'active';
      if (Array.isArray(this.modalData.attributes)) {
        this.attributes = [...this.modalData.attributes];
      } else if (this.modalData.attributes && typeof this.modalData.attributes === 'object') {
        this.attributes = Object.entries(this.modalData.attributes).map(([label, value]) => ({
          label,
          value: String(value ?? '')
        }));
      } else if (typeof this.modalData.attributes === 'string') {
        try {
          const parsed = JSON.parse(this.modalData.attributes);
          if (Array.isArray(parsed)) {
            this.attributes = parsed;
          } else if (parsed && typeof parsed === 'object') {
            this.attributes = Object.entries(parsed).map(([label, value]) => ({
              label,
              value: String(value ?? '')
            }));
          } else {
            this.attributes = [];
          }
        } catch {
          this.attributes = [];
        }
      } else {
        this.attributes = [];
      }
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
    if (this.isSubmitting()) return;
    const finalNameEn = this.nameEn.trim() || this.nameVi.trim() || this.name.trim();
    let numPrice = Number(this.price);
    let numManualPrice: number | null = null;

    if (this.isManualPrice) {
      numManualPrice = Number(this.manualPrice);
      if (this.manualPrice === null || this.manualPrice === undefined || isNaN(numManualPrice) || numManualPrice < 0) {
        this.state.showToast(this.lang.translate('toast.invalid_info'), 'error');
        return;
      }
      numPrice = numManualPrice;
    } else {
      if (this.price === null || this.price === undefined || isNaN(numPrice) || numPrice < 0) {
        this.state.showToast(this.lang.translate('toast.invalid_info'), 'error');
        return;
      }
    }

    if (!finalNameEn) {
      this.state.showToast(this.lang.translate('toast.invalid_info'), 'error');
      return;
    }

    const finalNameVi = this.nameVi.trim() || finalNameEn;
    const finalDescEn = this.descriptionEn.trim() || this.descriptionVi.trim() || this.description.trim() || null;
    const finalDescVi = this.descriptionVi.trim() || (finalDescEn ?? null);

    const data = {
      name: finalNameEn,
      name_en: finalNameEn,
      name_vi: finalNameVi,
      slug: this.slug?.trim() || undefined,
      description: finalDescEn,
      description_en: finalDescEn,
      description_vi: finalDescVi,
      price: numPrice,
      is_manual_price: this.isManualPrice,
      manual_price: this.isManualPrice ? numManualPrice : null,
      manual_price_currency: this.isManualPrice ? this.manualPriceCurrency : 'VND',
      status: this.status,
      category_id: this.categoryId || null,
      attributes: this.attributes.filter(attr => attr.label.trim() !== '' && attr.value.trim() !== '')
    };

    this.isSubmitting.set(true);
    if (this.isEditMode && this.editingId) {
      this.state.productStore.updateProduct(this.editingId, data).pipe(
        finalize(() => this.isSubmitting.set(false))
      ).subscribe((res) => {
        if (res !== null) {
          this.modalRef.close(true);
        }
      });
    } else {
      this.state.productStore.createProduct(data).pipe(
        finalize(() => this.isSubmitting.set(false))
      ).subscribe((res) => {
        if (res !== null) {
          this.modalRef.close(true);
        }
      });
    }
  }
}
