import { CommonModule } from '@angular/common';
import { ChangeDetectionStrategy, Component, inject } from '@angular/core';
import { LanguageService } from '@core/services/language.service';
import { MODAL_DATA, ModalRef } from '@core/services/modal-ref';
import { ButtonComponent } from '@shared/components/button/button.component';
import { TranslatePipe } from '@shared/pipes/translate.pipe';
import {
  InventoryLiveCheckResult,
  InventoryLiveCheckSummary,
} from '../inventory.store';

@Component({
  selector: 'app-check-live-results-modal',
  standalone: true,
  changeDetection: ChangeDetectionStrategy.OnPush,
  imports: [CommonModule, ButtonComponent, TranslatePipe],
  template: `
    <div class="space-y-5 pt-1">
      <p class="text-sm leading-6 text-slate-600 dark:text-slate-400">
        {{ 'inventory.check_live_results_description' | translate }}
      </p>

      <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
        <div class="rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900/70 p-3">
          <p class="text-xs font-bold uppercase tracking-wide text-slate-500">
            {{ 'inventory.check_live_total' | translate }}
          </p>
          <p class="mt-1 text-xl font-black text-slate-900 dark:text-white">{{ data.total }}</p>
        </div>
        <div class="rounded-xl border border-emerald-200 dark:border-emerald-900 bg-emerald-50 dark:bg-emerald-950/30 p-3">
          <p class="text-xs font-bold uppercase tracking-wide text-emerald-600 dark:text-emerald-400">
            {{ 'inventory.check_live_live' | translate }}
          </p>
          <p class="mt-1 text-xl font-black text-emerald-700 dark:text-emerald-300">{{ data.live_count }}</p>
        </div>
        <div class="rounded-xl border border-rose-200 dark:border-rose-900 bg-rose-50 dark:bg-rose-950/30 p-3">
          <p class="text-xs font-bold uppercase tracking-wide text-rose-600 dark:text-rose-400">
            {{ 'inventory.check_live_die' | translate }}
          </p>
          <p class="mt-1 text-xl font-black text-rose-700 dark:text-rose-300">{{ data.die_count }}</p>
        </div>
      </div>

      <div class="overflow-x-auto rounded-xl border border-slate-200 dark:border-slate-800">
        <table class="w-full min-w-[620px] text-sm">
          <thead class="bg-slate-50 dark:bg-slate-900">
            <tr class="text-left text-xs uppercase tracking-wide text-slate-500">
              <th class="px-4 py-3 font-black">#</th>
              <th class="px-4 py-3 font-black">{{ 'inventory.check_live_account' | translate }}</th>
              <th class="px-4 py-3 font-black">{{ 'inventory.check_live_result' | translate }}</th>
              <th class="px-4 py-3 font-black">{{ 'inventory.check_live_reason' | translate }}</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
            @for (result of data.results; track result.resource_id; let index = $index) {
              <tr class="bg-white dark:bg-slate-950">
                <td class="px-4 py-3 font-bold text-slate-400">{{ index + 1 }}</td>
                <td class="px-4 py-3">
                  <div class="font-mono font-extrabold text-slate-900 dark:text-white">
                    {{ result.uid || ('inventory.check_live_invalid_uid' | translate) }}
                  </div>
                  <div class="mt-0.5 font-mono text-[11px] text-slate-400">
                    #{{ result.resource_id | slice: 0 : 8 }}
                  </div>
                </td>
                <td class="px-4 py-3">
                  <span
                    class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-black"
                    [ngClass]="statusClass(result.status)"
                  >
                    <span class="h-1.5 w-1.5 rounded-full bg-current"></span>
                    {{ statusLabel(result.status) }}
                  </span>
                </td>
                <td class="px-4 py-3 text-slate-600 dark:text-slate-400">
                  {{ reasonLabel(result) }}
                </td>
              </tr>
            }
          </tbody>
        </table>
      </div>

      <div class="flex justify-end border-t border-slate-200/60 dark:border-slate-800/80 pt-4">
        <button app-button variant="primary" (click)="modalRef.close()">
          {{ 'action.close' | translate }}
        </button>
      </div>
    </div>
  `,
})
export class CheckLiveResultsModalComponent {
  public readonly modalRef = inject(ModalRef);
  public readonly data = inject<InventoryLiveCheckSummary>(MODAL_DATA);
  private readonly lang = inject(LanguageService);

  public statusLabel(status: InventoryLiveCheckResult['status']): string {
    return this.lang.translate(`inventory.check_live_${status}`);
  }

  public statusClass(status: InventoryLiveCheckResult['status']): string {
    return status === 'live'
      ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300'
      : 'bg-rose-100 text-rose-700 dark:bg-rose-950/60 dark:text-rose-300';
  }

  public reasonLabel(result: InventoryLiveCheckResult): string {
    return this.lang.translate(`inventory.check_live_reason_${result.reason}`);
  }
}
