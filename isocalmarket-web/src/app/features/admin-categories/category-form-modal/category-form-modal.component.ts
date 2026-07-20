import { Component, inject, OnInit, signal } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { ModalRef, MODAL_DATA } from '@core/services/modal-ref';
import { StateService } from '@core/services/state.service';
import { CustomInputComponent } from '@shared/components/custom-input/custom-input.component';
import { ButtonComponent } from '@shared/components/button/button.component';
import { TranslatePipe } from '@shared/pipes/translate.pipe';
import { LanguageService } from '@core/services/language.service';
import { finalize } from 'rxjs/operators';

@Component({
  selector: 'app-category-form-modal',
  standalone: true,
  imports: [
    CommonModule,
    FormsModule,
    CustomInputComponent,
    ButtonComponent,
    TranslatePipe
  ],
  templateUrl: './category-form-modal.component.html',
  styles: [`
    :host {
      display: block;
    }
  `]
})
export class CategoryFormModalComponent implements OnInit {
  private readonly modalRef = inject(ModalRef);
  private readonly modalData = inject(MODAL_DATA);
  public readonly state = inject(StateService);
  public readonly lang = inject(LanguageService);

  public isEditMode = false;
  public editingId: any = null;
  public name = '';
  public slug = '';
  public description = '';
  public readonly isSubmitting = signal(false);

  ngOnInit() {
    if (this.modalData) {
      this.isEditMode = true;
      this.editingId = this.modalData.id;
      this.name = this.modalData.name;
      this.slug = this.modalData.slug;
      this.description = this.modalData.description || '';
    }
  }

  public onClose() {
    this.modalRef.close(false);
  }

  public onSubmit() {
    if (this.isSubmitting()) return;
    if (!this.name) {
      this.state.showToast(this.lang.translate('toast.category_name_required'), 'error');
      return;
    }

    const data = {
      name: this.name,
      slug: this.slug || undefined,
      description: this.description || null
    };

    this.isSubmitting.set(true);
    if (this.isEditMode && this.editingId) {
      this.state.productStore.updateCategory(this.editingId, data).pipe(
        finalize(() => this.isSubmitting.set(false))
      ).subscribe((res) => {
        if (res !== null) {
          this.modalRef.close(true);
        }
      });
    } else {
      this.state.productStore.createCategory(data).pipe(
        finalize(() => this.isSubmitting.set(false))
      ).subscribe((res) => {
        if (res !== null) {
          this.modalRef.close(true);
        }
      });
    }
  }
}
