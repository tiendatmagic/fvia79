import { Component, OnInit, inject, signal, ChangeDetectorRef, NgZone } from '@angular/core';
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
        <div class="flex items-center justify-between mb-2">
          <label class="text-xs font-black text-slate-400 uppercase tracking-wider">
            {{ 'inventory.resource_content' | translate }}
            <span class="text-red-500">*</span>
          </label>
          <!-- Hidden file input for TXT import -->
          <input
            #txtFileInput
            type="file"
            accept=".txt"
            class="hidden"
            (change)="onTxtFileSelected($event)"
          />
          <button
            type="button"
            (click)="txtFileInput.click()"
            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-[11px] font-bold
              bg-emerald-500/10 text-emerald-600 dark:text-emerald-400
              hover:bg-emerald-500/20 active:scale-95
              border border-emerald-500/20 transition-all duration-200 cursor-pointer"
          >
            <app-icon name="upload" class="w-3.5 h-3.5" />
            <span>{{ 'inventory.import_txt' | translate }}</span>
          </button>
        </div>
        @if (importedFileName) {
          <div class="flex items-center gap-2 mb-2 px-3 py-2 rounded-lg bg-emerald-500/5 border border-emerald-500/10 text-xs font-semibold text-emerald-600 dark:text-emerald-400">
            <app-icon name="check-circle" class="w-4 h-4 shrink-0" />
            <span class="truncate">{{ 'inventory.txt_imported' | translate }}: {{ importedFileName }}</span>
          </div>
        }
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
  private readonly cdr = inject(ChangeDetectorRef);
  private readonly zone = inject(NgZone);

  public item: any = null;
  public secretData = '';
  public status = 'available';
  public submitting = signal<boolean>(false);
  public importedFileName = '';

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
        const trimmed = content.trim();

        if (!trimmed) {
          this.state.showToast(this.lang.translate('inventory.txt_empty'), 'error');
          input.value = '';
          return;
        }

        this.secretData = trimmed;
        this.importedFileName = file.name;
        this.state.showToast(
          this.lang.translate('inventory.txt_import_success_edit'),
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

  public close(success: boolean = false) {
    this.modalRef.close(success);
  }
}
