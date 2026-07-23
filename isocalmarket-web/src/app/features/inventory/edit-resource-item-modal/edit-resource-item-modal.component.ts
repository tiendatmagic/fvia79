import { Component, OnInit, inject, signal } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { ModalRef, MODAL_DATA } from '@core/services/modal-ref';
import { StateService } from '@core/services/state.service';
import { LanguageService } from '@core/services/language.service';
import { ButtonComponent } from '@shared/components/button/button.component';
import { CustomSelectComponent } from '@shared/components/custom-select/custom-select.component';
import { CustomInputComponent } from '@shared/components/custom-input/custom-input.component';
import { IconComponent } from '@shared/components/icon/icon.component';
import { TranslatePipe } from '@shared/pipes/translate.pipe';

@Component({
  selector: 'app-edit-resource-item-modal',
  standalone: true,
  imports: [
    CommonModule,
    FormsModule,
    ButtonComponent,
    CustomSelectComponent,
    CustomInputComponent,
    IconComponent,
    TranslatePipe
  ],
  template: `
    <div class="space-y-6 pb-2 pt-2">
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
        <div class="form-field">
          <label class="text-xs font-black text-slate-400 uppercase tracking-wider block mb-2">
            {{ 'common.product' | translate }}
          </label>
          <div class="px-4 py-3 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200/60 dark:border-slate-800 text-sm font-extrabold text-slate-800 dark:text-white truncate">
            {{ item?.product_name || 'N/A' }}
          </div>
        </div>

        <div class="form-field">
          <label class="text-xs font-black text-slate-400 uppercase tracking-wider block mb-2">
            {{ 'inv.batch_name' | translate }}
          </label>
          <div class="px-4 py-3 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200/60 dark:border-slate-800 text-sm font-extrabold text-[var(--color-primary)] truncate">
            {{ item?.batch_name || 'N/A' }}
          </div>
        </div>
      </div>

      <div class="form-field">
        <label class="text-xs font-black text-slate-400 uppercase tracking-wider block mb-2">
          {{ 'common.status' | translate }} <span class="text-red-500">*</span>
        </label>
        <app-custom-select
          [options]="statusOptions"
          valueKey="value"
          labelKey="label"
          [(value)]="status"
        />
      </div>

      <div class="form-field">
        <label class="text-xs font-black text-slate-400 uppercase tracking-wider block mb-2">
          {{ 'inventory.resource_content' | translate }}
          <span class="text-red-500">*</span>
        </label>
        <app-custom-input
          [placeholder]="'inventory.resource_content_placeholder' | translate"
          [(ngModel)]="secretData"
          name="secretData"
          type="textarea"
          [rows]="4"
          customClass="min-h-[120px] font-mono text-sm"
        />
      </div>

      <div class="pt-4 flex items-center justify-end gap-3 shrink-0 border-t border-slate-200/60 dark:border-slate-800/80">
        <button
          app-button
          variant="cancel"
          (click)="close(false)"
          [disabled]="submitting()"
        >
          {{ 'action.cancel' | translate }}
        </button>
        <button
          app-button
          variant="primary"
          (click)="save()"
          [loading]="submitting()"
        >
          <app-icon name="check" class="w-4 h-4 mr-1.5" />
          {{ 'action.save' | translate }}
        </button>
      </div>
    </div>
  `,
  styles: [`
    :host {
      display: block;
    }
  `]
})
export class EditResourceItemModalComponent implements OnInit {
  private readonly modalRef = inject(ModalRef);
  private readonly modalData = inject(MODAL_DATA);
  public readonly state = inject(StateService);
  public readonly lang = inject(LanguageService);

  public item: any = null;
  public secretData = '';
  public status = 'available';
  public submitting = signal<boolean>(false);

  public get statusOptions() {
    return [
      { label: this.lang.translate('inventory.resource_available'), value: 'available' },
      { label: this.lang.translate('inventory.resource_sold'), value: 'sold' },
    ];
  }

  ngOnInit() {
    if (this.modalData && this.modalData.item) {
      this.item = this.modalData.item;
      this.secretData = this.item.secret_data || '';
      this.status = this.item.status || 'available';
    }
  }

  public save() {
    if (!this.secretData.trim()) {
      this.state.showToast(this.lang.translate('toast.edit_inventory_validation'), 'error');
      return;
    }

    if (this.submitting()) return;
    this.submitting.set(true);

    this.state.inventoryStore.updateResourceItem(this.item.id, {
      secret_data: this.secretData.trim(),
      status: this.status
    }).subscribe({
      next: (res) => {
        this.submitting.set(false);
        if (res) {
          this.close(true);
        }
      },
      error: () => {
        this.submitting.set(false);
      }
    });
  }

  public close(success: boolean = false) {
    this.modalRef.close(success);
  }
}
