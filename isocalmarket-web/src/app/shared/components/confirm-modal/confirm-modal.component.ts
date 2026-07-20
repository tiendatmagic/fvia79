import { Component, Input, Output, EventEmitter, Inject, Optional, OnInit, inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { IconComponent } from '@shared/components/icon/icon.component';
import { ButtonComponent } from '@shared/components/button/button.component';
import { MODAL_DATA, ModalRef } from '@core/services/modal-ref';
import { LanguageService } from '@core/services/language.service';

@Component({
  selector: 'app-confirm-modal',
  standalone: true,
  imports: [CommonModule, IconComponent, ButtonComponent],
  templateUrl: './confirm-modal.component.html'})
export class ConfirmModalComponent implements OnInit {
  public readonly lang = inject(LanguageService);

  @Input() isOpen = false;
  @Input() title = '';
  @Input() description = '';
  @Input() descriptionHtml = '';
  @Input() confirmText = '';
  @Input() cancelText = '';
  @Input() confirmButtonClass = 'btn-danger';
  @Input() isSubmitting = false;
  @Input() variant: 'success' | 'warning' | 'danger' | 'info' = 'danger';

  @Output() confirm = new EventEmitter<void>();
  @Output() cancel = new EventEmitter<void>();

  isDynamic = false;

  constructor(
    @Optional() @Inject(MODAL_DATA) private modalData: any,
    @Optional() private modalRef: ModalRef<boolean>
  ) {
    if (this.modalRef) {
      this.isDynamic = true;
    }
  }

  ngOnInit(): void {
    if (!this.title) this.title = this.lang.translate('modal.confirm_title');
    if (!this.description) this.description = this.lang.translate('modal.confirm_desc');
    if (!this.confirmText) this.confirmText = this.lang.translate('action.confirm');
    if (!this.cancelText) this.cancelText = this.lang.translate('action.cancel');

    if (this.isDynamic && this.modalData) {
      this.title = this.modalData.title || this.title;
      this.description = this.modalData.description || this.description;
      this.descriptionHtml = this.modalData.descriptionHtml || this.descriptionHtml;
      this.confirmText = this.modalData.confirmText || this.confirmText;
      this.cancelText = this.modalData.cancelText || this.cancelText;
      this.confirmButtonClass = this.modalData.confirmButtonClass || this.confirmButtonClass;
      this.isSubmitting = this.modalData.isSubmitting || this.isSubmitting;
      this.variant = this.modalData.variant || this.variant;
    }
  }

  onConfirm(): void {
    if (this.isSubmitting) return;
    if (this.isDynamic && this.modalRef) {
      this.modalRef.close(true);
    } else {
      this.confirm.emit();
    }
  }

  onCancel(): void {
    if (this.isSubmitting) return;
    if (this.isDynamic && this.modalRef) {
      this.modalRef.close(false);
    } else {
      this.cancel.emit();
    }
  }

  get confirmVariant():
    | 'primary'
    | 'secondary'
    | 'success'
    | 'danger'
    | 'danger-light'
    | 'cancel'
    | 'info' {
    if (this.confirmButtonClass.includes('danger')) return 'danger';
    if (this.confirmButtonClass.includes('secondary')) return 'secondary';
    if (this.confirmButtonClass.includes('success')) return 'success';
    if (this.confirmButtonClass.includes('cancel')) return 'cancel';
    return 'primary';
  }
}
